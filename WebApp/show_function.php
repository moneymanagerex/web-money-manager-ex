<?php
require_once 'functions.php';
session_start();
security::redirect_if_not_loggedin();


# Delete & Modify
if(isset($_POST['btn_action']))
    {
        $btn_action = $_POST['btn_action'];
        
        if(isset($_POST['TrDelete']) && $btn_action == 'Delete')
            {
                $TrDeleteArr = $_POST['TrDelete'];
                db_function::transaction_delete_group ($TrDeleteArr);
                attachments::delete_group($TrDeleteArr);
                
                header("Location: show.php");
            }
        
        if(!isset($_POST['TrDelete']) && $btn_action == 'Delete')
            {   
                $test = various::send_alert_and_redirect('Nessuna operazione selezionata!', 'show.php');
            }
        
        if(isset($_POST['TrEdit']) && in_array($btn_action, ['Edit', 'Duplicate']))
            {
                $TrEdit = $_POST['TrEdit'];
                
                header("Location: new_transaction.php?Tr${btn_action}Nr=${TrEdit[0]}");
            }
            
        if(!isset($_POST['TrEdit']) && $btn_action == 'Edit')
            {   
                $test = various::send_alert_and_redirect('Nessuna operazione selezionata!', 'show.php');
            }
    }
