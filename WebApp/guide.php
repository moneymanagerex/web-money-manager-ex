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
            <h3><strong>Guide</strong></h3>
            <br />
            <p>
                To start use Money Manager Ex WebApp you need to insert this data in your existing desktop installation
            </p>
            <?php
                echo "<p>WebApp URL:</p>";
                echo "<p><strong>".str_replace("/guide.php","",costant::current_page_url())."</strong></p>";
                echo "<br />";
                echo "<p>Desktop GUID:</p>";
                echo "<p><strong>".costant::desktop_guid()."</strong></p>";
                echo "<br />";
                echo "<br />";
            ?>
            <!--
            <input type="button" class="btn btn-default" value="Copy URL" />
            &nbsp;&nbsp;
            <input type="button" class="btn btn-default" value="Copy GUID" />
            -->
            <input type="button" class="btn btn-lg btn-success btn-block" value="Return to menu" onclick="top.location.href = 'landing.php'" />
            <br />
        </div>
    </body>
</html>