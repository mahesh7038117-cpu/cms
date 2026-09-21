<?php
session_start();
session_destroy(); // Destroy active session
header("Location: login.php");
exit();
?>
