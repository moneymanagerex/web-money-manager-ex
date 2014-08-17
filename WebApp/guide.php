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
            <input type="button" class="btn btn-lg btn-success btn-block" value="Return to menu" onclick="top.location.href = 'landing.php'" />
            <br />
            <br />
        </div>
    </body>
</html>