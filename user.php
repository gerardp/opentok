<?php
// Begin session
session_start();
	
// If username is not set, then go to the index page
if (!isset($_SESSION['username'])) {
	header('Location: index.php');
}
// Include database connection settings.
include('includes/config.php')
?>
<?php
require 'facebook.php';
$facebook = new Facebook(array(
  'appId'  => '100697156725418',
  'secret' => 'e9e8f318673716c764c63f24801a6523',
));
$user = $facebook->getUser();
if ($user) {
  try {
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
}
if ($user) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
}
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
	<title>OpenTok API Sample &#151; Basic Tutorial</title>
	<link href="css/user.css" type="text/css" rel="stylesheet" >
	<link rel="shortcut icon" type="image/x-icon" href="images/favi.ico">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>	
	<script src="jqueryswf.js"></script>	
	<script src="http://static.opentok.com/v0.91/js/TB.min.js"></script>	
	<script type="text/javascript" charset="utf-8">

	function porra(e){
		apiKey = <?php print API_Config::API_KEY?>;
		sessionId = e;
		token = '<?php print $apiObj->generate_token(); ?>';	// Add to the page using the OpenTok server-side libraries.
	}


	porra('1_MX4xMTQwOTQ0Mn4xOTIuMTY4LjEuMX4yMDEyLTAxLTI3IDIwOjExOjQ2LjM3MTMzNCswMDowMH4wLjM3MjQzNzY2MjQ0MX4');


	var session = null;
	var publisher = null;

	var PUBLISHER_WIDTH = 100;
	var PUBLISHER_HEIGHT = 100;
	var SUBSCRIBER_WIDTH = 160;
	var SUBSCRIBER_HEIGHT = 120;

	var participants = 0;
	var watchers = 0;
	var ismember = 1;;

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
			$.ajax({
				type: "POST",
				url: "includes/removeguest.php",
				data:({streamId: sid}),
				success: function() {
				}
			});
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
		document.getElementById("action").innerHTML = '<a href="#" id="streamlg" onclick="startPublishing()"><div class="streambtn"><p>Start Streaming</p></div></a>';

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

		// AJAX
		var sid =  event.streams[0].streamId;
		var cid = event.streams[0].connection.connectionId;
		ismember = sid;
		
		$.ajax({
			type: "POST",
			url: "includes/form.php",
			data:({streamId: sid, connectionId: cid,}),
			success: function() {
			}
		});

		// Display streams on screen, except for this page's own stream.

		for (var i = 0; i < event.streams.length; i++) {



			if (event.streams[i].connection.connectionId != event.target.connection.connectionId) {
				subscribeToStream(event.target, event.streams[i]);
				watchers--;
			} else {
				// Our publisher just started streaming



				//alert(event.streams.toSource())
				//alert(event.streams[0].streamId);
				//alert(event.streams[0].connection.connectionId);




				// Update status, controls and counts
				document.getElementById("status").innerHTML = "You are participating in the call";
				document.getElementById("action").innerHTML = '<a href="#" onclick="stopPublishing()"><div id="leavecall"><p style="margin:9px 38px;font-size:13px">Leave call</p></div></a>';

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
				document.getElementById("action").innerHTML = '<a href="#" onclick="startPublishing()"><div class="streambtn"><p style="margin:9px 40px">Join call</p></div></a>';

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
	function lala(){
		//alert($("object").attr("id"));
		var a = [];
		$("#myCamera > object").each(function(){
			var param = $("param[name='flashvars']").val();
			var id = $(this).attr("id");
			a.push(id);
			vai(param,id)
		});
		function vai(param,id){
			$("#streams").append('<div class="users">'+										
			'<object width="150" height="150" type="application/x-shockwave-flash" id="publisher_1_MX4xMTQwOTQ0Mn4xOTIuMTY4LjEuMX4yMDEyLTAxLTI3IDIwOjExOjQ2LjM3MTMzNCswMDowMH4wLjM3MjQzNzY2MjQ0MX4_1" style="outline:none;" data="http://static.opentok.com/v0.91.43.6486422/flash/f_publishwidget.swf?partnerId=11409442">'+
			'<param name="allowscriptaccess" value="always">'+
			'<param name="cameraSelected" value="false">'+
			'<param name="wmode" value="transparent">'+
			'<param name="flashvars" value="width=420&height=265&publisherId=publisher_1_MX4xMTQwOTQ0Mn4xOTIuMTY4LjEuMX4yMDEyLTAxLTI3IDIwOjExOjQ2LjM3MTMzNCswMDowMH4wLjM3MjQzNzY2MjQ0MX4_1&connectionId=c90a0f85be955d187000d6b39a8b27113c1ec8f0&sessionId=1_MX4xMTQwOTQ0Mn4xOTIuMTY4LjEuMX4yMDEyLTAxLTI3IDIwOjExOjQ2LjM3MTMzNCswMDowMH4wLjM3MjQzNzY2MjQ0MX4&token=T1==cGFydG5lcl9pZD0xMTQwOTQ0MiZzZGtfdmVyc2lvbj10YnBocC12MC45MS4yMDExLTEwLTEyJnNpZz1iNmI0NzhlYjYyMWM4MDY2ZDcxNmY2NWRkNmY0OWU1OTk5ZWY3Njk4OnNlc3Npb25faWQ9JmNyZWF0ZV90aW1lPTEzMjc5Nzk3MTcmcm9sZT1wdWJsaXNoZXImbm9uY2U9MTMyNzk3OTcxNy4xNDE2MTY4MzQzMg==&cameraSelected=false&simulateMobile=false&publishCapability=1&startTime=1327979722186">'+
			'</object>'+
			'</div>');
		}
	}

/*$(document).ready(function(){
$("#streams").append('<div class="users">'+										
'<object width="150" height="150" type="application/x-shockwave-flash" id="subscriber_1061994175_1" style="outline:none;" data="http://static.opentok.com/v0.91.43.6486422/flash/f_subscribewidget.swf?partnerId=11409442">'+
'<param name="allowscriptaccess" value="always">'+
'<param name="cameraSelected" value="false">'+
'<param name="wmode" value="transparent">'+
'<param name="flashvars" value="TQwOTQ0MiZzZGtfdmVyc2lvbj10YnBocC12MC45MS4yMDExLTEwLTEyJnNpZz1iNmI0NzhlYjYyMWM4MDY2ZDcxNmY2NWRkNmY0OWU1OTk5ZWY3Njk4OnNlc3Npb25faWQ9JmNyZWF0ZV90aW1lPTEzMjc5Nzk3MTcmcm9sZT1wdWJsaXNoZXImbm9uY2U9MTMyNzk3OTcxNy4xNDE2MTY4MzQzMg==&cameraSelected=false&simulateMobile=false&publishCapability=1&startTime=1327979722186">'+
'</object>'+
'</div>');
})*/


</script>


</head>
<body>
	<div id="wrapperUser">
		<p>
	        <fb:login-button autologoutlink="true" perms="email,user_birthday,status_update,publish_stream"></fb:login-button>
	    </p>
		<h1 style="opacity:0"><img src="images/topfloor.png"/></h1>
		<div class = "rightbox">
			<div class="controls">
				<div id="status" style='font:18px Arial,"Bitstream Vera Sans",sans-serif;'>You are connecting to the call</div>
				<div id="action" style="padding-bottom: 6px">&nbsp;</div>
			</div>
		</div>
		<div id="localview">
			<div id="myCamera" class="publisherContainer"></div>
		</div>
		<div id="streams"></div>
		<div id="streams">
		</div>

		<div id="main">
			<div id="stream_1" class="right-pic"></div>
		</div>
		<div>

			<script type="text/javascript">
			// Set debugging level if wanted
			// TB.setLogLevel(TB.DEBUG);
			var welcome = false;
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


			var refreshId = setInterval(check, 7000);
			
			$("#streamlg").animate({opacity:1},1000)
			

			function check() {
				var y = "yes";
				$.ajax({
					type: "POST",
					url: "includes/check.php",
					data:({join: y}),
					success: function(data) {
						porra()
					}
				});
			}
			var go = false;
			var list = new Array();
			var members = new Array();
			var arr = new Array()
			
			function porra(){
				if(ismember != 1 && ismember!=members[0]){
					members.push(ismember)
				}
			
				var users = $(".users").length;
				var sessionString = "";
				var sessionArray = new Array();
				var sessionSplit = "";
				var finalFlashvars
				
				$.getJSON('json/obj.json', function(e) {
					$.each(e, function(l, v){
						arr.push(v.objid);
					})
					
					$.each(e, function(i, item) {
						
				/*		 sessionSplit = item.flashvars.split("&");
						 sessionSplit[2] = "sessionId=1_MX4xMTQwOTQ0Mn4xMjcuMC4wLjF-MjAxMi0wMi0wMSAwMzo1MDoxMy4yMDQ5NjIrMDA6MDB-MC41NTQ0Mzc5MjEyMTV-";
						for(d=0;d<=sessionSplit.length;d++){
							sessionArray.push(sessionSplit[d]+"&");
						}
						for(k=0;k<=sessionArray.length;k++){
							finalFlashvars+=sessionArray[k];
								
						}
						finalFlashvars	*/
						
											
						if(ismember == item.streamId ){
							go = true;
						}
						
						if(go == true){
						/*	if ($.inArray(item.objid, list) === -1 && $.inArray(item.flashvars, list) === -1  ) {
								if(e != e.length){
									$("#streams").append('<div class="users">'+										
									'<object width="150" height="150" type="application/x-shockwave-flash" id="'+item.objid+'" style="outline:none;" data="http://static.opentok.com/v0.91.43.6486422/flash/f_subscribewidget.swf?partnerId=11409442">'+
									'<param name="allowscriptaccess" value="always">'+
									'<param name="cameraSelected" value="false">'+
									'<param name="wmode" value="transparent">'+
									'<param name="flashvars" value="'+item.flashvars+'">'+
									'</object>'+
									'</div>');	
									list.push(item.objid,item.flashvars);
								}
							}*/
						}
					//	alert(arr);
						$(".users > object").each(function(){
							var getid = $(this).attr("id");
							if($.inArray(getid, arr) === -1){
								$(this).parent().remove();
							}
						})
					});
				});
				arr = []
			}

			</script>
		</body>

		</html>



