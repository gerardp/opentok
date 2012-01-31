<?php 
require_once 'OpenTokSDK.php'; 
$apiObj = new OpenTokSDK(API_Config::API_KEY, API_Config::API_SECRET); 
$client = ""; 
if (isset($_SERVER["REMOTE_ADDR"]))    { 
	$client = $_SERVER["REMOTE_ADDR"]; 
} 
else if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))    { 
	$client = $_SERVER["HTTP_X_FORWARDED_FOR"]; 
} 
else if (isset($_SERVER["HTTP_CLIENT_IP"]))    { 
	$client = $_SERVER["HTTP_CLIENT_IP"]; 
} 
if(isset($_REQUEST['sessionId'])) { 
	$sessionId = $_REQUEST['sessionId']; 
} else { 
	$session = $apiObj->create_session($client); 
	$sessionId = $session->getSessionId(); 
} 
$token = $apiObj->generate_token(); 
$arr = array ('token'=>$token,'sessionId'=>(string)$sessionId); 
//echo  json_encode($arr); 
?>
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>OpenTok API Sample &#151; Basic Tutorial</title>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>	
	<link href="style.css" type="text/css" rel="stylesheet" >
	<script src="http://static.opentok.com/v0.91/js/TB.min.js"></script>
	<script type="text/javascript" charset="utf-8">
	
	var apiKey;
	var sessionId;
	var token;
	
	function porra(e){
	 apiKey = <?php print API_Config::API_KEY?>;
	 sessionId = e;
	 token = '<?php print $apiObj->generate_token("1_MX4xMTQwOTQ0Mn4xMjcuMC4wLjF-MjAxMi0wMS0zMCAyMzo0NTozOS4yNDE5NzkrMDA6MDB-MC4xNjE5OTA3ODQwMX4", "moderator"); ?>';	// Add to the page using the OpenTok server-side libraries.
	}
	
	porra('1_MX4xMTQwOTQ0Mn4xMjcuMC4wLjF-MjAxMi0wMS0zMCAyMzo0NTozOS4yNDE5NzkrMDA6MDB-MC4xNjE5OTA3ODQwMX4');
	
	var session = null;
	var publisher = null;

	var PUBLISHER_WIDTH = 420;
	var PUBLISHER_HEIGHT = 265;
	var SUBSCRIBER_WIDTH = 160;
	var SUBSCRIBER_HEIGHT = 120;

	var participants = 0;
	var watchers = 0;

	var learn = false; // Set to true if you want detailed event listener alerting

	// Generic function to dump streamEvents to the alert box
	function dumpStreams(streams, reason) {
		for (var i = 0; i < streams.length; i++) {
			alert("streamID: "+streams[i].streamId + "\n" +
			"connectionId: "+streams[i].connection.connectionId +" \n" +
			"type: "+streams[i].type +"\n" +
			"name: "+streams[i].name +"\n" +
			"reason: "+reason);
		}
	}

	// Generic function to dump connectionEvents to the alert box
	function dumpConnections(connections, reason) {
		for (var i = 0; i < connections.length; i++) {
			alert("connectionId: " + connections[i].connectionId + " \n" +
			"reason: " + reason);
		}
	}


	// Action functions

	// Called when user wants to start participating in the call
	function startPublishing() {
		// Starts publishing user local camera and mic
		// as a stream into the session

		// Create a div for the publisher to replace
		var parentDiv = document.getElementById("myCamera");
		var stubDiv = document.createElement("div");
		stubDiv.id = "opentok_publisher";
		parentDiv.appendChild(stubDiv);

		publisher = session.publish(stubDiv.id, {width: PUBLISHER_WIDTH, height: PUBLISHER_HEIGHT});

		document.getElementById("status").innerHTML = "Trying to join the call...";
		document.getElementById("action").innerHTML = "&nbsp;";
	}

	// Called when user wants to stop participating in the call
	function stopPublishing() {
		if (publisher != null) {
			// Stop the stream
			session.unpublish(publisher);
			publisher = null;
		}

		document.getElementById("status").innerHTML = "Leaving the call...";
		document.getElementById("action").innerHTML = "&nbsp;";
	}
	var no = false;

	// Called to subscribe to a new stream
	function subscribeToStream(session, stream) {
		if(no != false){
			// Create a div for the subscriber to replace
			// Assumes that streamIds are integers; true for basic streams

			var parentDiv = document.getElementById("stream_" + (stream.streamId % 4 + 1));
			var stubDiv = document.createElement("div");
			stubDiv.id = "opentok_subscriber_" + stream.streamId;
			parentDiv.appendChild(stubDiv);

			session.subscribe(stream, stubDiv.id, {width: SUBSCRIBER_WIDTH, height: SUBSCRIBER_HEIGHT});
			participants++;
		}
	}

	// Called to unsubscribe from an existing stream
	function unsubscribeFromStream(session, stream) {
		var subscribers = session.getSubscribersForStream(stream);

		for (var i = 0; i < subscribers.length; i++) {
			session.unsubscribe(subscribers[i]);
			participants--;
		}
	}

	// Called to update watcher / participant counts on screen
	function updateCountDisplays() {
		document.getElementById("count-header").innerHTML = "Users already streaming: " + watchers;
		//	document.getElementById("watchers").innerHTML = ((watchers == 0) ? "No" : watchers) + " watcher" + ((watchers != 1) ? "s" : "");
		//document.getElementById("participants").innerHTML = ((participants == 0) ? "No" : participants) + " participant" + ((participants != 1) ? "s" : "");
	}


	// Event listener functions

	function exceptionHandler(event) {
		// Dont try to handle anything; just provide exception
		// messages to a Javascript alert box for now
		alert("Exception: " + event.code + "::" + event.message);
	}

	function sessionConnectedHandler(event) {
		// Note that this page's connection is included in event.connections
		// We can know which one it is by comparing to event.target.connection.connectionId

		var streamConnectionIds = {};
		var streamConnections = 0; // Number of connections with a stream

		if (learn) {
			alert("sessionConnected event");
			dumpConnections(event.connections, "");
			dumpStreams(event.streams, "");
		}

		// Now possible to join a call - update status and controls
		//document.getElementById("status").innerHTML = "You are watching the call";
		document.getElementById("action").innerHTML = '<a href="#" onclick="startPublishing()">Start Streaming</a>';

		// Display any existing streams on screen
		for (var i = 0; i < event.streams.length; i++) {
			subscribeToStream(event.target, event.streams[i]);

			// Count unique connectionIds as we go
			if (!streamConnectionIds.hasOwnProperty(event.streams[i].connection.connectionId)) {
				streamConnectionIds[event.streams[i].connection.connectionId] = true;
				streamConnections++;
			}
		}

		// Assume each connection represents a different
		// user connected to the session
		watchers = event.connections.length - streamConnections;

		updateCountDisplays();
	}

	function connectionCreatedHandler(event) {
		// Note that we will do not get a connectionCreated
		// event for this page's connection when we connect.
		// That case is handled by the sessionConnected event.

		if (learn) {
			alert("connectionCreated event");
			dumpConnections(event.connections, "");
		}

		// Assume each connection represents a different
		// user connected to the session.
		watchers += event.connections.length;

		updateCountDisplays();
	}


	function connectionDestroyedHandler(event) {
		if (learn) {
			alert("connectionDestroyed event");
			dumpConnections(event.connections, event.reason);
		}

		// Assume each connection represents a different
		// user that was connected to the session.
		watchers -= event.connections.length;

		updateCountDisplays();
	}


	function streamCreatedHandler(event) {
		// Note that we will get a streamCreated event for 
		// this page's stream when we successfully start publishing.

		if (learn) {
			alert("streamCreated event");
			dumpStreams(event.streams, "");
		}

		// Display streams on screen, except for this page's own stream.

		for (var i = 0; i < event.streams.length; i++) {
			if (event.streams[i].connection.connectionId != event.target.connection.connectionId) {
				subscribeToStream(event.target, event.streams[i]);
				watchers--;
			} else {
				// Our publisher just started streaming

				// Update status, controls and counts
				document.getElementById("status").innerHTML = "You are participating in the call";
				document.getElementById("action").innerHTML = '<a href="#" onclick="stopPublishing()">Leave call</a>';

				participants++;
				watchers--;
			}
		}

		updateCountDisplays();
	}


	function streamDestroyedHandler(event) {
		// Note that we will get a streamDestroyed event for 
		// this page's stream when we successfully stop publishing

		if (learn) {
			alert("streamDestroyed event");
			dumpStreams(event.streams, event.reason);
		}

		// Remove streams from screen, except for our own stream

		for (var i = 0; i < event.streams.length; i++) {
			if (event.streams[i].connection.connectionId != event.target.connection.connectionId) {
				unsubscribeFromStream(event.target, event.streams[i]);
				watchers++;
			} else {
				// Our publisher just stopped streaming

				// Update status, controls and counts
				document.getElementById("status").innerHTML = "You are watching the call";
				document.getElementById("action").innerHTML = '<a href="#" onclick="startPublishing()">Join call</a>';

				participants--;
				watchers++;
			}
		}

		updateCountDisplays();
	}


	window.onload = function(){
		$("h1").css("display","block");
		$("h1").animate({opacity:1},2300);
		

	}

	</script>
</head>
<body>
	<div id="wrapperUser">
		<h1 style="opacity:0"><img src="images/topfloor.png"/></h1>
		<div class = "rightbox">
			<div class="controls">
				<div id="status">You are connecting to the call</div>
				<div id="action" style="padding-bottom: 6px">&nbsp;</div>
				<div id="count-header">Not yet connected</div>
				<div id="watchers">&nbsp;</div>
			</div>
		</div>
		<div id="localview">
			<div id="myCamera" class="publisherContainer"></div>
		</div>
		<div id="main">
			<div id="stream_1" class="right-pic"></div>
		</div>
		<div>

		<script type="text/javascript">
		// Set debugging level if wanted
		// TB.setLogLevel(TB.DEBUG);

		if (TB.checkSystemRequirements() != TB.HAS_REQUIREMENTS) {
			alert("Unable to run TokBox OpenTok in this browser.");
		} else {
			// Register the exception handler and
			// create the local session object
			TB.addEventListener("exception", exceptionHandler);
			session = TB.initSession(sessionId);

			// Register all the listeners that route events to
			// Javascript functions
			session.addEventListener("sessionConnected", sessionConnectedHandler);
			session.addEventListener("connectionCreated", connectionCreatedHandler);
			session.addEventListener("connectionDestroyed", connectionDestroyedHandler);
			session.addEventListener("streamCreated", streamCreatedHandler);
			session.addEventListener("streamDestroyed", streamDestroyedHandler);

			/* Connect to the session
			If testing the app from the desktop, be sure to check the Flash Player Global Security setting
			to allow the page from communicating with SWF content loaded from the web. For more information,
			see http://www.tokbox.com/opentok/build/tutorials/basictutorial.html#localTest */
			session.connect(apiKey, token);
		}
		</script>

	</body>

	</html>
	