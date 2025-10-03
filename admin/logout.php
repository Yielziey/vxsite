<?php
session_start();
$_SESSION = array();
session_destroy();
// I-redirect sa login.php
header("location: login.php");
exit;
?>
