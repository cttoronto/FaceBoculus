var CTT_FBStickman = function($facepath, $color){
				if (!$color){
					 $color = 0x3b5998;
				}
				if (!$facepath){
					 $facepath = "profile.jpg";
				}
				this.stickman = new THREE.Object3D();//create an empty container
				//scene.add( person );//when done, add the group to the scene

				material = new THREE.MeshBasicMaterial( { 
					color: $color, 
					shading: THREE.FlatShading,
					vertexColors: THREE.VertexColors 
				});
				
				geometry = new THREE.CylinderGeometry(1,1,80,10,1);
				var stickman_body = new THREE.Mesh(geometry, material);
				this.stickman.add(stickman_body);
				
				geometry = new THREE.CylinderGeometry(1,1,50,10,1);
				var stickman_arm_r = new THREE.Mesh(geometry, material);
				this.stickman.add(stickman_arm_r);
				
				geometry = new THREE.CylinderGeometry(1,1,50,10,1);
				var stickman_arm_l = new THREE.Mesh(geometry, material);
				this.stickman.add(stickman_arm_l);
				
				geometry = new THREE.CylinderGeometry(1,1,50,10,1);
				var stickman_leg_r = new THREE.Mesh(geometry, material);
				this.stickman.add(stickman_leg_r);
				
				geometry = new THREE.CylinderGeometry(1,1,50,10,1);
				var stickman_leg_l = new THREE.Mesh(geometry, material);
				this.stickman.add(stickman_leg_l);
				stickman_body.position.y = 82;
				stickman_arm_r.position = {x: -8.5, y: 90, z: 0};
				stickman_arm_l.position = {x: 8.5, y: 90, z: 0};
				stickman_leg_r.position = {x: -8.5, y: 20, z: 0};
				stickman_leg_l.position = {x: 8.5, y: 20, z: 0};
				stickman_leg_l.rotation.z  =stickman_arm_l.rotation.z = 20*Math.PI/180
				stickman_leg_r.rotation.z = stickman_arm_r.rotation.z = -20*Math.PI/180
				
				geometry = new THREE.PlaneGeometry(30,30,1,1);
				material = new THREE.MeshLambertMaterial({ map: THREE.ImageUtils.loadTexture($facepath) });
				material.side = THREE.DoubleSide;
				this.face = new THREE.Mesh(geometry, material);
				this.stickman.add(this.face);
				this.face.position = {x: 0, y: 137, z: 0};
}
CTT_FBStickman.prototype.model = function(){
	return this.stickman;
}
CTT_FBStickman.prototype.facemesh = function(){
	return this.face;
}