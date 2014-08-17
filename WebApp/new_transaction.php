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
	
    <title>Transaction</title>
    <link rel="icon" href="res/favicon.ico" />
    
    <link rel="stylesheet" type="text/css" href="res/bootstrap-3.2.0.min.css" />
    <link rel="stylesheet" type="text/css" href="res/bootstrap-theme-3.2.0.min.css" />
    <link rel="stylesheet" type="text/css" href="res/typeahead-bootstrap-0.9.9.css" />
    <link rel="stylesheet" type="text/css" href="res/style_global-0.9.9.css" />
    
    <script src="res/modernizr-2.8.3.js" type="text/javascript"></script>
    <script src="res/jquery-2.1.1.min.js" type="text/javascript"></script>
    <script src="res/typeahead.bundle-0.10.2.min.js" type="text/javascript"></script>
    <script src="res/functions-1.0.1.js" type="text/javascript"></script>
</head>

<body>
	<script type="text/javascript">
        test_html5();
	</script>
    
    <?php
    attachments::delete_zero();
       
    if (isset($_GET["TrEditNr"]))
        {
            $TrEditNr = $_GET["TrEditNr"];
            $FlagNew = False;
        }
    else
        {
            $TrEditNr = 0;
            $FlagNew = True;
        }
    
    if($FlagNew)
        {
            $resultarray = array();
            $TransactionHeaderText = "Insert new transcation";
            $TransactionDate = "2014-01-01";
            $TransactionStatus = costant::transaction_default_status();
            $TransactionType = costant::transaction_default_type();
            $TransactionAccount = costant::transaction_default_account();
            $TransactionToAccount = "None";
            $TransactionPayee = "";
            $TransactionCategory = "";
            $TransactionSubCategory = "";
            $TransactionAmount = "0";
            $TransactionNotes = "Empty";
            $TransactionSubmit = "Insert transaction";
        }
        else
        {
            $resultarray = db_function::transaction_select_one($TrEditNr);
            $TransactionHeaderText = "Edit transcation";
            $TransactionDate = $resultarray["Date"];
            $TransactionStatus = $resultarray["Status"];
            $TransactionType = $resultarray["Type"];
            $TransactionAccount = $resultarray["Account"];
            $TransactionToAccount = $resultarray["ToAccount"];
            $TransactionPayee = $resultarray["Payee"];
            $TransactionCategory = $resultarray["Category"];
            $TransactionSubCategory = $resultarray["SubCategory"];
            $TransactionAmount = $resultarray["Amount"];
            $TransactionNotes = $resultarray["Notes"];
            $TransactionSubmit = "Edit transaction";
        }
    if (sizeof($resultarray) > 0 || $FlagNew == True)
        {
            echo "<div class='container'>";
                echo "<form id='Transaction' class='form-transaction' method='post' action = 'insert.php'
                onsubmit='return confirm_if_not_present_in_datalist(\"Payee\",\"PayeeList\",\"Do you want to add the new payee\")'>";
    
                    echo "<h3 class='text_align_center'>${TransactionHeaderText}</h3>";
                    echo "<br />";
                    
                    design::input_date($TransactionDate);
                    design::input_status($TransactionStatus);
                    design::input_type($TransactionType);
                    design::input_account($TransactionAccount);
                    design::input_toaccount($TransactionToAccount);
                    if (costant::disable_payee() !== True)
                        {
                            design::input_payee($TransactionPayee);
                        }
                    else
                        {
                            design::input_hidden("Payee","None");
                        }
                    if (costant::disable_category() !== True)
                        {
                            design::input_category($TransactionCategory);
                            design::input_subcategory($TransactionSubCategory);
                        }
                    else
                        {
                            design::input_hidden("Category","None");
                            design::input_hidden("SubCategory","None");
                        }
                    design::input_amount($TransactionAmount);
                    design::input_notes($TransactionNotes);
                    
                    echo "<div class='form-group'>";
                        echo "<label for='fileToUpload'>Take a picture or upload attachments</label><br />";
                        echo "<input type='file' name='fileToUpload' id='fileToUpload' onchange='attachment_uploadFile();' />";
                        echo "<span class='help-block'></span>";
                    echo "</div>";
                    
                    echo "<div class='table-responsive' id='attachments_table'>";
                    echo "</div>";
                    echo "<br />";  
                    
                    if ($FlagNew)
                        {
                            echo "<script type='text/javascript'>";
                                echo "var date_today = get_today();";
                                echo "document.getElementById('Date').value=date_today;";
                            echo "</script>";  
                        }
                    else
                        {
                            design::input_hidden("TrEditedNr",$TrEditNr);
                            echo "<script type='text/javascript'>";
                                echo "populate_sub_category();";
                            echo "</script>";  
                        }
                    echo "<button type='submit' id='SubmitButton' name='SubmitButton' class='btn btn-lg btn-success btn-block'>${TransactionSubmit}</button>";
                    echo "<br />";
                    echo "<br />";
                echo "</form>";
            echo "</div>";
            
            echo "<script type='text/javascript'>";
                //Refresh Attachments table
                echo "attachment_RefreshTable(${TrEditNr});";
                //Manager trasnfer disable field
                echo "enable_element ('ToAccount','Type','Transfer');";
                echo "disable_element ('Payee','Type','Transfer');";
                //Manage default category
                echo "$('#Payee').bind('input', set_default_category);";
                echo "$('#Payee').bind('typeahead:selected', set_default_category);";
                //Manage subcategory
                echo "$('#Category').bind('input', populate_sub_category);";
                echo "$('#Category').bind('typeahead:selected', populate_sub_category);";
            echo "</script>";  
        }
    else
        {
            //Transaction not found
            echo "<div class='container'>";
                echo "<br />";
                echo "<br />";
                echo "<h3 class='text_align_center'>Wrong transaction ID</h3>";
                echo "<br />";
                echo "<br />";
                echo "<input type='button' class='btn btn-lg btn-success btn-block' value='Insert new' onclick=".'"top.location.href = '."'new_transaction.php'".'" />';
                echo "<br />";
                echo "<br />";
            echo "</div>";
        }
    ?>
</body>
</html>