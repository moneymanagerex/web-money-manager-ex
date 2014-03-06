<?php
# Import configuration file
require_once "functions.php";

if (isset($_GET["guid"]) && $_GET["guid"] == costant::desktop_guid())
    {   
        #Delete all transaction
        if (isset($_GET["deleteall"]))
            {
                db_function::transaction_delete_all();
                echo "All transaction deleted succesfully!";
            }
        
        #Delete transaction group
        if (isset($_GET["deletegroup"]))
            {
                $deletegroup_string = $_GET["deletegroup"];
                $deletegroup_array = explode(",",$deletegroup_string);
                db_function::transaction_delete_group($deletegroup_array);
                echo "Transaction deleted succesfully!";
            }

        #Import BankAccount
        #EXAMPLE = WebApp/services.php?import_bankaccount=Account1;;Account2;;Account3
        if (isset($_GET["import_bankaccount"]))
            {
                $bankaccounts_string = $_GET["import_bankaccount"];
                $bankaccounts_array = explode(";;", $bankaccounts_string);
                db_function::bankaccount_insert($bankaccounts_array);
                echo "All accounts imported succesfully!";
            }
        
        #Import Payee
        #EXAMPLE = WebApp/services.php?import_payee=Payee1;;Payee2;;Payee3
        if (isset($_GET["import_payee"]))
            {
                $payees_string = $_GET["import_payee"];
                $payees_array = explode(";;", $payees_string);
                db_function::payee_insert_all ($payees_array);
                echo "All payees imported succesfully!";
            }
        
        #Download New_Transaction
        if (isset($_GET["download_db"]))
            {
                $const_dbpath = costant::database_path();
                header("Cache-Control: public");
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename= " . $const_dbpath);
                header("Content-Transfer-Encoding: binary");
                readfile($const_dbpath);
            }
    }
else
    {
        echo "Wrong guid!";
    }
    ?>