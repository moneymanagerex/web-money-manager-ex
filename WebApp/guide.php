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
            <h3><strong>Guide</strong></h3>
            <br />
            <p>
                To start use Money Manager Ex WebApp you need to insert this data in your existing desktop installation
            </p>
            <?php
                echo "<p>WebApp URL:</p>";
                    $CurrentPage = str_replace("/guide.php","",costant::current_page_url());
                echo "<p><strong>".$CurrentPage."</strong></p>";
                echo "<br />";
                echo "<p>Desktop GUID:</p>";
                echo "<p><strong>".costant::desktop_guid()."</strong></p>";
                echo "<br />";
                echo "<br />";
            ?>
            <p>
                Please open desktop version to import bank account and start use Web version
            </p>
            <br />
            <br />
            <!--
            <input type="button" class="btn btn-default" value="Copy URL" />
            &nbsp;&nbsp;
            <input type="button" class="btn btn-default" value="Copy GUID" />
            -->
            <a href="landing.php" class="btn btn-lg btn-success btn-block">Return to menu</a>
            <br />
            <br />
        </div>
		
		<script src="res/app/base-1.0.3.js" type="text/javascript"></script>
    </body>
</html>