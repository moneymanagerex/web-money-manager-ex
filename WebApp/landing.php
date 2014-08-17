<?php
require_once "functions.php";
session_start();
security::redirect_if_not_loggedin();
?>

<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    	
        <title>Money Manager EX</title>
        <link rel="icon" href="res/favicon.ico" />
        
        <link rel="stylesheet" type="text/css" href="res/bootstrap-3.2.0.min.css" />
        <link rel="stylesheet" type="text/css" href="res/bootstrap-theme-3.2.0.min.css" />
        <link rel="stylesheet" type="text/css" href="res/style_global-0.9.9.css" />
    </head>
    
    <body>
        <div class="container text_align_center">
            <h2><strong>Money Manager EX</strong></h2>
            <br />
            <img src="res/mmex.ico" alt="Money Manager EX Logo" height="150" width="150"/>
            <br />
            <br />
            <br />
            <input type="button" class="btn btn-lg btn-success btn-block" value="New transaction" onclick="top.location.href = 'new_transaction.php'" />
            <br />
            <input type="button" class="btn btn-lg btn-success btn-block" value="Show transaction" onclick="top.location.href = 'show.php'" />
            <br />
            <input type="button" class="btn btn-lg btn-success btn-block" value="Edit settings" onclick="top.location.href = 'settings.php'" />
            <br />
            <input type="button" class="btn btn-lg btn-success btn-block" value="Guide" onclick="top.location.href = 'guide.php'" />
            <br />
            <input type="button" class="btn btn-lg btn-success btn-block" value="About" onclick="top.location.href = 'about.php'" />
            <br />
            <input type="button" class="btn btn-lg btn-success btn-block" value="Logout" onclick="top.location.href = 'logout.php'" />
            <br />
        </div>
    </body>

</html>