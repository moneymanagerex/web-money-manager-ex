<?php
# Import configuration file
require_once "functions.php";

$api_version = "0.9.0";
$operation_succeded = "Operation has succeeded";
$wrong_guid = "Wrong GUID";

if (isset($_GET["guid"]) && $_GET["guid"] == costant::desktop_guid())
    {   
        #Test guid
        if (isset($_GET["check_guid"]))
            {
                echo $operation_succeded;
            }
        #Delete all transaction
        if (isset($_GET["delete_all"]))
            {
                db_function::transaction_delete_all();
                echo $operation_succeded;
            }
        
        #Delete transaction group
        if (isset($_GET["delete_group"]))
            {
                $deletegroup_string = $_GET["delete_group"];
                $deletegroup_array = explode(",",$deletegroup_string);
                db_function::transaction_delete_group($deletegroup_array);
                echo $operation_succeded;
            }

        #Import BankAccount
        #EXAMPLE = WebApp/services.php?import_bankaccount=Account1;;Account2;;Account3
        if (isset($_GET["import_bankaccount"]))
            {
                $bankaccounts_string = $_GET["import_bankaccount"];
                $bankaccounts_array = explode(costant::import_delimiter(), $bankaccounts_string);
                db_function::bankaccount_delete_all();
                db_function::bankaccount_insert($bankaccounts_array);
                echo $operation_succeded;
            }
        
        #Import Payee
        #EXAMPLE = WebApp/services.php?import_payee=Payee1;;Payee2;;Payee3
        if (isset($_GET["import_payee"]))
            {
                $payees_string = $_GET["import_payee"];
                $payees_array = explode(costant::import_delimiter(), $payees_string);
                db_function::payee_insert ($payees_array);
                echo $operation_succeded;
            }
        
        #Delete Payee
        if (isset($_GET["delete_payee"]))
            {
                db_function::payee_delete_all();
                echo $operation_succeded;
            }
        
        #Download New_Transaction
        if (isset($_GET["download_db"]))
            {
                $const_dbpath = costant::database_path();
                db_function::db_vacuum();
                header("Cache-Control: public");
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename= " . $const_dbpath);
                header("Content-Transfer-Encoding: binary");
                readfile($const_dbpath);
            }
        
        #Check New Transaction
        if (isset($_GET["check_new_transaction"]))
            {
                $maxid = db_function::transaction_select_maxid();
                if ($maxid > 0)
                {
                    echo "True";
                }
                else
                {
                    echo "False";
                }
            }
            
        #Return WebApp Version
        if (isset($_GET["check_api_version"]))
            {
                echo $api_version;
            }
    }
else
    {
        echo $wrong_guid;
    }
    ?>