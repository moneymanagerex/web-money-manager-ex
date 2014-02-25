<?php
require_once "functions.php";
session_start();
security::redirect_if_not_loggedin();


# Delete & Modify
if(isset($_POST["TrModify"]))
    {
        $TrModify = $_POST["TrModify"];
        
        if(isset($_POST["TrDelete"]) && $TrModify == "Delete")
            {
                $TrDeleteArr = $_POST["TrDelete"];
                db_function::transaction_delete_group ($TrDeleteArr);
                
                header("Location: show.php");
            }
        
        if(!isset($_POST["TrDelete"]) && $TrModify == "Delete")
            {   
                $test = various::send_alert_and_redirect("No transaction selected!","show.php");
            }
        
        if(isset($_POST["TrEdit"]) && $TrModify == "Edit")
            {
                $TrEdit = $_POST["TrEdit"];
                
                header("Location: edit_transaction.php?tredit=".$TrEdit[0]);
            }
            
        if(!isset($_POST["TrEdit"]) && $TrModify == "Edit")
            {   
                $test = various::send_alert_and_redirect("No transaction selected!","show.php");
            }
    }

# Edit
if(isset($_POST["EditSubmit"]))
    {
        if($_POST["EditSubmit"] == "EditSubmit")
            {
                # Get variables
                $TrEditedId = $_POST["TrEditedId"];
                $TrDate = $_POST["Date"];
                $TrStatus = $_POST["Status"];
                $TrType = $_POST["Type"];
                $TrAccount = $_POST["Account"];
                $TrToAccount = $_POST["ToAccount"];
                $TrAmount = $_POST["Amount"];
                $TrNotes = $_POST["Notes"];
                
                # Update
                db_function::transaction_update($TrEditedId,$TrDate,$TrStatus,$TrType,$TrAccount,$TrToAccount,$TrAmount,$TrNotes);
                
                header("Location: show.php");
            }
    }
?>