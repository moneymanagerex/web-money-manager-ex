<?php
require_once "functions.php";
session_start();
security::redirect_if_not_loggedin();

#Return payee default category
if (isset($_GET["get_default_category"]))
    {
        $ArrDefaultCategory = db_function::payee_select_one($_GET["get_default_category"]);
        echo json_encode($ArrDefaultCategory);
    }
    
#Return subcategory
if (isset($_GET["get_subcategory"]))
    {
        $ArrSubCategory = db_function::subactegory_select_all($_GET["get_subcategory"]);
        echo json_encode($ArrSubCategory);
    }
?>