<?php
require_once "functions.php";

$const_username = costant::login_username();
$const_password = costant::login_password();
            
if (isset($const_username) AND isset($const_password))
    {
        session_start();
        security::redirect_if_not_loggedin();
    }

if ($_SERVER["REQUEST_METHOD"] == "POST")
    {        
        if(!empty($_POST["Set_Username"]) && !empty($_POST["Account"]))
            {
                $username = $_POST["Set_Username"];
                $account = $_POST["Account"];
                if (isset($_POST["Set_Password"]))
                    {
                        $password = hash("sha512", $_POST["Set_Password"]);
                    }
                else
                    {
                        $password = costant::login_password();
                    }
                
                $parameterarray = array(
                    "user_username"         => $username,
                    "user_password"         => $password,
                    "defaultaccountname"    => $account
                );
                
                various::update_configuration_file($parameterarray);
                header("Location: landing.php");
            }
        else
            {
                header("Location: landing2.php");
            }
    }
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
        
        <script src="functions.js" type="text/javascript"></script>
    </head>
    
    <body>
    <div class="container">
        <?php
        if (isset($const_username) AND isset($const_password))
            {
                echo "<h2 align = 'center'>Edit settings</h2>";
            }
        else
            {
                echo "<br />";
                echo "<p style='text-align:center'><img src='res\mmex.ico' alt='Money Manager EX Logo' height='150' width='150' /></p>";
                echo "<br />";
                echo "<h2 align = 'center'>Insert new settings to start use Money Manager</h2>";
            }
        ?>
        <br />
        <form id="login" method="post">
            <?php
                require_once "functions.php";
                $const_username = costant::login_username();
                $const_password = costant::login_password();
                $const_defaultaccountname = costant::transaction_account_default();
                
                if (isset($const_username))
                    {
                        design::input_setting("Username",$const_username,"","Text",True);
                    }
                else
                    {
                        design::input_setting("Username","","Inser a username","Text",True);
                    }
                
                if (isset($const_password))
                    {
                        #design::input_setting("Password",$const_password,"","Password");
                        design::input_setting_password("Password","To change insert a new password",False,"");
                    }
                else
                    {
                        design::input_setting_password("Password","Insert a password",True,"disable_element(".'"'."Set_Confirm_Password".'","'."Set_Password".'","")');
                    }
        
                design::input_setting_password("Confirm_Password","Confirm new password",False,"");
                
                if (isset($const_defaultaccountname))
                    {
                        design::input_account($const_defaultaccountname);
                    }
                else
                    {
                        design::input_account("None");
                    }
                
            ?>
            <br />
            <button type="submit" id="EditSettings" name="EditSettings" class="btn btn-lg btn-success btn-block">Edit Settings</button>
        </form>
    </body>
</html>