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
	
    <title>Edit Transaction</title>
    <link rel="icon" href="res\favicon.ico" />
    
    <link rel="stylesheet" href="res\bootstrap.min.css" />
    <link rel="stylesheet" href="res\bootstrap-theme.min.css" />
    <link rel="stylesheet" type="text/css" href="style_global.css" />
    
    <script src="res\modernizr.js" type="text/javascript"></script>
    <script src="functions.js" type="text/javascript"></script>
</head>

<body>
	<script>
        test_html5();
	</script>
    <?php   
    if (isset($_GET['tredit']))
        {
            $TrEditNr = $_GET['tredit'];
            $resultarray = db_function::transaction_select_one($TrEditNr);
            
            if (sizeof($resultarray) > 0)
                {
                    echo "<div class='container'>";
                        echo "<form id='Edit_Transaction' class='form-edit-transaction' method='post' action = 'show_function.php' onsubmit='return confirm_if_not_present_in_datalist(\"Payee\",\"PayeeList\",\"Do you want to add the new payee\")'>";
                            echo "<h3 align='center'>Edit transaction</h3>";
                            echo "<br />";
                            design::input_date($resultarray["Date"]);
                            design::input_status($resultarray["Status"]);
                            design::input_type($resultarray["Type"]);
                            design::input_account($resultarray["Account"]);
                            design::input_toaccount($resultarray["ToAccount"]);
                            if (costant::disable_payee() !== True)
                                {
                                    design::input_payee($resultarray["Payee"]);
                                }
                            else
                                {
                                    design::input_hidden("Payee","None");
                                }
                            design::input_amount($resultarray["Amount"]);
                            design::input_notes($resultarray["Notes"]);
                            design::input_hidden("TrEditedId",$TrEditNr);
                            echo "<button type='submit' id='EditSubmit' name='EditSubmit' class='btn btn-lg btn-success btn-block' value='EditSubmit'>Edit</button>";
                            echo "<br />";
                            echo "<br />";
                        echo "</form>";
                    echo "</div>";
                    echo "<script type='text/javascript'>";
                        echo "enable_element ('ToAccount','Type','Transfer');";
                        echo "disable_element ('Payee','Type','Transfer');";
                    echo "</script>";  
                }
            else
                {
                    echo "<div class='container'>";
                        echo "<br />";
                        echo "<br />";
                        echo "<h3 align = 'center'>Wrong transaction ID</h3>";
                        echo "<br />";
                        echo "<br />";
                        echo "<input type='button' class='btn btn-lg btn-success btn-block' value='Insert new' onclick=".'"top.location.href = '."'new_transaction.php'".'" />';
                        echo "<br />";
                        echo "<br />";
                    echo "</div>";
                }
        }
    ?>

</body>
</html>