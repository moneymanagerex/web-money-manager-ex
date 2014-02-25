<?php
# Import configuration file
require_once "functions.php";

#Delete all transaction
if (isset($_GET['deleteall']))
    {
        $string_delete_get = $_GET['deleteall'];;
        $const_desktop_guid = costant::desktop_guid();
        
        if ($string_delete_get == $const_desktop_guid)
        {
            db_function::transaction_delete_all();
            echo "All transaction deleted succesfully!";
        }
        else
        {
            echo "Wrong string to delete all transaction!";
        }
    }
    
#Import BankAccount
#EXAMPLE = MMEX_WebApp/services?import_bankaccount=Pippo%20Pluto;;Topopolino;;Paperino
if (isset($_GET['import_bankaccount']))
    {
        $bankaccounts_string = $_GET['import_bankaccount'];
        $bankaccounts_array = explode(";;", $bankaccounts_string);
        db_function::bankaccount_insert($bankaccounts_array);
        echo "All accounts imported succesfully!";
    }
?>