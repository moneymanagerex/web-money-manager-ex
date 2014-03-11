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
    
    <link rel="stylesheet" type="text/css" href="res/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="res/bootstrap-theme.min.css" />
    <link rel="stylesheet" type="text/css" href="style_global.css" />

    <script src="res/jquery.min.js" type="text/javascript"></script>
    <script src="res/bootstrap.min.js" type="text/javascript"></script>
    <script src="functions.js" type="text/javascript"></script>

</head>

<body>

<?php

$recordmaxid = db_function::transaction_select_maxid();
if ($recordmaxid > 0 )
    {
        $resultarray = db_function::transaction_select_all_show();
        echo "<div class='container'>";
            echo "<h3 class='text_align_center'>Current pending transaction</h3>";
            echo "<br/>";
            echo "<div class='table-responsive'>";
                echo "<form id='Show_Function' class='form-show-function' method='post' action = 'show_function.php'>";
                echo "<table class = 'table table-hover table-condensed'>";
                #echo "<table class = 'table table-hover table-condensed table-bordered'>"; //TABLE BORDERED FOR DEBUG
                    echo "<thead>";
                        echo "<tr>";
                            #echo "<th>Nr.</th>";
                            echo "<th>Date</th>";
                            #echo "<th>Status</th>";
                            echo "<th>Type</th>";
                            echo "<th>Account</th>";
                            #echo "<th>ToAccount</th>";
                            if (costant::disable_payee() == False)
                            {
                                echo "<th>Payee</th>";
                            }
                            echo "<th class = 'text_align_right'>   Amount</th>";
                            echo "<th class = 'text_align_center'>  Notes</th>";
                            echo "<th class = 'text_align_center'>  Delete</th>";
                            echo "<th class = 'text_align_center'>  Edit</th>";
                        echo "</tr>";
                    echo "</thead>";
                    
                    echo "<tbody>";
                    for ($i = 0; $i <= $recordmaxid; $i++)
                        {
                            if (isset($resultarray[$i]['ID']))
                                {
                                    echo "<tr>";
                                        $lineid = $resultarray[$i]['ID'];
                                        foreach (array_slice($resultarray[$i],1) as $colname => $value)
                                        {
                                            switch ($colname)
                                            {
                                                case "Amount":
                                                    design::table_cell(number_format($value,2,",",""),"text_align_right td_size_5");
                                                    break;
                                                    
                                                case "Type":
                                                      if ($value == "Withdrawal")
                                                          $value = "With.";
                                                      if ($value == "Deposit")
                                                          $value = "Dep";
                                                      if ($value == "Transfer")
                                                          $value = "Tran.";
                                                      design::table_cell($value,"");
                                                      break;
                                                case "Payee":
                                                    if (costant::disable_payee() == False)
                                                        {
                                                            design::table_cell($value,"");
                                                        }
                                                    break;
                                                case "Notes":
                                                    design::table_cell("<span class='glyphicon glyphicon-info-sign' data-toggle='tooltip' title='${value}' id='tooltip${lineid}'></span>","text_align_center");
                                                    break;
                                                default:
                                                    design::table_cell($value,"");
                                                    break;
                                            }
                                        }
                                        echo "<td class ='text_align_center'>";
                                            echo "<input type='checkbox' name='TrDelete[]' value='${lineid}' />";
                                        echo "</td>";
                                        echo "<td class ='text_align_center'>";
                                            echo "<input type='radio' name='TrEdit[]' value='${lineid}' />";
                                        echo "</td>";
                                        
                                    echo "</tr>";
                                }
                        }
                    echo "</tbody>";
                echo "</table>";
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
            echo "</div>";
        echo "</div>";
        
        #JavaScript for notes tooltip
        echo "<script type='text/javascript'>\n";
            echo "$(window).load(function(){\n";
                    echo "$(document).ready(function() {\n";
                        for ($i = 0; $i <= $recordmaxid; $i++)
                            if (isset($resultarray[$i]['ID']))
                            {
                                $lineid = $resultarray[$i]['ID'];
                                echo "$('#tooltip${lineid}').tooltip();\n";
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
        echo "</div>";
    }
?>
</body>
</html>