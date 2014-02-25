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
        <link rel="icon" href="res\favicon.ico" />
        
        <link rel="stylesheet" href="res\bootstrap.min.css" />
        <link rel="stylesheet" href="res\bootstrap-theme.min.css" />
        <link rel="stylesheet" type="text/css" href="style_global.css" />
    </head>
    
    <body>
        <div class="container" align="center">
            <h2><strong>Money Manager EX - Web App</strong></h2>
            <br />
            <img src="res\mmex.ico" alt="Money Manager EX Logo" height="150" width="150"/>
            <br />
            <br />
            <br />
            <h4> Copyright 2014 - Gabriele</h4>
            <br />
            <p>
                Used components:
                <br />
                <a href="http://getbootstrap.com/">Bootstrap</a>
                <br />
                <a href="http://modernizr.com/">Modernizr</a>
                <br />
                <a href="http://www.sqlite.org/">SQLite</a>
            </p>
            <br />
            <br />
        </div>
    </body>

</html>