<?php

$b_restricted_auth  = true;

$TrEditedNr = -1;
if(isset($_POST["TrEditedNr"]))
{
    $TrEditedNr = $_POST["TrEditedNr"];
}

switch ($TrEditedNr)
{
    case -1:    
        $s_page_title = 'New Transaction';
        $transaction_action = 'added';
    break;
    case 0:     
        $s_page_title = 'Existing Transaction';
        $transaction_action = 'duplicated';
    break;
    default:    
        $s_page_title = 'Existing Transaction';
        $transaction_action = 'updated';
    break;
}

$a_head_js_add[]        = '<script src="res/app/base-1.0.4.js" type="text/javascript"></script>';
$a_head_css_add[]       = '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';

include_once '_common.php';
include_once '_header.php';


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

    if ($TrEditedNr > 0)
    {

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
            <i class="material-icons md-48 btn-success">
                check_box
            </i>
            <h3>
                <?php echo ucfirst($transaction_action); ?> successfully
            </h3>
            <br />
            <br />
            <a href="new_transaction.php" class="btn btn-lg btn-success btn-block">Add next</a>
            <br />
            <a href="show.php" class="btn btn-lg btn-success btn-block">Show transactions</a>
            <br />
        </div>
		
    </body>
</html>
