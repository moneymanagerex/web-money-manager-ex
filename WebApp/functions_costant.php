<?php

##########################
### Constant  function ###
##########################
class costant
    {
        public static function login_username ()
            {
                global $user_username;
                return $user_username;
            }

        public static function login_password ()
            {
                global $user_password;
                return $user_password;
            }

        public static function transaction_default_account ()
            {
                global $defaultaccountname;
                return $defaultaccountname;
            }

        public static function transaction_default_status ()
            {
                global $tr_default_status;
                return $tr_default_status;
            }

        public static function transaction_default_type ()
            {
                global $tr_default_type;
                return $tr_default_type;
            }
        public static function attachments_folder ()
            {
                global $attachments_folder;
                return $attachments_folder;
            }
        public static function database_path ()
            {
                global $dbpath;
                return $dbpath;
            }

        public static function desktop_guid ()
            {
                global $desktop_guid;
                return $desktop_guid;
            }

        public static function app_version ()
            {
                global $app_version;
                return $app_version;
            }

        public static function app_name ()
            {
                global $app_name;
                return $app_name;
            }

        public static function api_version ()
            {
                global $api_version;
                return $api_version;
            }

        public static function disable_authentication ()
            {
                global $disable_authentication;
                if ($disable_authentication == "True")
                    {return True;}
                else
                    {return False;}
            }

        public static function disable_payee ()
            {
                global $disable_payee;
                if ($disable_payee == "True")
                    {return True;}
                else
                    {return False;}
            }

        public static function disable_category ()
            {
                global $disable_category;
                if ($disable_category == "True")
                    {return True;}
                else
                    {return False;}
            }

        public static function current_page_url ()
            {
             $pageURL = $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER["HTTP_HOST"];
             if ($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443")
                {$pageURL .= ':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];}
             return $pageURL;
            }

        public static function uiLanguage ()
            {
                global $language;
                return $language;
            }

        public static function lang ($Key)
        {
            global $lang;
            return $lang[$Key];
        }
    }
