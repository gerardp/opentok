<?php
// Begin session
session_start();
// Delete certain session
unset($_SESSION['username']);
// Delete all session variables
session_destroy();  
// Jump to index page
header('Location: ../index.php');

?>