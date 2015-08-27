<?php
require_once "functions.php";
#session_start();
#security::redirect_if_not_loggedin();
?>

<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
    	
        <title>Money Manager EX</title>
        <link rel="icon" href="res/favicon.ico" />
        <link rel="apple-touch-icon" href="res/apple-touch-icon.png" />
        
        <link rel="stylesheet" type="text/css" href="res/bootstrap-3.2.0.min.css" />
        <link rel="stylesheet" type="text/css" href="res/bootstrap-theme-3.2.0.min.css" />
        <link rel="stylesheet" type="text/css" href="res/style_global-0.9.9.css" />
    </head>
    
    <body>
        <div class="container text_align_center">
            <br />
            <img src="res/mmex.ico" alt="Money Manager Ex Logo" height="150" width="150"/>
            <br />
            <h2><strong>Money Manager Ex - Web App</strong></h2>
            <?php
	           echo "<h4> Version ".costant::app_version()."</h4>";
               echo "<h5> API Version ".costant::api_version()."</h5>";
            ?>
            <br />
            <h4> Developer: Gabriele [Gabriele-V]</h4>
            <a href="https://github.com/moneymanagerex/web-money-manager-ex"><h4> GitHub WebPage</h4></a>
            <br />
            <p>
                Used components:
                <br />
                <a href="http://getbootstrap.com/">Bootstrap</a>
                <br />
                <a href="http://jquery.com//">jQuery</a>
                <br />
                <a href="http://modernizr.com/">Modernizr</a>
                <br />
                <a href="http://www.sqlite.org/">SQLite</a>
            </p>
            <img src="res/html5.png" alt="HTML5 Logo" height="100" width="100"/>
            <br />
            <br />
            <input type="button" class="btn btn-lg btn-success btn-block" value="Return to menu" onclick="top.location.href = 'landing.php'" />
            <br />
            <br />
        </div>
    </body>

</html>