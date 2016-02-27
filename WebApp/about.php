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
        <meta name="mobile-web-app-capable" content="yes">
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
            <br />
            <img src="res/mmex.png" alt="Money Manager Ex Logo" height="150" width="150"/>
            <br />
            <h2><strong>Money Manager Ex - Web App</strong></h2>
            <?php
	           echo "<h4> Version ".costant::app_version()."</h4>";
               echo "<h5> API Version ".costant::api_version()."</h5>";
            ?>
            <br />
            <h4> Developer: Gabriele [Gabriele-V]</h4>
            <a href="https://github.com/moneymanagerex/web-money-manager-ex" target="_blank"><h4> GitHub WebPage</h4></a>
            <br />
            <p>
                Used components:
                <br />
                <a href="http://getbootstrap.com/" target="_blank">Bootstrap</a>
                <br />
                <a href="http://jquery.com//" target="_blank">jQuery</a>
                <br />
                <a href="http://modernizr.com/" target="_blank">Modernizr</a>
                <br />
                <a href="http://www.sqlite.org/" target="_blank">SQLite</a>
            </p>
            <img src="res/html5.png" alt="HTML5 Logo" height="100" width="100"/>
            <br />
            <br />
            <a href="landing.php" class="btn btn-lg btn-success btn-block">Return to menu</a>
            <br />
            <br />
        </div>
		
		<script src="res/app/base-1.0.4.js" type="text/javascript"></script>
	</body>
</html>