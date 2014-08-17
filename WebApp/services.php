<?php
# Import configuration file
require_once "functions.php";

$operation_succeded = "Operation has succeeded";
$wrong_guid = "Wrong GUID";
header("Content-Type: text/plain; charset=utf-8");

if (isset($_GET["guid"]) && $_GET["guid"] == costant::desktop_guid())
    {   
        #Test guid
        if (isset($_GET["check_guid"]))
            {echo $operation_succeded;}
        
        #Return WebApp API Version
        if (isset($_GET["check_api_version"]))
            {echo costant::api_version();}
            
        #Delete BankAccount
        if (isset($_GET["delete_bankaccount"]))
            {
                db_function::bankaccount_delete_all();
                echo $operation_succeded;
            }
            
        #Import BankAccount
        if (isset($_GET["import_bankaccount"]))
            {
                db_function::bankaccount_insert_json($_POST["MMEX_Post"]);
                echo $operation_succeded;
            }
            
        #Delete Payee
        if (isset($_GET["delete_payee"]))
            {
                db_function::payee_delete_all();
                echo $operation_succeded;
            }
            
        #Import Payee
        if (isset($_GET["import_payee"]))
            {
                db_function::payee_insert_json($_POST["MMEX_Post"]);
                echo $operation_succeded;
            }
            
        #Delete Category
        if (isset($_GET["delete_category"]))
            {
                db_function::category_delete_all();
                echo $operation_succeded;
            }
            
        #Import Category
        if (isset($_GET["import_category"]))
            {
                db_function::category_insert_json($_POST["MMEX_Post"]);
                echo $operation_succeded;
            }
        
        #Download New Transactions
        if (isset($_GET["download_transaction"]))
            {
                $TransactionsArr = db_function::transaction_select_all_order_by_date();
                if( !empty($TransactionsArr) )
                    echo (json_encode($TransactionsArr[0],JSON_UNESCAPED_UNICODE));     
            }
        
        #Download Attachments
        if (isset($_GET["download_attachments"]))
            {
                $AttachmentsArr = attachments::get_attachments_filename_array((int)$_GET["download_attachments"],false);
                if ( !empty ($AttachmentsArr) )
                    {
                        $FullPath = costant::attachments_folder()."/".$AttachmentsArr[0];
                        header("Content-Type:");
                        header("Cache-Control: public");
                        header("Content-Description: File Transfer");
                        header("Content-Disposition: attachment; filename= ".$AttachmentsArr[0]);
                        header("Content-Transfer-Encoding: binary");
                        readfile($FullPath);
                    }     
            }
        
        #Download Attachments by name
        if (isset($_GET["download_attachments_by_name"]))
            {
                $AttachmentFileName = $_GET["download_attachments_by_name"];
                if ( !empty ($AttachmentFileName) )
                    {
                        $FullPath = costant::attachments_folder()."/".$AttachmentFileName;
                        header("Content-Type:");
                        header("Cache-Control: public");
                        header("Content-Description: File Transfer");
                        header("Content-Disposition: attachment; filename= ".$AttachmentFileName);
                        header("Content-Transfer-Encoding: binary");
                        readfile($FullPath);
                    }     
            }
        
        #Delete Attachments
        if (isset($_GET["delete_attachment"]))
            {
                $AttachmentFileName = $_GET["delete_attachment"];
                if (!empty ($AttachmentFileName))
                    {attachments::delete_attachment_by_name($AttachmentFileName);}
            }
        
        #Delete transaction group
        if (isset($_GET["delete_group"]))
            {
                $deletegroup_string = $_GET["delete_group"];
                $deletegroup_array = explode(",",$deletegroup_string);
                db_function::transaction_delete_group($deletegroup_array);
                attachments::delete_group($deletegroup_array);
                echo $operation_succeded;
            }
    }
else
    {
        echo $wrong_guid;
    }
    ?>