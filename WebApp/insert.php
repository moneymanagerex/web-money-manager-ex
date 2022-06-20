<?php

$b_restricted_auth  = true;

$TrEditedNr = -1;
if(isset($_POST["TrEditedNr"]))
{
    $TrEditedNr = $_POST["TrEditedNr"];
}

switch ($TrEditedNr)
{
    case -1:    $s_page_title = 'New Transaction added';            break;
    case 0:     $s_page_title = 'Existing Transaction duplicated';  break;
    default:    $s_page_title = 'Existing Transaction updated';     break;
}

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
            <br />
            <br />
            <h3><?php echo $s_page_title; ?> successfully</h3>
            <br />
            <br />
            <a href="new_transaction.php" class="btn btn-lg btn-success btn-block">Add new</a>
            <br />
            <a href="show.php" class="btn btn-lg btn-success btn-block">Show transactions</a>
            <br />
            <a href="landing.php" class="btn btn-lg btn-success btn-block">Return to menu</a>
            <br />
            <br />
        </div>
		
		<script src="res/app/base-1.0.4.js" type="text/javascript"></script>
    </body>
</html>
