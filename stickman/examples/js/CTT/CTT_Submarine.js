var CTT_Submarine = function(){
	var $scope = this;
	// texture
	var manager = new THREE.LoadingManager();
	manager.onProgress = function ( item, loaded, total ) {
		console.log("Loading Manager", item, loaded, total );
	};

	var yellowMat= new THREE.MeshPhongMaterial( { specular:0xEEEE00, color: 0xAAAA00, shading: THREE.SmoothShading , vertexColors: THREE.VertexColors } );
	var redMat = new THREE.MeshPhongMaterial( {specular:0xEE0000, color: 0xAA0000, shading: THREE.SmoothShading , vertexColors: THREE.VertexColors } );

	// model
	
	var Submarine = new THREE.Object3D();//create an empty container
	scene.add( Submarine );//when done, add the group to the scene
	
	var loader = new THREE.OBJLoader( manager );
	//loader.load( 'male02/male02.obj', function ( object ) {
	loader.load( 'submarine_body.obj', function ( object ) {
		$scope.submarine_body = object;
		object.traverse( function ( child ) {
			if ( child instanceof THREE.Mesh ) {
				child.material = yellowMat;
			}
		} );

		Submarine.add( object );//add a mesh with geometry to it
		
		
      // directional lighting
      var directionalLight = new THREE.DirectionalLight(0x111111);
      directionalLight.position.set(1, 1, 1).normalize();
      Submarine.add(directionalLight);

	} );
	var loader = new THREE.OBJLoader( manager );
	loader.load( 'periscope.obj', function ( object ) {
		$scope.periscope = object;
		object.traverse( function ( child ) {
			if ( child instanceof THREE.Mesh ) {
				child.material = redMat;
			}
		} );
		Submarine.add( object );
		$scope.periscope.position.y = -83;
		$scope.periscope.position.z = 61;
	} );
	var loader = new THREE.OBJLoader( manager );
	var propellor;
	loader.load( 'propellor.obj', function ( object ) {
		$scope.propellor = object;
		object.traverse( function ( child ) {
			if ( child instanceof THREE.Mesh ) {
				child.material = redMat;
			}
		} );
		Submarine.add( object );

	} );
	
	Submarine.position.y = 500;
	Submarine.position.z = -660;
	Submarine.rotation.x = 90 * (Math.PI / 180);
	Submarine.rotation.y = 180 * (Math.PI / 180);
	
	Submarine.scale.x = Submarine.scale.y = Submarine.scale.z = 2;
	this.Submarine = Submarine;
	this.propellor = $scope.propellor;
	this.periscope = $scope.periscope;
	this.submarine_body = $scope.submarine_body;
	
	this.sin_i = 0;
	$scope.initListeners();
	this.controlSub = true;
}
CTT_Submarine.prototype.initListeners = function(){
	window.document.onkeydown = function(e){
		if (window.event.keyCode == 17){
			window.controlSub = true;
		}
	}
	window.document.onkeyup = function(e){
		if (window.event.keyCode == 17){
			window.controlSub = false;
		}
	}
}
CTT_Submarine.prototype.mesh = function(){
	return this.Submarine;
}
CTT_Submarine.prototype.model = function(){
	return {propellor:this.propellor, periscope:this.periscope, submarine_body:this.submarine_body};	
}

CTT_Submarine.prototype.render = function() {
	if (!this.propellor){
		return;
	}
	this.propellor.rotation.y += 0.15;
	this.sin_i += 0.01;
	
	this.periscope.rotation.z = Math.sin(this.sin_i)*20* (Math.PI / 180);
	this.Submarine.rotation.y = (180+ Math.sin(this.sin_i*2)*10)* (Math.PI / 180);
	
	this.Submarine.translateY(-1);
	this.Submarine.rotation.z +=0.0025;
	
	//this.Submarine.position.x = (Math.sin(this.sin_i)*-500);
	//this.Submarine.position.z = (Math.cos(this.sin_i)*500);
	//submarine.rotation.z = Math.sin(sin_i/2)*10* (Math.PI / 180);
	//console.log(sin_i);
	//console.log(Math.sin(sin_i));
}
