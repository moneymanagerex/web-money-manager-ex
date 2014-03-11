<?php
require_once "functions.php";
#session_start();
#security::redirect_if_not_loggedin();
?>

<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    	
        <title>Money Manager EX</title>
        <link rel="icon" href="res/favicon.ico" />
        
        <link rel="stylesheet" type="text/css" href="res/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="res/bootstrap-theme.min.css" />
        <link rel="stylesheet" type="text/css" href="style_global.css" />
    </head>
    
    <body>
        <div class="container text_align_center">
            <br />
            <img src="res/mmex.ico" alt="Money Manager Ex Logo" height="150" width="150"/>
            <br />
            <h2><strong>Money Manager Ex - Web App</strong></h2>
            <?php
	           echo "<h4> Version ".costant::app_version()."</h4>";
            ?>
            <br />
            <h4> Developer: Gabriele [Gabriele-V]</h4>
            <a href="https://sourceforge.net/projects/moneymanagerex-webapp"><h4> Sourceforge WebPage</h4></a>
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
            <input type="button" class="btn btn-lg btn-success btn-block" value="Return" onclick="top.location.href = 'landing.php'" />
            <br />
        </div>
    </body>

</html>