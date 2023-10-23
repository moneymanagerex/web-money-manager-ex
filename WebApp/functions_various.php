<?php

##########################
###  Various function  ###
##########################
class various
{
    public static function send_alert_and_redirect ($AlertMessage, $AlertRedirect)
        {
            echo '<script src="res/app/functions-1.2.0.js" type="text/javascript"></script>';
            echo '<script language="javascript">';
            if ($AlertRedirect <> 'None')
                {echo "send_alert_and_redirect ('${AlertMessage}','${AlertRedirect}')";}
            echo '</script>';
        }

    public static function update_configuration_file ($ParameterArray)
        {
            $configfile="configuration_user.php";

            if (file_exists($configfile))
                {unlink($configfile);}

            $fileopen = fopen($configfile, 'w');
            fwrite($fileopen, '<?php'."\n");
            fwrite($fileopen, '#######################'."\n");
            fwrite($fileopen, '##    User Setting   ##'."\n");
            fwrite($fileopen, '#######################'."\n");
            fwrite($fileopen, "\n");

            foreach ($ParameterArray as $key => $value)
                {fwrite($fileopen, "\$${key} = \"${value}\";\n");}

            fclose($fileopen);

            /**
             *  prevent caching of params
             *  force "refresh" loaded configuration after saving new data
             */
            self::clearCache( $configfile );

        }

    /**
     *  remove the cached version of the script from memory and force PHP to recompile
     *  opcache_invalidate is not always available. So it is better to check if it exists. Also check both of opcache_invalidate and apc_compile_file.
     *
     *  @param  String      path of the file to check and clear the cache for
     *  @return void
     */
    public static function clearCache(String $s_path_file) : void
    {
        if (file_exists($s_path_file))
        {
            if (function_exists('opcache_invalidate') && strlen(ini_get('opcache.restrict_api')) < 1)
            {
                opcache_invalidate($s_path_file, true);
            }
            elseif (function_exists('apc_compile_file'))
            {
                apc_compile_file($s_path_file);
            }
        }
    }

    public static function debug_to_file ($Value)
        {
            ob_start();
            var_dump($Value);
            $data = ob_get_clean();
            $fp = fopen("debug.txt", "w");
            fwrite($fp, $data);
            fwrite($fp, "\n");
            fwrite($fp, "///////////////////////////////////////////");
            fwrite($fp, "\n");
            fclose($fp);
        }

    /**
     *  when we need to know whta page is currently open
     *
     *  @param  void
     *
     *  @return String      current page URL
     */
    public static function getPageUrl() : String
    {
        $page_addr = $_SERVER['REQUEST_URI'];
        $page_url = (substr(trim($page_addr, '/'), 0, strpos($page_addr, '.')-1));
        return $page_url;
    }

    /**
     *  Pages list to show on Landing page as a menu
     *
     *  @param  void
     *
     *  @return Array       all pages with URL to Title values
     */
    public static function getPagesList() : Array
    {
        $pages_list             = [
                                    'new_transaction'   => costant::lang("page.new-transaction"),
                                    'show'              => costant::lang("page.show-transactions"),
                                    'settings'          => costant::lang("page.settings"),
                                    'guide'             => costant::lang("page.guide"),
                                    'about'             => costant::lang("page.about")
                                    ];
        return $pages_list;
    }

    /**
     *  Get page name
     *
     *  @param  String      page name override from specific page
     *
     *  @return String      current page name
     */
    public static function getPageName(?String $s_page_title = null) : String
    {
        $page_title = '';
        $pages_list = self::getPagesList();

        if (is_null($s_page_title))
        {
            $page_title .= $pages_list[various::getPageUrl()];
        }
        elseif (!empty($s_page_title))
        {
            $page_title .= $s_page_title;
        }

        return $page_title;
    }

    /**
     *  Get page full title including App name
     *
     *  @param  String      page name override from specific page
     *
     *  @return String      current page title
     */
    public static function getPageTitle(?String $s_page_title = '') : String
    {
        $page_title = '';
        $page_name = self::getPageName($s_page_title);

        if (costant::app_name() != $page_name && !empty($page_name))
        {
            $page_title .= $page_name;
            $page_title .= ' | ';
        }

        $page_title .= costant::app_name();

        return $page_title;
    }
}
