<?php
require_once "functions.php";
session_start();
security::redirect_if_not_loggedin();
?>

<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1" />
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="MMEX">
		<meta name="apple-mobile-web-app-capable" content="yes" />
    	
        <title>Insert Transaction</title>
        <link rel="icon" href="res/favicon.ico" />
        <link rel="apple-touch-icon" href="res/mmex.png" />
        
        <link rel="stylesheet" type="text/css" href="res/bootstrap-3.3.6.min.css" />
        <link rel="stylesheet" type="text/css" href="res/bootstrap-theme-3.3.6.min.css" />
        <link rel="stylesheet" type="text/css" href="res/style_global-0.9.9.css" />
		
		<script src="res/jquery-2.1.4.min.js" type="text/javascript"></script>
    </head>
    
    <body>
        <?php
        
        #Get variables
        $TrDate = $_POST["Date"];
        $TrStatus = $_POST["Status"];
        $TrType = $_POST["Type"];
        $TrAccount = $_POST["Account"];
        if (isset($_POST["ToAccount"]))
            {$TrToAccount = $_POST["ToAccount"];}
            else
            {$TrToAccount = "None";}
        if (isset($_POST["Payee"]))
            {$TrPayee = $_POST["Payee"];}
            else
            {$TrPayee = "None";}
        if (isset($_POST["Category"]))
            {$TrCategory = $_POST["Category"];}
            else
            {$TrCategory = "None";}
        if (isset($_POST["SubCategory"]))
            {$TrSubCategory = $_POST["SubCategory"];}
            else
            {$TrSubCategory = "None";}
        $TrAmount = $_POST["Amount"];
        $TrNotes = $_POST["Notes"];
         
        #Execute common insert
        db_function::category_insert_single($TrCategory,$TrSubCategory);
        db_function::payee_insert_single($TrPayee,$TrCategory,$TrSubCategory);
        db_function::payee_update_single($TrPayee,$TrCategory,$TrSubCategory);
        
        if(isset($_POST["TrEditedNr"]))
            {
                $TrEditedNr = $_POST["TrEditedNr"];
                # Update
                db_function::transaction_update($TrEditedNr, $TrDate, $TrStatus, $TrType, $TrAccount, $TrToAccount, $TrPayee, $TrCategory, $TrSubCategory, $TrAmount, $TrNotes);
                
                echo "<script type='text/javascript'>";
                    echo "location.href='show.php'";
                echo "</script>";
            }
        else
            {
                $TrEditedNr = db_function::transaction_insert ($TrDate, $TrStatus, $TrType, $TrAccount, $TrToAccount, $TrPayee, $TrCategory, $TrSubCategory, $TrAmount, $TrNotes);
            }
        
        attachments::rename_zero($TrEditedNr);
        
        ?>
        
        <div class="container text_align_center">
            <br />
            <br />
            <h3>Transaction inserted successfully</h3>
            <br />
            <br />
            <a href="new_transaction.php" class="btn btn-lg btn-success btn-block">Insert new</a>
            <br />
            <a href="show.php" class="btn btn-lg btn-success btn-block">Show transaction</a>
            <br />
            <a href="landing.php" class="btn btn-lg btn-success btn-block">Return to menu</a>
            <br />
            <br />
        </div>
		
		<script src="res/app/base-1.0.4.js" type="text/javascript"></script>
    </body>
</html>