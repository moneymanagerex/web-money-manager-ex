<?php
require_once "functions.php";
$error = db_function::db_create();
if ($error !== "ok")
    {
        echo $error;
    }
else
    {
        $upgrade_result = db_upgrade::upgrade_db();
        if ($upgrade_result == "update_done")
            {
                various::send_alert_and_redirect("Database succesfully updated to version ".costant::app_version(),"index.php");
            }
    }
$username = null;
$password = null;

$const_username = costant::login_username();
$const_password = costant::login_password();
$const_disable_authentication = costant::disable_authentication();

if ($const_disable_authentication == "True")
    {
        header("Location: landing.php");
    }
       
if ($const_disable_authentication !== "True" && (!isset($const_username) OR !isset($const_password)))
    {
        header("Location: settings.php");
    }

if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        if(!empty($_POST["Username"]) && !empty($_POST["Password"]))
        {
            $username = $_POST["Username"];
            $password = hash("sha512", $_POST["Password"]);
            
            if($username == $const_username && $password == $const_password)
            {
                session_start();
                $user_browser = $_SERVER['HTTP_USER_AGENT'];
                $_SESSION["username"] = $username;
                $_SESSION["login_string"] = hash("sha512", $password . $user_browser);
                header("Location: landing.php");
                
            }
            else
            {
                header("Location: index.php");
            }
        
        }
        else
        {
            header("Location: index.php");
        }
    }
else
{
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
            <form id="login" method="post">
                <div class="form-group">
                    <label for="Username">Username</label>
                    <input id="Username" type="text" name="Username" class="form-control" placeholder="Insert Username" required />
                    <span class="help-block"></span>
                </div>
                <div class="form-group">
                    <label for="Password">Password</label>
                    <input id="Password" type="password" name="Password" class="form-control" placeholder="Insert Password" required />
                    <span class="help-block"></span>
                </div>
                <br />
                <button type="submit" id="Login" name="Login" class="btn btn-lg btn-success btn-block" value = "Login">Login</button>
            </form>
        </div>
    </body>
</html>
<?php
}
?>