<?php
require_once "functions.php";
session_start();
security::redirect_if_not_loggedin();
?>

<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1" />
        <meta name="apple-mobile-web-app-title" content="MMEX">
		<meta name="apple-mobile-web-app-capable" content="yes" />
    	
        <title>Money Manager EX</title>
        <link rel="icon" href="res/favicon.ico" />
        <link rel="apple-touch-icon" href="res/mmex.png" />
        
        <link rel="stylesheet" type="text/css" href="res/bootstrap-3.3.6.min.css" />
        <link rel="stylesheet" type="text/css" href="res/bootstrap-theme-3.3.6.min.css" />
        <link rel="stylesheet" type="text/css" href="res/style_global-0.9.9.css" />
		
		<script src="res/jquery-2.1.4.min.js" type="text/javascript"></script>
    </head>
    
    <body>
        <div class="container text_align_center">
            <h2><strong>Money Manager EX</strong></h2>
            <br />
            <img src="res/mmex.png" alt="Money Manager EX Logo" height="150" width="150"/>
            <br />
            <br />
            <br />
            <a href="new_transaction.php" class="btn btn-lg btn-success btn-block">New transaction</a>
            <br />
            <a href="show.php" class="btn btn-lg btn-success btn-block">Show transaction</a>
            <br />
            <a href="settings.php" class="btn btn-lg btn-success btn-block">Edit settings</a>
            <br />
            <a href="guide.php" class="btn btn-lg btn-success btn-block">Guide</a>
            <br />
            <a href="about.php" class="btn btn-lg btn-success btn-block">About</a>
            <br />
            <a href="logout.php" class="btn btn-lg btn-success btn-block">Logout</a>
            <br />
        </div>
		
		<script src="res/app/base-1.0.3.js" type="text/javascript"></script>
    </body>
</html>