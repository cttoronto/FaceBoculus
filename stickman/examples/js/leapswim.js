//Requires <script src="http://js.leapmotion.com/0.2.0/leap.min.js"></script>

function listenForSwim(swimForwardCallback) {
	var controller = new Leap.Controller({enableGestures: true});

	var clock=0;
	var ticks=0;
	var windowSize=10;
	var sampleSize=3;
	var handBucket = {};
	var clockScale = 10;
	var targetVelocity = 300;
	
	controller.on('frame', function(frame) {
		clock++;
		
		if (clock%clockScale==0)
		{			
			ticks++;
			
			for(var i=0; i<frame.hands.length; i++) {
				var hand = frame.hands[i];
				
				//drawHand(frame, hand);

				if (Math.abs(hand.palmVelocity[0]) > targetVelocity)
				{
					if (handBucket[hand.id] == undefined) {
						handBucket[hand.id] = { handId: hand.id,
												birthTick:ticks, 
												direction:hand.palmVelocity[0]>0 ? "R" : "L",
												count:1};
						console.log("Added " + JSON.stringify(handBucket[hand.id]));
					}
					else {
						if ((hand.palmVelocity[0]>0 && handBucket[hand.id].direction=="R") ||
							(hand.palmVelocity[0]<0 && handBucket[hand.id].direction=="L")) {
							handBucket[hand.id].count++;
							
							console.log("Hand "+hand.id+" travelling " +handBucket[hand.id].direction+ " with count " + handBucket[hand.id].count);
						}
						else {
							handBucket[hand.id].count=1;
							handBucket[hand.id].direction=hand.palmVelocity[0]>0 ? "R" : "L";
							console.log("Hand "+hand.id+" changed directions, now going "+handBucket[hand.id].direction);
						}
					}
				}
			}
			
			var hit1 = null;
			var hit2 = null;
			var garbageCollection = [];
			var found = false;
			for (var key in handBucket) {
				if (handBucket.hasOwnProperty(key)) {
					if (handBucket[key].count >= sampleSize) {
						if (hit1 == null) {
							hit1 = handBucket[key];
						}
						else {
							if (!(handBucket[key].direction === hit1.direction)) {
								
								found = true;
								hit2 = handBucket[key];
								
								garbageCollection.push(hit1.handId);
								garbageCollection.push(hit2.handId);
								
								break;
							}
						}
					}
					
					if (ticks - handBucket[key].birthTick > windowSize) {
						garbageCollection.push(key);
					}
				}
			}
			
			for (var garbageKey in garbageCollection) {
				console.log("Deleted " + JSON.stringify(handBucket[garbageCollection[garbageKey]]));
				delete handBucket[garbageCollection[garbageKey]];
			}
			
			if (found)
				swimForwardCallback();
		}
	});
	
	controller.connect();
}
	