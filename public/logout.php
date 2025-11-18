<?php require_once "includes/header.php"; ?>
<?php
session_start();
session_destroy();
header("Location: login.php");
?>
