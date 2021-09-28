<?php

$b_restricted_auth  = true;
$s_page_title       = 'Settings';
include_once '_common.php';

if ($_SERVER["REQUEST_METHOD"] == "POST")
    {        
        if (isset ($_POST["Set_Disable_authentication"]))
            {$disable_authentication = $_POST["Set_Disable_authentication"];}
            else
            {$disable_authentication = "False";}
        
        if (isset ($_POST["Set_Username"]))
            {$username = $_POST["Set_Username"];}
            else
            {$username = "";}
        
        $default_account = $_POST["Default_Account"];
        $default_account = $_POST["Default_Account"];
        
        if (isset ($_POST["Set_Disable_payee"]))
            {$disable_payee = $_POST["Set_Disable_payee"];}
            else
            {$disable_payee = "False";}
        
        if (isset ($_POST["Set_Disable_category"]))
            {$disable_category = $_POST["Set_Disable_category"];}
            else
            {$disable_category = "False";}
        
        $guid = $_POST["Set_Guid"];
        
        if (isset($_POST["Set_Password"]) && $_POST["Set_Password"] !== "" && $_POST["Set_Password"] !== Null)
            {$password = hash("sha512", $_POST["Set_Password"]);}
        else
            {
                if (isset ($_POST["Set_Disable_authentication"]))
                    {$password = "";}
                    else
                    {$password = costant::login_password();} 
            }
        
        $parameterarray = array
            (
                "disable_authentication"=> $disable_authentication,
                "user_username"         => $username,
                "user_password"         => $password,
                "disable_payee"         => $disable_payee,
                "disable_category"      => $disable_category,
                "defaultaccountname"    => $default_account,
                "desktop_guid"          => $guid
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

include_once '_header.php';

?>
    <div class="container">
        <?php
        if (isset($const_username) AND isset($const_password))
            {echo "<h3 class='text_align_center'>Edit settings</h3>";}
        else
                {
                echo "<br />";
                echo "<p style='text-align:center'><img src='res\mmex.png' alt='Money Manager EX Logo' height='150' width='150' /></p>";
                echo "<h3 class='text_align_center'>Insert new settings to start use Money Manager</h3>";
                }
        ?>
        <br />
        <form id="login" method="post" action="settings.php">
            <?php
                $const_disable_authentication = costant::disable_authentication();
                $const_username = costant::login_username();
                $const_password = costant::login_password();
                $const_desktop_guid = costant::desktop_guid();
                $const_disable_payee = costant::disable_payee();
                $const_disable_category = costant::disable_category();
                $const_defaultaccountname = costant::transaction_default_account();
                
                //SECTION AUTHENTICATION
                design::section_legened("Authentication");
                    if ($const_disable_authentication == True)
                        {design::settings_checkbox("Set_Disable_authentication",True,"Disable authentication (Not recommended)");}
                        else
                        {design::settings_checkbox("Set_Disable_authentication",False,"Disable authentication (Not recommended)");}

                    if (isset($const_username) && $const_disable_authentication == False)
                        {design::settings("Username",$const_username,"","Text",True);}
                        else
                        {design::settings("Username","","Insert a new username","Text",True);}
                    
                    if (isset($const_password) && $const_disable_authentication == False)
                        {design::settings_password("Password","To change insert a new password",False);}
                        else
                        {design::settings_password("Password","Insert a password",True);}
            
                    design::settings_password("Confirm_Password","Confirm new password",False);
                echo "<br />";
                
                //SECTION NEW TRANSACTIONS
                design::section_legened("New transactions");
                    if ($const_disable_payee == True)
                        {design::settings_checkbox("Set_Disable_payee",True,"Disable payees management");}
                        else
                        {design::settings_checkbox("Set_Disable_payee",False,"Disable payees management");}
                        
                    if ($const_disable_category == True)
                        {design::settings_checkbox("Set_Disable_category",True,"Disable categories management");}
                        else
                        {design::settings_checkbox("Set_Disable_category",False,"Disable categories management");}
                    
                    if (isset($const_defaultaccountname))
                        {design::settings_default_account($const_defaultaccountname);}
                        else
                        {design::settings_default_account("None");}
                echo "<br />";
                
                //SECTION DESKTOP INTEGRATION
                design::section_legened("Desktop integration");
                    if (isset($const_desktop_guid))
                        {design::settings("Guid",$const_desktop_guid,"","Text",True);}
                        else
                        {design::settings("Guid",security::generate_guid(),"","Text",True);}
                
            ?>
            <script type="text/javascript">
                function Disable_Authentication()
                    {
                        if (document.getElementById("Set_Disable_authentication").checked)
                            {
                                document.getElementById("Set_Username").disabled = true;
                                document.getElementById("Set_Username").value = "";
                                document.getElementById("Set_Password").disabled = true;
                                document.getElementById("Set_Password").value = "";
                                document.getElementById("Set_Confirm_Password").disabled = true;
                                document.getElementById("Set_Confirm_Password").value = "";
                                disable_confirm_password_if_empty ();
                            }
                        else
                            {
                                document.getElementById("Set_Username").disabled = false;
                                document.getElementById("Set_Password").disabled = false;
                                document.getElementById("Set_Confirm_Password").disabled = false;
                                disable_confirm_password_if_empty ();
                            }
                    }
                $("#Set_Disable_authentication").bind("change", Disable_Authentication);
                $("#Set_Password").bind("input", disable_confirm_password_if_empty);
                disable_confirm_password_if_empty();
                Disable_Authentication();
            </script>  
            <br />
            <?php
                if (isset($const_username) AND isset($const_password))
                    {
                        echo ("<button type='button' id='EditSettings' name='EditSettings' class='btn btn-lg btn-success btn-block' onclick='check_password_match_and_submit(\"Set_Password\",\"Set_Confirm_Password\",\"login\")'>Edit Settings</button>");
                        echo "<br />";
                        echo ("<a href='landing.php' class='btn btn-lg btn-success btn-block'>Return to menu</a>");
                    }
                else
                    {
                        echo ("<button type='button' id='EditSettings' name='EditSettings' class='btn btn-lg btn-success btn-block' onclick='check_password_match_and_submit(\"Set_Password\",\"Set_Confirm_Password\",\"login\")'>Apply Settings</button>");
                    }
                echo "<br />";
                echo "<br />";
            ?>
            <br />
        </form>
    </div>
	
	<script src="res/app/base-1.0.4.js" type="text/javascript"></script>
<?php

include_once '_footer.php';
