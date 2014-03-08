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
    <link rel="icon" href="res\favicon.ico" />
    
    <link rel="stylesheet" href="res\bootstrap.min.css" />
    <link rel="stylesheet" href="res\bootstrap-theme.min.css" />
    <link rel="stylesheet" type="text/css" href="style_global.css" />

</head>

<body>

<?php
#Import common file
require_once "functions.php";

$recordmaxid = db_function::transaction_maxid();
if ($recordmaxid > 0 )
    {
        $resultarray = db_function::transaction_select_all_show();
        echo "<div class='container'>";
            echo "<h3 align = 'center'>Current pending transaction</h3>";
            echo "<br/>";
            echo "<div class='table-responsive'>";
                echo "<form id='Show_Function' class='form-show-function' method='post' action = 'show_function.php'>";
                echo "<table class = 'table table-hover table-condensed'>";
                    echo "<thead>";
                        echo "<tr>";
                            #echo "<th>Nr.</th>";
                            echo "<th>Date</th>";
                            #echo "<th>Status</th>";
                            echo "<th>Type</th>";
                            echo "<th>Account</th>";
                            #echo "<th>ToAccount</th>";
                            echo "<th>Amount</th>";
                            echo "<th>Notes</th>";
                            echo "<th>Delete</th>";
                            echo "<th>Edit</th>";
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
                                                    $value = number_format($value,2,",","");
                                                    break;
                                                case "Status":
                                                        if ($value == "")
                                                            $value = "None";
                                                        if ($value == "R")
                                                            $value = "Reconciled";
                                                        if ($value == "V")
                                                            $value = "Void";
                                                        if ($value == "F")
                                                            $value = "Follow Up";                                                                                                 
                                                        if ($value == "D")
                                                            $value = "Duplicate"; 
                                                default:
                                                    $value = $value;
                                            }
                                            echo "<td>";
                                            echo $value;
                                            echo "</td>";
                                        }
                                        echo "<td>";
                                            echo "<input type='checkbox' name='TrDelete[]' value='${lineid}' >";
                                        echo "</td>";
                                        echo "<td>";
                                            echo "<input type='radio' name='TrEdit[]' value='${lineid}' >";
                                        echo "</td>";
                                    echo "</tr>";
                                }
                        }
                    echo "</tbody>";
                echo "</table>";
            echo "</div>";
            echo "<br />";
            echo "<button type='submit' id='TrModify' name='TrModify' value = 'Delete' class='btn btn-lg btn-success btn-block'>Delete selected</button>";
            echo "<br />";
            echo "<button type='submit' id='TrModify' name='TrModify' value = 'Edit' class='btn btn-lg btn-success btn-block'>Edit selected</button>";
            echo "</form>";
            #echo "<input type='button' class='btn btn-lg btn-success btn-block' value='New transaction' onclick=".'"top.location.href = '."'new_transaction.php'".'" />';
            echo "<br />";
            echo "<input type='button' class='btn btn-lg btn-success btn-block' value='Return to menu' onclick=".'"top.location.href = '."'landing.php'".'" />';
            echo "<br />";
            echo "<br />";
        echo "</div>";
    }
else
    {
        echo "<div class='container'>";
            echo "<br />";
            echo "<br />";
            echo "<h3 align = 'center'>No transaction inserted</h3>";
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