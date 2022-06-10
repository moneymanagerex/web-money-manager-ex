<?php

#########################
###     Security      ###
#########################
class security
{
    public static function redirect_if_not_loggedin ()
        {
            $disable_authentication = costant::disable_authentication();
            if (($disable_authentication) == False)
            {
                if(isset($_SESSION["login_string"],$_SESSION["username"]))
                {
                    $password = costant::login_password();
                    $login_string = $_SESSION["login_string"];
                    $username = $_SESSION["username"];
                    $user_browser = $_SERVER["HTTP_USER_AGENT"];

                    $login_check = hash('sha512', $password . $user_browser);
                    if ($login_check !== $login_string)
                        {header("Location: index.php");}
                }
                elseif ($_SERVER['PHP_SELF'] != "/index.php")
                {
                    header("Location: index.php");
                }
            }
        }


    public static function generate_guid()
        {
            if (function_exists('com_create_guid'))
                {return com_create_guid();}
            else
                {
                    mt_srand((double)microtime()*10000);
                    $charid = strtoupper(md5(uniqid(rand(), true)));
                    $hyphen = chr(45);// "-"
                    $uuid = chr(123)// "{"
                    .substr($charid, 0, 8).$hyphen
                    .substr($charid, 8, 4).$hyphen
                    .substr($charid,12, 4).$hyphen
                    .substr($charid,16, 4).$hyphen
                    .substr($charid,20,12)
                    .chr(125);// "}"
                    return $uuid;
                }
        }
}
