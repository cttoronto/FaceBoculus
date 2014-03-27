/**
 * Copyright 2013 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Based on THREE.PointerLockControls by mrdoob.
 * @author benvanik
 */

var moveForward = false;
var moveBackward = false;
var moveLeft = false;
var moveRight = false;
var moveUp = false;
var moveDown = false;
var moveYDelta = 0;
var moveXDelta = 0;
var moveZDelta = 0;

var velocity;

THREE.OculusRiftControls = function ( camera ) {

	var scope = this;

	var moveObject = new THREE.Object3D();
	moveObject.position.y = 10;
	moveObject.add( camera );

	

	var isOnObject = false;
	var canJump = false;

	velocity = new THREE.Vector3();

	var PI_2 = Math.PI / 2;

	this.moveSpeed = 0.12 / 4;
	this.jumpSpeed = 2;

	var _q1 = new THREE.Quaternion();
	var axisX = new THREE.Vector3( 1, 0, 0 );
	var axisZ = new THREE.Vector3( 0, 0, 1 );

	var onMouseMove = function ( event ) {

		if ( scope.enabled === false ) return;

		var movementX = event.movementX || event.mozMovementX || event.webkitMovementX || 0;
		var movementY = event.movementY || event.mozMovementY || event.webkitMovementY || 0;

		console.log(movementX, movementY);

		_q1.setFromAxisAngle( axisZ, movementX * 0.002 );
		moveObject.quaternion.multiplySelf( _q1 );
		_q1.setFromAxisAngle( axisX, movementY * 0.002 );
		moveObject.quaternion.multiplySelf( _q1 );
	};

	var onKeyDown = function ( event ) {

		switch ( event.keyCode ) {

			case 38: // up
			case 87: // w
				moveForward = true;
				break;

			case 37: // left
			case 65: // a
				moveLeft = true; break;

			case 40: // down
			case 83: // s
				moveBackward = true;
				break;

			case 39: // right
			case 68: // d
				moveRight = true;
				break;

			case 32: // space
				if ( canJump === true ) velocity.y += this.jumpSpeed;
				canJump = false;
				break;

		}

	}.bind(this);

	var onKeyUp = function ( event ) {

		switch( event.keyCode ) {

			case 38: // up
			case 87: // w
				moveForward = false;
				break;

			case 37: // left
			case 65: // a
				moveLeft = false;
				break;

			case 40: // down
			case 83: // a
				moveBackward = false;
				break;

			case 39: // right
			case 68: // d
				moveRight = false;
				break;

		}

	};

	document.addEventListener( 'mousemove', onMouseMove, false );
	document.addEventListener( 'keydown', onKeyDown, false );
	document.addEventListener( 'keyup', onKeyUp, false );

	this.enabled = false;

	this.getObject = function () {

		return moveObject;

	};

	this.isOnObject = function ( boolean ) {

		isOnObject = boolean;
		canJump = boolean;

	};

	this.update = function ( delta, vrstate ) {

		//if ( scope.enabled === false ) return;
		delta *= 0.1;

		velocity.x += ( - velocity.x ) * 0.08 * delta;
		velocity.z += ( - velocity.z ) * 0.08 * delta;
		velocity.y += ( - velocity.y ) * 0.08 * delta;
		//velocity.y -= 0.10 * delta;

		//set upper limits for velocity
		velocity.x = Math.min(velocity.x, 20);
		velocity.z = Math.min(velocity.z, 20);
		velocity.y = Math.min(velocity.y, 20);

		if ( moveForward ) velocity.z -= this.moveSpeed * Math.abs(moveZDelta);
		if ( moveBackward ) velocity.z += this.moveSpeed * Math.abs(moveZDelta);

		if ( moveLeft ) velocity.x -= this.moveSpeed * Math.abs(moveXDelta);
		if ( moveRight ) velocity.x += this.moveSpeed * Math.abs(moveXDelta);

		if ( moveUp ) velocity.y += this.moveSpeed * Math.abs(moveYDelta);
		if ( moveDown ) velocity.y -= this.moveSpeed * Math.abs(moveYDelta);

		if ( isOnObject === true ) {

			velocity.y = Math.max( 0, velocity.y );

		}

		var rotation = new THREE.Quaternion();
		var angles = new THREE.Vector3();
		if (vrstate) {
			rotation.set(
					vrstate.hmd.rotation[0],
					vrstate.hmd.rotation[1],
					vrstate.hmd.rotation[2],
					vrstate.hmd.rotation[3]);
			angles.setEulerFromQuaternion(rotation, 'XYZ');
			angles.z = 0;
			angles.normalize();
			rotation.setFromEuler(angles, 'XYZ');
			rotation.normalize();
			// velocity.applyQuaternion(rotation);
		}

		moveObject.translateX( velocity.x );
		moveObject.translateY( velocity.y );
		moveObject.translateZ( velocity.z );

		if ( moveObject.position.y < 10 ) {
			velocity.y = 0;
			moveObject.position.y = 10;
			canJump = true;
		}
		if ( moveObject.position.y > 350 ){
			velocity.y = 0;
			moveObject.position.y = 350;
		}

		if ( moveObject.position.x < -200 ) {
			velocity.x = 0;
			moveObject.position.x = -200;
		}

		if ( moveObject.position.x > 200 ) {
			velocity.x = 0;
			moveObject.position.x = 200;
		}

		if ( moveObject.position.z < -150 ) {
			velocity.z = 0;
			moveObject.position.z = -150;
		}

		if ( moveObject.position.z > 250 ) {
			velocity.z = 0;
			moveObject.position.z = 250;
		}		


	};

};
