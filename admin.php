<?php
// Begin session
session_start();
	
// If username is not set, then go to the index page
if (!isset($_SESSION['username'])) {
	header('Location: index.php');
}
// Include database connection settings.
include('includes/config.php');

session_start();

// set timeout period in seconds
$inactive = 600;

// check to see if $_SESSION['timeout'] is set
if(isset($_SESSION['timeout']) ) {
	$session_life = time() - $_SESSION['timeout'];
	if($session_life > $inactive)
        { session_destroy(); header("Location: index.php"); }
}
$_SESSION['timeout'] = time();
?>

<?php 
require_once 'opentok/OpenTokSDK.php'; 
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
	<title>Control Room</title>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>	
	<link href="css/style.css" type="text/css" rel="stylesheet" >
	<link rel="shortcut icon" type="image/x-icon" href="images/favi.ico">
	<script src="http://static.opentok.com/v0.91/js/TB.min.js"></script>
	<script type="text/javascript" charset="utf-8">

	var apiKey;
	var sessionId;
	var token;

	function porra(e){
		apiKey = <?php print API_Config::API_KEY?>;
		sessionId = e;
		token = '<?php print $apiObj->generate_token("1_MX4xMTQwOTQ0Mn4xOTIuMTY4LjEuMX4yMDEyLTAxLTI3IDIwOjExOjQ2LjM3MTMzNCswMDowMH4wLjM3MjQzNzY2MjQ0MX4", "moderator"); ?>';	// Add to the page using the OpenTok server-side libraries.
	}

	porra('1_MX4xMTQwOTQ0Mn4xOTIuMTY4LjEuMX4yMDEyLTAxLTI3IDIwOjExOjQ2LjM3MTMzNCswMDowMH4wLjM3MjQzNzY2MjQ0MX4');

	var session;
	var publisher;

	var subscribers = {};

	var PUBLISHER_WIDTH = 264;
	var PUBLISHER_HEIGHT = 198;

	// Un-comment either of the following to set automatic logging and exception handling.
	// See the exceptionHandler() method below.
	// TB.setLogLevel(TB.DEBUG);
	TB.addEventListener("exception", exceptionHandler);

	if (TB.checkSystemRequirements() != TB.HAS_REQUIREMENTS) {
		alert("You don't have the minimum requirements to run this application."
		+ "Please upgrade to the latest version of Flash.");
	} else {
		session = TB.initSession(sessionId);

		// Add event listeners to the session
		session.addEventListener("sessionConnected", sessionConnectedHandler);
		session.addEventListener("sessionDisconnected", sessionDisconnectedHandler);
		session.addEventListener("streamCreated", streamCreatedHandler);
		session.addEventListener("streamDestroyed", streamDestroyedHandler);
		session.addEventListener("signalReceived", signalReceivedHandler);
	}

	//--------------------------------------
	//  OPENTOK EVENT HANDLERS
	//--------------------------------------




	function sessionConnectedHandler(event) {
		for (var i = 0; i < event.streams.length; i++) {
			addStream(event.streams[i]);
		}

		hide("connectLink");
		show("disconnectLink");
		show("publishLink");
		show("signalLink");
		document.getElementById("myCamera").innerHTML = "";
	}

	function sessionDisconnectedHandler(event) {
		event.preventDefault();	// Prevent the default cleanup because we do it ourselves

		// Remove the publisher
		if (publisher) {
			stopPublishing();
		}

		// Remove all subscribers
		for (var streamId in subscribers) {
			removeStream(streamId);
		}

		if (event.reason == "forceDisconnected") {
			alert("A moderator has disconnected you from the session.");
		}

		show("connectLink");
		hide("disconnectLink");
		hide("publishLink");
		hide("unpublishLink");
		hide("signalLink");
	}

	function streamCreatedHandler(event) {
		for (var i = 0; i < event.streams.length; i++) {
			addStream(event.streams[i]);
		}
	}

	function streamDestroyedHandler(event) {

		for (var i = 0; i < event.streams.length; i++) {
			
			var sid =  event.streams[0].streamId;
			var cid = event.streams[0].connection.connectionId;

			$.ajax({
				type: "POST",
				url: "includes/removeguest.php",
				data:({streamId: sid}),
				success: function() {
				}
			});
			
			removeStream(event.streams[i].streamId);
			if (event.streams[i].connection.connectionId == session.connection.connectionId &&
				event.reason == "forceUnpublished") {
					alert("A moderator has stopped publication of your stream.");
					hide("unpublishLink");
					show("publishLink");
					publisher = null;
				} else {
					removeStream(event.streams[i].streamId);
				}
			}
		}

		function signalReceivedHandler(event) {
			alert("Received a signal from connection " + event.fromConnection.connectionId);
		}

		/*
		If you un-comment the call to TB.addEventListener("exception", exceptionHandler) above, OpenTok calls the
		exceptionHandler() method when exception events occur. You can modify this method to further process exception events.
		If you un-comment the call to TB.setLogLevel(), above, OpenTok automatically displays exception event messages.
		*/
		function exceptionHandler(event) {
			alert("Exception: " + event.code + "::" + event.message);
		}

		//--------------------------------------
		//  LINK CLICK HANDLERS
		//--------------------------------------

		/*
		If testing the app from the desktop, be sure to check the Flash Player Global Security setting
		to allow the page from communicating with SWF content loaded from the web. For more information,
		see http://www.tokbox.com/opentok/build/tutorials/helloworld.html#localTest
		*/
		function connect() {
			session.connect(apiKey, token);
		}

		function disconnect() {
			session.disconnect();
		}

		// Called when user wants to start publishing to the session
		function startPublishing() {
			if (!publisher) {
				var parentDiv = document.getElementById("myCamera");
				var publisherDiv = document.createElement('div'); // Create a div for the publisher to replace
				publisherDiv.setAttribute('id', 'opentok_publisher');
				parentDiv.appendChild(publisherDiv);
				publisher = session.publish(publisherDiv.id); // Pass the replacement div id to the publish method
				hide('publishLink');

			}
		}

		function stopPublishing() {
			if (publisher) {
				session.unpublish(publisher);
				hide("unpublishLink");
				show("publishLink");
			}

			publisher = null;
		}

		function signal() {
			session.signal();
		}

		function forceDisconnectStream(streamId) {
			session.forceDisconnect(subscribers[streamId].stream.connection.connectionId);
		}

		function forceUnpublishStream(streamId) {
			//alert(session.signal(subscribers[streamId].stream))
			session.forceUnpublish(subscribers[streamId].stream);
			$.ajax({
				type: "POST",
				url: "includes/removeguest.php",
				data:({streamId: sid}),
				success: function() {
				}
			});
		}

		//--------------------------------------
		//  HELPER METHODS
		//--------------------------------------


		var guardar = [];


		function joinTopFloor(a){
			var br = a.split(",")
			var sid = br[0];
			var cid = br[1];

			$.ajax({
				type: "POST",
				url: "includes/topfloor.php",
				data:({streamId: sid, connectionId: cid}),
				success: function() {
				}
			});

		}



		function addStream(stream) {

			if (stream.connection.connectionId == session.connection.connectionId) {
				show("unpublishLink");
				return;
			}
			// Create the container for the subscriber
			var container = document.createElement('div');
			container.className = "subscriberContainer";
			var containerId = "container_" + stream.streamId;
			container.setAttribute("id", containerId);
			document.getElementById("subscribers").appendChild(container);

			// Create the div that will be replaced by the subscriber
			var div = document.createElement('div');
			var divId = stream.streamId;
			div.setAttribute('id', divId);
			div.style.cssFloat = "top";
			container.appendChild(div);

			//	alert(stream.toSource());
			//	alert(stream.connection.connectionId);

			// SEND OBJECT DATA
			var sid =  stream.streamId;
			var cid = stream.connection.connectionId;		


			// Create a div for the force disconnect link
			var moderationControls = document.createElement('ul');
			moderationControls.style.cssFloat = "bottom";
			moderationControls.innerHTML =
			'<li id="unpublish"><a href="#" onclick="javascript:forceUnpublishStream(\'' + stream.streamId + '\')">Unpublish</a><br></li>'
			+ '<li id="connect"><a href="javascript:void(0)" class="getobj" onclick="javascript:joinTopFloor(\''+sid+','+cid+'\')">Add to TFloor</a></li>'
			container.appendChild(moderationControls);

			subscribers[stream.streamId] = session.subscribe(stream, divId);
		}

		function removeStream(streamId) {
			var subscriber = subscribers[streamId];
			if (subscriber) {
				var container = document.getElementById(subscriber.id).parentNode;

				session.unsubscribe(subscriber);
				delete subscribers[streamId];

				// Clean up the subscriber container
				document.getElementById("subscribers").removeChild(container);
			}
		}

		function show(id) {
			document.getElementById(id).style.display = 'block';
		}

		function hide(id) {
			document.getElementById(id).style.display = 'none';
		}


		window.onload = function(){
			$("h1").css("display","block");
			$("h1").animate({opacity:1},2300);
		}

		</script>
	</head>
	<body>
		<style>
			.title{float: right;
		    font-size: 120px;
		    margin: -27px 290px 0 0;
		    width: 310px;}
		</style>
		<div id="wrapper">
			<h1><img src="images/topfloor.png"/></h1>
			<p class="title">MANAGE<span style="font-size:13px">...add streams to the top floor. You can unpublish a stream if you want..</span><p>
			<a style="margin: 30px 0 0 0;
				float: left;
				text-decoration: none;"href="topfloor.php" target="_blank"><div>&nbsp;&nbsp;Top Floor page</div></a>
				<a style="margin: 30px 0 0 0;
					float: left;
					text-decoration: none;"href="includes/logout.php"><div>&nbsp;&nbsp;/&nbsp;&nbsp;logout</div></a>
			
			<div style="display:none" id="topBar">
				<div id="links">
					<!--<a href="#" id ="connectLin" onClick="javascript:connect()"><div id="con"><p>Administer</p></div></a>
					<a href="#" id ="disconnectLink" onClick="javascript:disconnect()" /><div id="dis"><p>Logout</p></div></a>-->
				</div>
			</div>
			<div id="myCamera" class="publisherContainer" ></div>
			<div id="subscribers"></div>
			<script>
			show('connectLink');
			</script>
		</div>
	</body>
	
	<script>
	$(".getobj").live("click", function(){
		var obj = $(this).parent().parent().parent();
		var oid = obj.children("object").attr("id");
		//  alert(oid);
		var flashvars = $('#'+oid+' param[name=flashvars]').attr('value');
		//alert(flashvars);

		var parobj = obj.attr("id");
		parobj = parobj.split("_");
		paraobj  = parobj[1];
		$.ajax({
			type: "POST",
			url: "includes/vars.php",
			data:({objid: oid, flashvars: flashvars, streamid: parobj[1]}),
			success: function() {
			}
		});	
		
		
		
		return false;
		
			

	});
	</script>

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
<script>
	$(document).ready(function(){
		connect();
	})
</script>

</body>

</html>
