<?php

$b_restricted_auth  = true;
$transaction_name   = '';

$TrEditNr = 0;
$TransactionHeaderText = 'Creating new transaction';
$TransactionSubmit = 'Create transaction';
$FlagNew = true;

if (isset($_GET['TrEditNr']))
{
    $TrEditNr = $_GET['TrEditNr'];
    $TransactionHeaderText = 'Editing transaction';
    $TransactionSubmit = 'Update transaction';
    $FlagNew = false;
}
elseif (isset($_GET['TrDuplicateNr']))
{
    $TrEditNr = $_GET['TrDuplicateNr'];
    $TransactionHeaderText = 'Duplicating transaction';
    $TransactionSubmit = 'Create duplicate';
}

$s_page_title           = $TransactionHeaderText;
$a_head_css_add[]       = '<link rel="stylesheet" type="text/css" href="res/typeahead-bootstrap-0.11.1.css" />';
$a_head_js_add[]        = '<script src="res/app/base-1.0.4.js" type="text/javascript"></script>';
$a_head_js_add[]        = '<script src="res/typeahead.bundle-0.11.1.min.js" type="text/javascript"></script>';
$a_head_js_add[]        = '<script src="res/modernizr-3.2.0.js" type="text/javascript"></script>';
$a_head_js_add[]        = '<script src="res/app/new_transaction-1.0.4.js" type="text/javascript"></script>';

include_once '_common.php';
include_once '_header.php';

?>

	<script type="text/javascript">
        test_html5();
	</script>
    
<?php

    attachments::delete_zero();

    if ($TrEditNr == 0)
        {
            $resultarray = array();
            $TransactionDate = '2014-01-01';
            $TransactionStatus = costant::transaction_default_status();
            $TransactionType = costant::transaction_default_type();
            $TransactionAccount = costant::transaction_default_account();
            $TransactionToAccount = 'None';
            $TransactionPayee = '';
            $TransactionCategory = '';
            $TransactionSubCategory = '';
            $TransactionAmount = '0';
            $TransactionNotes = 'Empty';
        }
        else
        {
            $resultarray = db_function::transaction_select_one($TrEditNr);
            $TransactionDate = $resultarray['Date'];
            $TransactionStatus = $resultarray['Status'];
            $TransactionType = $resultarray['Type'];
            $TransactionAccount = $resultarray['Account'];
            $TransactionToAccount = $resultarray['ToAccount'];
            $TransactionPayee = $resultarray['Payee'];
            $TransactionCategory = $resultarray['Category'];
            $TransactionSubCategory = $resultarray['SubCategory'];
            $TransactionAmount = $resultarray['Amount'];
            $TransactionNotes = $resultarray['Notes'];
        }

    if (sizeof($resultarray) > 0 || $FlagNew)
        {
            echo "<div class='container'>";
                echo "<form id='Transaction' class='form-transaction' method='post' action='insert.php'>";
    
                    design::input_date($TransactionDate);
                    design::input_status($TransactionStatus);
                    design::input_type($TransactionType);
                    design::input_account($TransactionAccount);
                    design::input_toaccount($TransactionToAccount);
                    design::input_amount($TransactionAmount);
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

                    design::input_notes($TransactionNotes);
                    
                    echo "<div class='form-group'>";
                        echo '<label class="width100" for="fileToUpload">Take a picture or upload attachments</label><br />';
                        echo "<input type='file' name='fileToUpload' id='fileToUpload' onchange='attachment_uploadFile(${TrEditNr});' />";
                        echo "<span class='help-block'></span>";
                    echo "</div>\n";
                    
                    echo "<div class='table-responsive' id='attachments_table'>";
                    echo "</div>\n";

                    if (!isset($_GET['TrEditNr']) && !(isset($_GET['TrDuplicateNr'])))
                        {
                            echo "<script type='text/javascript'>";
                                echo "var date_today = get_today();";
                                echo "document.getElementById('Date').value=date_today;";
                            echo "</script>";  
                        }
                    else
                        {
                            if (isset($_GET['TrDuplicateNr']))
                            {
                                design::input_hidden("TrEditedNr", 0);
                            }
                            elseif (isset($_GET['TrEditNr']))
                            {
                                design::input_hidden("TrEditedNr",$TrEditNr);
                            }

                            echo "<script type='text/javascript'>";
                                echo "populate_sub_category(false);";
                            echo "</script>";
                        }
                    echo "<button type='submit' id='SubmitButton' name='SubmitButton' class='btn btn-lg btn-success btn-block'>${TransactionSubmit}</button>";

                echo "</form>";
            echo "</div>\n";

            echo "<script type='text/javascript'>";
                //Refresh Attachments table
                echo "attachment_RefreshTable(${TrEditNr});\n";
                //Manage transfer disable field
                echo "enable_element ('ToAccount','Type_Withdrawal','Transfer');\n";
                echo "disable_element ('Payee','Type_Withdrawal','Transfer');\n";
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

include_once '_footer.php';
