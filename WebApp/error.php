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
        <meta name="apple-mobile-web-app-title" content="MMEX">
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
            <h3><strong>Internal Error</strong></h3>
            <?php
	           echo "<h4> Version ".costant::app_version()."</h4>";
            ?>
            <br />
            <h4> Please contact developer Gabriele [Gabriele-V]</h4>
            <a href="https://github.com/moneymanagerex/web-money-manager-ex" target="_blank"><h4> on GitHub WebPage</h4></a>
            <br />
            <br />
        </div>
    </body>
</html>