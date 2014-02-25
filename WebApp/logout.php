<?php
require_once "functions.php";
session_start();
    $_SESSION["username"] = null;
    $_SESSION["login_string"] = null;
session_destroy();
header("Location: index.php");
?>