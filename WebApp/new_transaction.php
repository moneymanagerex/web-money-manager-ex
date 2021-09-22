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
	
    <title>Transaction</title>
    <link rel="icon" href="res/favicon.ico" />
    <link rel="apple-touch-icon" href="res/mmex.png" />
    
    <link rel="stylesheet" type="text/css" href="res/bootstrap-3.3.6.min.css" />
    <link rel="stylesheet" type="text/css" href="res/bootstrap-theme-3.3.6.min.css" />
    <link rel="stylesheet" type="text/css" href="res/typeahead-bootstrap-0.11.1.css" />
    <link rel="stylesheet" type="text/css" href="res/style_global-0.9.9.css" />
    
    <script src="res/modernizr-3.2.0.js" type="text/javascript"></script>
    <script src="res/jquery-2.1.4.min.js" type="text/javascript"></script>
    <script src="res/typeahead.bundle-0.11.1.min.js" type="text/javascript"></script>
    <script src="res/app/functions-1.1.0.js" type="text/javascript"></script>
    <script src="res/app/new_transaction-1.0.4.js" type="text/javascript"></script>
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
                echo "<form id='Transaction' class='form-transaction' method='post' action='insert.php'>";
    
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
                        echo "<input type='file' name='fileToUpload' id='fileToUpload' onchange='attachment_uploadFile(${TrEditNr});' />";
                        echo "<span class='help-block'></span>";
                    echo "</div>\n";
                    
                    echo "<div class='table-responsive' id='attachments_table'>";
                    echo "</div>\n";
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
                                echo "populate_sub_category(false);";
                            echo "</script>";  
                        }
                    echo "<button type='submit' id='SubmitButton' name='SubmitButton' class='btn btn-lg btn-success btn-block'>${TransactionSubmit}</button>";
                    echo "<br />";
                    echo "<a href='landing.php' class='btn btn-lg btn-success btn-block'>Return to menu</a>";
                    echo "<br />";
                    echo "<br />";
                echo "</form>";
            echo "</div>\n";

            echo "<script type='text/javascript'>";
                //Refresh Attachments table
                echo "attachment_RefreshTable(${TrEditNr});\n";
                //Manager transfer disable field
                echo "enable_element ('ToAccount','Type','Transfer');\n";
                echo "disable_element ('Payee','Type','Transfer');\n";
                //Manage default category
                echo "$('#Payee').bind('input', set_default_category);\n";
                echo "$('#Payee').bind('typeahead:selected', set_default_category);\n";
                //Manage subcategory
                echo "$('#Category').bind('input', populate_sub_category);\n";
                echo "$('#Category').bind('typeahead:selected', populate_sub_category);\n";
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
            echo "</div>\n";
        }
    ?>
	
	<script src="res/app/base-1.0.4.js" type="text/javascript"></script>
</body>
</html>
