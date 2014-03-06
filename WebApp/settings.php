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
        if (isset ($_POST["Set_Disable_authentication"]))
            {
                $disable_authentication = $_POST["Set_Disable_authentication"];
            }
        else
            {
                $disable_authentication = "False";
            }
        if (isset ($_POST["Set_Username"]))
            {
                $username = $_POST["Set_Username"];
            }
        else
            {
                $username = "";
            }
        
        $account = $_POST["Account"];
        
        if (isset ($_POST["Set_Disable_payee"]))
            {
                $disable_payee = $_POST["Set_Disable_payee"];
            }
        else
            {
                $disable_payee = "False";
            }
        
        $guid = $_POST["Set_Guid"];
        
        if (isset($_POST["Set_Password"]) && $_POST["Set_Password"] !== "" && $_POST["Set_Password"] !== Null)
            {
                $password = hash("sha512", $_POST["Set_Password"]);
            }
        else
            {
                if (isset ($_POST["Set_Disable_authentication"]))
                    {
                        $password = "";
                    }
                else
                    {
                        $password = costant::login_password();
                    }
                
            }
        
        $parameterarray = array
            (
                "disable_authentication"=> $disable_authentication,
                "user_username"         => $username,
                "user_password"         => $password,
                "disable_payee"         => $disable_payee,
                "desktop_guid"          => $guid,
                "defaultaccountname"    => $account
            );
              
        if (file_exists("configuration_user.php"))
            {
                various::update_configuration_file($parameterarray);
                header("Location: landing.php");
            }
        else
            {
                various::update_configuration_file($parameterarray);
                header("Location: guide.php");
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
                echo "<h3 align = 'center'>Edit settings</h3>";
            }
        else
            {
                echo "<br />";
                echo "<p style='text-align:center'><img src='res\mmex.ico' alt='Money Manager EX Logo' height='150' width='150' /></p>";
                echo "<h3 align = 'center'>Insert new settings to start use Money Manager</h3>";
            }
        ?>
        <br />
        <form id="login" method="post" action="settings.php">
            <?php
                require_once "functions.php";
                $const_disable_authentication = costant::disable_authentication();
                $const_username = costant::login_username();
                $const_password = costant::login_password();
                $const_desktop_guid = costant::desktop_guid();
                $const_disable_payee = costant::disable_payee();
                $const_defaultaccountname = costant::transaction_account_default();
                
                echo "<div class='checkbox'>";
                    echo "<label>";
                        if ($const_disable_authentication == True)
                            {
                                echo "<input id='Set_Disable_authentication' type='checkbox' name='Set_Disable_authentication' value='True' onchange = 'Disable_Authentication()' checked>Disable authentication (Not recommended)";
                            }
                        else
                            {
                                echo "<input id='Set_Disable_authentication' type='checkbox' name='Set_Disable_authentication' value='True' onchange = 'Disable_Authentication()'>Disable authentication (Not recommended)";
                            }
                    echo "</label>";
                echo "</div>";

                
                if (isset($const_username) && $const_disable_authentication == False)
                    {
                        design::input_setting("Username",$const_username,"","Text",True);
                    }
                else
                    {
                        design::input_setting("Username","","Insert a new username","Text",True);
                    }
                
                if (isset($const_password) && $const_disable_authentication == False)
                    {
                        #design::input_setting("Password",$const_password,"","Password");
                        design::input_setting_password("Password","To change insert a new password",False,"");
                    }
                else
                    {
                        design::input_setting_password("Password","Insert a password",True,"disable_element_if_empty(".'"'."Set_Confirm_Password".'","'."Set_Password".'")');
                    }
        
                design::input_setting_password("Confirm_Password","Confirm new password",False,"");
                
                echo "<div class='checkbox'>";
                    echo "<label>";
                        if ($const_disable_payee == True)
                            {
                                echo "<input id='Set_Disable_payee' type='checkbox' name='Set_Disable_payee' value='True' checked>Disable payees management";
                            }
                        else
                            {
                                echo "<input id='Set_Disable_payee' type='checkbox' name='Set_Disable_payee' value='True'>Disable payees management";
                            }
                    echo "</label>";
                echo "</div>";
                
                if (isset($const_desktop_guid))
                    {
                        design::input_setting("Guid",$const_desktop_guid,"","Text",True);
                    }
                else
                    {
                        design::input_setting("Guid",security::generate_guid(),"","Text",True);
                    }
                
                if (isset($const_defaultaccountname))
                    {
                        design::input_account($const_defaultaccountname);
                    }
                else
                    {
                        design::input_account("None");
                    }
                
            ?>
            <script type="text/javascript">
                function Disable_Authentication()
                    {
                        if (document.getElementById("Set_Disable_authentication").checked)
                            {
                                document.getElementById("Set_Username").disabled = true;
                                document.getElementById("Set_Password").disabled = true;
                                document.getElementById("Set_Confirm_Password").disabled = true;
                                disable_element_if_empty ("Set_Confirm_Password","Set_Password");
                            }
                        else
                            {
                                document.getElementById("Set_Username").disabled = false;
                                document.getElementById("Set_Password").disabled = false;
                                document.getElementById("Set_Confirm_Password").disabled = false;
                                disable_element_if_empty ("Set_Confirm_Password","Set_Password");
                            }
                    }
                disable_element_if_empty ("Set_Confirm_Password","Set_Password");
                Disable_Authentication();
            </script>  
            <br />
            <?php
                if (isset($const_username) AND isset($const_password))
                    {
                        echo ("<button type='button' id='EditSettings' name='EditSettings' class='btn btn-lg btn-success btn-block' onclick='checkcheck_password_match_and_submit(".'"'."Set_Password".'","'."Set_Confirm_Password".'","'."settings.php".'","'."login".'"'.")'>Edit Settings</button>");
                    }
                else
                    {
                        echo ("<button type='button' id='EditSettings' name='EditSettings' class='btn btn-lg btn-success btn-block' onclick='checkcheck_password_match_and_submit(".'"'."Set_Password".'","'."Set_Confirm_Password".'","'."settings.php".'","'."login".'"'.")'>Apply Settings</button>");
                    }
            ?>
            <br />
        </form>
    </body>
</html>