<?php

require 'src/facebook.php';

$facebook = new Facebook(array(
  'appId'  => '220762168118305',
  'secret' => 'cc93d71c061ff715d8a6a3ec62ca7147',
));

// See if there is a user from a cookie
$user = $facebook->getUser();

if ($user) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
    $limit = (isset($_GET['limit'])) ? $_GET['limit'] : 50;
    // $friends = $facebook->api('me/friends?fields=id,name&limit=5000');
    $friends = $facebook->api('/me/friends?limit='.$limit);
    $PottersFriends = array();
    foreach ($friends["data"] as $value) {
      $PottersFriends[] = (object) array(
          'name' =>$value['name'],
          'id' =>$value['id'],
          'image' =>"http://graph.facebook.com/" . $value['id'] . "/picture?height=200&type=normal&width=200"
        );
    }

        // echo '<ul>';
        // foreach ($friends["data"] as $value) {
        //   if (strpos($value["name"], "'") === false) {
        //     echo '<li>';
        //     echo '<div class="pic"><a href="#" onclick="PostOnFriendsWall(\'' . $value["id"] . '\', \'' . $value["name"] . '\')">';
        //     echo '<img src="https://graph.facebook.com/' . $value["id"] . '/picture"/>';
        //     echo '</a></div>';
        //     echo '<div class="picName">'.$value["name"].'</div>'; 
        //     echo '</li>';
        //   }
        // }
        // echo '</ul>';

       

  } catch (FacebookApiException $e) {
    echo '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
    $user = null;
  }
}

?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>FacebOculus</title>
		<style>
			html, body {
				width: 100%;
				height: 100%;
			}

			body {
				background-color: #ffffff;
				margin: 0;
				overflow: hidden;
				font-family: arial;
			}

			#help {
				position: absolute;
				top: 800px;
			}
			#info {
				position: absolute;
				top: 820px;
			}

			canvas {
				position: absolute;
				top: 0;
				left: 0;
			}
			#login { position: fixed; z-index: 1000; }

		</style>
	</head>
	<body>
	<?php if ($user) { ?>
    <?php } else { ?>
      <!-- <fb:login-button></fb:login-button> -->
      <div id="login"><a onclick="login(); return false;">Login</a></div>
    <?php } ?>
    <div id="fb-root"></div>
		<script src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.2/underscore-min.js"></script>
		<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>

		<script src="../lib/vr.js"></script>
		<script src="../lib/leap.js"></script>		
		<script src="../third_party/three.js/three.js"></script>
		<script src="js/controls/OculusRiftControls.js"></script>
		<script src="js/effects/OculusRiftEffect.js"></script>
		<script src="js/CTT/CTT_FBStickman.js"></script>

    <div id="help">Move this window to your Rift (use the Windows key + arrows to get it there fast) then hit 'f' to make it fullscreen.</div>
		<div id="info">IPD: <span id='ipd'>0</span> (+o/-p)</div>
<script>
  

      var people = <?=json_encode($PottersFriends);?>;
        

      window.fbAsyncInit = function() {
        FB.init({
          appId: '<?php echo $facebook->getAppID() ?>',
          cookie: true,
          xfbml: true,
          oauth: true
        });
        FB.Event.subscribe('auth.login', function(response) {
          window.location.reload();

        });
        FB.Event.subscribe('auth.logout', function(response) {
          window.location.reload();
        });
      };
      
      var token = {};

      function login() {
        FB.login(function(response) {
          console.log(response);
          localStorage.setItem("token", response.authResponse.accessToken);
         // handle the response
         // token = response.authResponse.accessToken;
         // grabFriends();
        }, {scope: 'user_status,friends_status,publish_actions'});
      }


       function logResponse(response) {
        console.log(response);
        if (response && !response.error) {
          /* handle the result */
          console.log("success");
        } else {
          console.log(response.error);
        }
      }


     (function() {
        var e = document.createElement('script'); e.async = true;
        e.src = document.location.protocol +
          '//connect.facebook.net/en_US/all.js';
        document.getElementById('fb-root').appendChild(e);
      }());
    </script>
		<script>
		(function() {
			var camera, scene, renderer;
			var geometry, material, mesh;
			var controls, time = Date.now();

			var effect; // rift effect

			var object_faces = new Array();

			var ray;
			var person;
			var sphere;
			
			var plane_floor;

			
			if (!vr.isInstalled()) {
				//statusEl.innerText = 'NPVR plugin not installed!';
				alert('NPVR plugin not installed!');
			}
			vr.load(function(error) {
				if (error) {
					//statusEl.innerText = 'Plugin load failed: ' + error.toString();
					alert('Plugin load failed: ' + error.toString());
				}

				try {
					init();
					animate();
				} catch (e) {
					//statusEl.innerText = e.toString();
					console.log(e);
				}
			});

			function init() {

				camera = new THREE.PerspectiveCamera( 75, window.innerWidth / window.innerHeight, 1, 1000 );
				//camera.position.y = 125;
				scene = new THREE.Scene();
				scene.fog = new THREE.Fog( 0xffffff, 0, 750 );

				var light = new THREE.DirectionalLight( 0xffffff, 1.5 );
				light.position.set( 1, 1, 1 );
				scene.add( light );

				var light = new THREE.DirectionalLight( 0xffffff, 0.75 );
				light.position.set( -1, - 0.5, -1 );
				scene.add( light );

				controls = new THREE.OculusRiftControls( camera );
				scene.add( controls.getObject() );

				// var cameraHelper = new THREE.CameraHelper(camera);
				// scene.add(cameraHelper);

				ray = new THREE.Raycaster();
				ray.ray.direction.set( 0, -1, 0 );

				// floor

				geometry = new THREE.PlaneGeometry( 2000, 2000, 100, 100 );
				geometry.applyMatrix( new THREE.Matrix4().makeRotationX( - Math.PI / 2 ) );

				for ( var i = 0, l = geometry.vertices.length; i < l; i ++ ) {

					var vertex = geometry.vertices[ i ];
					vertex.x += Math.random() * 20 - 10;
					vertex.y += Math.random() * 2;
					vertex.z += Math.random() * 20 - 10;

				}

				for ( var i = 0, l = geometry.faces.length; i < l; i ++ ) {
					var face = geometry.faces[ i ];
					face.vertexColors[ 0 ] = new THREE.Color().setHSL( Math.random() * 0.2 + 0.5, 0.75, Math.random() * 0.25 + 0.75 );
					face.vertexColors[ 1 ] = new THREE.Color().setHSL( Math.random() * 0.2 + 0.5, 0.75, Math.random() * 0.25 + 0.75 );
					face.vertexColors[ 2 ] = new THREE.Color().setHSL( Math.random() * 0.2 + 0.5, 0.75, Math.random() * 0.25 + 0.75 );
					face.vertexColors[ 3 ] = new THREE.Color().setHSL( Math.random() * 0.2 + 0.5, 0.75, Math.random() * 0.25 + 0.75 );

				}

				material = new THREE.MeshBasicMaterial( { vertexColors: THREE.VertexColors } );

				plane_floor = new THREE.Mesh( geometry, material );
				scene.add( plane_floor );
				
				geometry = new THREE.SphereGeometry(20,12,12);
				sphere = new THREE.Mesh(geometry);
				// scene.add(sphere);
				
				
				renderer = new THREE.WebGLRenderer({
					devicePixelRatio: 1,
					alpha: false,
					clearColor: 0xffffff,
					antialias: true
				});

				effect = new THREE.OculusRiftEffect( renderer );

				document.getElementById('ipd').innerHTML =
						effect.getInterpupillaryDistance().toFixed(3);

				document.body.appendChild( renderer.domElement );

				//

				window.addEventListener( 'resize', onWindowResize, false );
				document.addEventListener( 'keydown', keyPressed, false );
				
				populatePeople(people);

				var fingers = {};
  				var spheres = {};
  				var controllerOptions = {enableGestures: true};

  				Leap.loop(controllerOptions, function(frame) {

				    var fingerIds = {};
				    var handIds = {};

				    //if (paused) return;

				    if (frame.hands.length == 1){
				    	var hand = frame.hands[0];

				    	if (hand.fingers.length == 1){
				    		
						    for (var index = 0; index < frame.pointables.length; index++) {

						      var pointable = frame.pointables[index];
						      var finger = fingers[pointable.id];

						      var pos = pointable.tipPosition;
						      var dir = pointable.direction;

						      var origin = new THREE.Vector3(camera.parent.position.x, camera.parent.position.y, camera.parent.position.z);
						      var direction = new THREE.Vector3(dir[0], dir[1], dir[2]);

						      if (!finger) {
						        finger = new THREE.ArrowHelper(origin, direction, 40, 0xff0000);
						        fingers[pointable.id] = finger;
						        scene.add(finger);
						      }

						      finger.position = origin;
						      finger.setDirection(direction);

						      fingerIds[pointable.id] = true;
						    }

						    for (fingerId in fingers) {
						      if (!fingerIds[fingerId]) {
						        scene.remove(fingers[fingerId]);
						        delete fingers[fingerId];
						      }
						    }

						    if (frame.gestures && frame.gestures.length > 0) {
			            		handleGesture(frame.gestures[0]);
			        		}

						    velocity = new THREE.Vector3();
				    		moveXDelta = moveYDelta = moveZDelta = 0;

						} else {

							for (fingerId in fingers) {
						      if (!fingerIds[fingerId]) {
						        scene.remove(fingers[fingerId]);
						        delete fingers[fingerId];
						      }
						    }
				    	
					    	// pitch is 
					    		// forward (positive values) 
					    		// backward (negative values)
					    	moveForward = (hand.pitch() <= -0.3);
					    	moveBackward = (hand.pitch() > 0.3);
					    	velocity.z = (!moveForward && !moveBackward) ? 0 : velocity.z;
					    	moveZDelta = (!moveForward && !moveBackward) ? 0 : hand.pitch() * 10;

					    	// roll is 
					    		// left (positive values
					    		// right (negative values)
					    	moveLeft = (hand.roll() >= 0.3);
					    	moveRight = (hand.roll() < -0.3);
					    	velocity.x = (!moveLeft && !moveRight) ? 0 : velocity.x;
					    	moveXDelta = (!moveLeft && !moveRight) ? 0 : hand.roll() * 10;

					    	moveUp = (hand.palmPosition[1] > 150);
					    	moveDown = (hand.palmPosition[1] < 100);
					    	velocity.y = (!moveUp && !moveDown) ? 0 : velocity.y;
					    	moveYDelta = (!moveUp && !moveDown) ? 0 : 10 / Math.round((125 - hand.palmPosition[1], 2) + 0.003);
					    }
				    } else {
				    	velocity = new THREE.Vector3();
				    	moveXDelta = moveYDelta = moveZDelta = 0;
				    }
					
				    //if(frame.gestures.length > 0) console.log(frame.gestures);

				    renderer.render(scene, camera);
				});

				function handleGesture(gesture) {
			
				    switch(gesture.type){
		        	   case "screenTap":
							//console.log("Screen tap: " + gesture.position);
							//console.log(fingers[gesture.pointableIds[0]].position);
							checkCollisions(fingers[gesture.pointableIds[0]].position);
				            break;
		       		     default:
		           		    //console.log('detected unhandled gesture of type', gesture.type, gesture);
		       		}
				} 

			}

			function onWindowResize() {
			}

			function keyPressed (event) {
				switch ( event.keyCode ) {
					case 79: // o
						effect.setInterpupillaryDistance(
								effect.getInterpupillaryDistance() - 0.001);
						document.getElementById('ipd').innerHTML =
								effect.getInterpupillaryDistance().toFixed(3);
						break;
					case 80: // p
						effect.setInterpupillaryDistance(
								effect.getInterpupillaryDistance() + 0.001);
						document.getElementById('ipd').innerHTML =
								effect.getInterpupillaryDistance().toFixed(3);
						break;

					case 70: // f
						if (!vr.isFullScreen()) {
							vr.enterFullScreen();
						} else {
							vr.exitFullScreen();
						}
						e.preventDefault();
						break;

					case 32: // space
						vr.resetHmdOrientation();
						e.preventDefault();
						break;
				}
			}
			function populatePeople($people){
				var person;
				for (var i = 0; i < $people.length; i ++){	
					person = new CTT_FBStickman($people[i]);
					scene.add(person.model());
					person.model().position.x = Math.random()*1000-500;
					person.model().rotation.y = Math.random()*360 *Math.PI/180;
					person.model().position.z = Math.random()*1000-500;
					object_faces.push(person.facemesh().face);
				}				
			}
			var vrstate = new vr.State();
			function animate() {
				vr.requestAnimationFrame(animate);

				controls.isOnObject( false );

				ray.ray.origin.copy( controls.getObject().position );
				ray.ray.origin.y -= 10;

				var intersections = ray.intersectObjects( object_faces );
				if ( intersections.length > 0 ) {
					var distance = intersections[ 0 ].distance;
					if ( distance > 0 && distance < 10 ) {
						controls.isOnObject( true );
					}
				}

				// Poll VR, if it's ready.
				var polled = vr.pollState(vrstate);
				controls.update( Date.now() - time, polled ? vrstate : null );

				//renderer.render( scene, camera );
				effect.render( scene, camera, polled ? vrstate : null );

				time = Date.now();
//				sphere.position = controls.getObject().position.clone();
//				sphere.position.z -= 10;

/*
				var collide_ray = new THREE.Raycaster(sphere.position);
				collide_ray.ray.direction.set( 0, 0, -1 );

				var collisions = collide_ray.intersectObjects( object_faces );
				
				if ( collisions.length > 0 ) {
					var distance = collisions[ 0 ].distance;
					//console.log(distance, collisions[0].object);
					if ( distance > 0 && distance < 10 ) {
						onPoke(collisions[0].object.data);
					}
				}
*/
				/**/
			}

			function checkCollisions($fingerPosition){
				var collide_ray = new THREE.Raycaster($fingerPosition);
				collide_ray.ray.direction.set( 0, 0, -1 );

				var collisions = collide_ray.intersectObjects( object_faces );
				
				if ( collisions.length > 0 ) {
					var distance = collisions[ 0 ].distance;
					
					//console.log(distance, collisions[0].object);
					if ( distance > 0 && distance < 20 ) {
						onPoke(collisions[0].object.data);
					}
				}
			}

			var poked_faces = new Array();

			function onPoke($data){
		
				for (var i = 0; i < poked_faces.length; i ++){
                   if (poked_faces[i] == $data){
                       return;    
                   }
               }
               poked_faces.push($data);


        var token    = localStorage.getItem("token"),
            postData = {
                "message": "I am FaceBoculus-ing and would like to poke @" + $data.name + "!!!!",
	                // "access_token": token,
	                // "from": { "id": "<?=$user_profile["id"];?>", "name": "<?=$user_profile["name"];?>" },
	                // "to": { "name": friendName, "id": friendId },
	                // "message_tags": [{ "name": friendName, "id": friendId, "offset": 46, "length": friendName.length }],
	                // "tags": $data.id,
	                "picture": "http://paper-face.com/cttoronto/faceboculus/poke.jpg",
	                "caption": "POKE",
	                "description": "Brought to you by FaceBoculus and the Creative Technologists of Toronto"
	                // "app_id": "<?php echo $facebook->getAppID() ?>"
	            };

	        FB.api(
	            // "/" + friendId + "/feed",
	            "/me/feed/",
	            "POST",
	            postData,   // object
	            logResponse // Callback function created just above this method
	        );
	        	
			}
		})();
		</script>
	</body>
</html>
