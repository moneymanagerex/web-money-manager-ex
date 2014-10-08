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
	
    <title>Show Transaction</title>
    <link rel="icon" href="res/favicon.ico" />
    
    <link rel="stylesheet" type="text/css" href="res/bootstrap-3.2.0.min.css" />
    <link rel="stylesheet" type="text/css" href="res/bootstrap-theme-3.2.0.min.css" />
    <link rel="stylesheet" type="text/css" href="res/style_global-0.9.9.css" />

    <script src="res/jquery-2.1.1.min.js" type="text/javascript"></script>
    <script src="res/bootstrap-3.2.0.min.js" type="text/javascript"></script>
    <script src="res/functions-1.0.1.js" type="text/javascript"></script>

</head>

<body>

<?php

$recordmaxid = db_function::transaction_select_maxid();
if ($recordmaxid > 0 )
    {
        $resultarray = db_function::transaction_select_all_order_by_date();
        echo "<div class='container'>";
            echo "<h3 class='text_align_center'>Current pending transaction</h3>";
            echo "<br/>";
            echo "<div class='table-responsive'>";
                echo "<form id='Show_Function' class='form-show-function' method='post' action = 'show_function.php'>";
                echo "<table class = 'table table-hover table-condensed'>";
                #echo "<table class = 'table table-hover table-condensed table-bordered'>"; //TABLE BORDERED FOR DEBUG
                    echo "<thead>";
                        echo "<tr>";
                            echo "<th>Date</th>";
                            echo "<th>Type</th>";
                            echo "<th>Account</th>";
                            if (costant::disable_payee() == False)
                                {echo "<th>Payee</th>";}
                            if (costant::disable_category() == False)
                                {echo "<th>Category</th>";}
                            echo "<th class = 'text_align_right'>Amount</th>";
                            echo "<th class = 'text_align_center'>Notes</th>";
                            echo "<th class = 'text_align_center'>Delete</th>";
                            echo "<th class = 'text_align_center'>Edit</th>";
                        echo "</tr>";
                    echo "</thead>";
                    
                    echo "<tbody>";
                    for ($i = 0; $i <= $recordmaxid; $i++)
                        {
                            if (isset($resultarray[$i]["ID"]))
                                {
                                    echo "<tr>";
                                        //TRANSACTION ID
                                        $lineid = $resultarray[$i]["ID"];
                                        
                                        //DATE
                                        $TrDateShow = $resultarray[$i]["Date"];
                                        design::table_cell($TrDateShow,"");
                                        
                                        //TYPE
                                        $TrStatusShow = $resultarray[$i]["Status"];
                                        $TrTypeShow = $resultarray[$i]["Type"];
                                            if ($TrTypeShow == "Withdrawal")
                                                $TrTypeShowFormatted = "With.";
                                            if ($TrTypeShow == "Deposit")
                                                $TrTypeShowFormatted = "Dep.";
                                            if ($TrTypeShow == "Transfer")
                                                $TrTypeShowFormatted = "Tran.";
                                        design::table_cell("${TrStatusShow} - ${TrTypeShowFormatted}","");
                                        
                                        //ACCOUNT
                                        $TrAccountShow = $resultarray[$i]["Account"];
                                        $TrToAccountShow = $resultarray[$i]["ToAccount"];
                                        if ($TrTypeShow == "Transfer")
                                        {
                                            design::table_cell("<span data-toggle='tooltip' title='Transfer to: ${TrToAccountShow}' id='tooltip_account_${lineid}'>${TrAccountShow}</span>","");
                                        }
                                        else
                                            {design::table_cell($TrAccountShow,"");}
                                            
                                        //PAYEE
                                        $TrPayeeShow = $resultarray[$i]["Payee"];
                                        if (costant::disable_payee() == False)
                                            {design::table_cell($TrPayeeShow,"");}
                                        
                                        //CATEGORY
                                        $TrCategoryShow = $resultarray[$i]["Category"];
                                        $TrSubCategoryShow = $resultarray[$i]["SubCategory"];
                                            if (costant::disable_category() == False && $TrSubCategoryShow != "None")
                                            {
                                                design::table_cell("<span data-toggle='tooltip' title='Subcategory: ${TrSubCategoryShow}' id='tooltip_category_${lineid}'>${TrCategoryShow}*</span>","");
                                            }
                                        else if (costant::disable_category() == False)
                                                {design::table_cell($TrCategoryShow,"");}
                                            
                                        //AMOUNT
                                        $TrAmountShow = number_format($resultarray[$i]["Amount"],2,",","");
                                        design::table_cell($TrAmountShow,"text_align_right td_size_5");
                                        
                                        //NOTES
                                        $TrNotesShow = $resultarray[$i]["Notes"];
                                        $NotesHTMLCode = "";
                                        if ($TrNotesShow != "" && $TrNotesShow != "None")
                                            $NotesHTMLCode .= "<span class='glyphicon glyphicon-info-sign' data-toggle='tooltip' title='${TrNotesShow}' id='tooltip_notes_${lineid}'></span> ";
                                        if (attachments::get_number_of_attachments($lineid) > 0)
                                            $NotesHTMLCode .= "<span class='glyphicon glyphicon-paperclip'></span>";
                                        design::table_cell($NotesHTMLCode,"text_align_center");
                                        
                                        //DELETE
                                        echo "<td class ='text_align_center'>";
                                            echo "<input type='checkbox' name='TrDelete[]' value='${lineid}' />";
                                        echo "</td>";
                                        
                                        //EDIT
                                        echo "<td class ='text_align_center'>";
                                            echo "<input type='radio' name='TrEdit[]' value='${lineid}' />";
                                        echo "</td>";

                                    echo "</tr>";
                                }
                        }
                    echo "</tbody>";
                echo "</table>";
            echo "</div>\n";
                echo "<br />";
                echo "<button type='submit' id='TrDelete' name='TrModify' value = 'Delete' class='btn btn-lg btn-success btn-block'>Delete selected</button>";
                echo "<br />";
                echo "<button type='submit' id='TrModify' name='TrModify' value = 'Edit' class='btn btn-lg btn-success btn-block'>Edit selected</button>";
                echo "</form>";
                #echo "<input type='button' class='btn btn-lg btn-success btn-block' value='New transaction' onclick=".'"top.location.href = '."'new_transaction.php'".'" />';
                echo "<br />";
                echo "<input type='button' class='btn btn-lg btn-success btn-block' value='Return to menu' onclick=".'"top.location.href = '."'landing.php'".'" />';
                echo "<br />";
                echo "<br />";
        echo "</div>\n";
        
        #JavaScript for notes tooltip
        echo "<script type='text/javascript'>\n";
            echo "$(window).load(function(){\n";
                echo "$(document).ready(function() {\n";
                    for ($i = 0; $i <= $recordmaxid; $i++)
                        if (isset($resultarray[$i]["ID"]))
                        {
                            $lineid = $resultarray[$i]["ID"];
                            if ($resultarray[$i]["Type"] == "Transfer")
                                {echo "$('#tooltip_account_${lineid}').tooltip();\n";}
                            if ($resultarray[$i]["SubCategory"] != "None")
                                {echo "$('#tooltip_category_${lineid}').tooltip();\n";}
                            if ($resultarray[$i]["Notes"] != "" && $resultarray[$i]["Notes"] != "None" )
                                {echo "$('#tooltip_notes_${lineid}').tooltip();\n";}
                        }
                echo "});\n";
            echo "});\n";
        echo "</script>\n";
    }
else
    {
        echo "<div class='container'>";
            echo "<br />";
            echo "<br />";
            echo "<h3 class='text_align_center'>No transaction inserted</h3>";
            echo "<br />";
            echo "<br />";
            echo "<input type='button' class='btn btn-lg btn-success btn-block' value='Insert new' onclick=".'"top.location.href = '."'new_transaction.php'".'" />';
            echo "<br />";
            echo "<input type='button' class='btn btn-lg btn-success btn-block' value='Return to menu' onclick=".'"top.location.href = '."'landing.php'".'" />';
            echo "<br />";
            echo "<br />";
        echo "</div>\n";
    }
?>
</body>
</html>