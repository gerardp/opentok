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
	header("Location: http://66.65.171.39:8000/opentok/login.php"); 
}
$naitik = $facebook->api('/naitik');
?>