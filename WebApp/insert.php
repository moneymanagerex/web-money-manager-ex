<?php
require_once "functions.php";
session_start();
security::redirect_if_not_loggedin();
?>

<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    	
        <title>Insert Transaction</title>
        <link rel="icon" href="res\favicon.ico" />
        
        <link rel="stylesheet" href="res\bootstrap.min.css" />
        <link rel="stylesheet" href="res\bootstrap-theme.min.css" />
        <link rel="stylesheet" type="text/css" href="style_global.css" />
    </head>
    
    <body>
        <?php
        #Import common file
        require_once "functions.php";
        
        #Get variables
        $TrDate = $_POST["Date"];
        $TrStatus = $_POST["Status"];
        $TrType = $_POST["Type"];
        $TrAccount = $_POST["Account"];
        if (isset($_POST["ToAccount"]))
            {
                $TrToAccount = $_POST["ToAccount"];
            }
        else
            {
                $TrToAccount = "None";
            }
        if (isset($_POST["Payee"]))
            {
                $TrPayee = $_POST["Payee"];
            }
        else
            {
                $TrPayee = "None";
            }
        $TrAmount = $_POST["Amount"];
        $TrNotes = $_POST["Notes"];
         
        #Execute insert
        db_function::transaction_insert ($TrDate, $TrStatus, $TrType, $TrAccount, $TrToAccount, $TrPayee, $TrAmount, $TrNotes);
        db_function::payee_insert_new($TrPayee);
        ?>
        
        <div class="container" align="center">
            <br />
            <br />
            <h3>Transaction inserted successfully</h3>
            <br />
            <br />
            <input type="button" class="btn btn-lg btn-success btn-block" value="Insert new" onclick="top.location.href = 'new_transaction.php'" />
            <br />
            <input type="button" class="btn btn-lg btn-success btn-block" value="Show transaction" onclick="top.location.href = 'show.php'" />
            <br />
            <input type="button" class="btn btn-lg btn-success btn-block" value="Return to menu" onclick="top.location.href = 'landing.php'" />
            <br />
            <br />
        </div>
    </body>
    
</html>