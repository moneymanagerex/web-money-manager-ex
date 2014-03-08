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
	
    <title>New Transaction</title>
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

    <div class="container">
        <form id="New_Transaction" class="form-new-transaction" method="post" action = "insert.php" onsubmit="return confirm_if_not_present_in_datalist('Payee','PayeeList','Do you want to add the new payee')">
            <h3 align="center">Insert new transaction</h3>
            <br />
            <?php
                #Import common file
                require_once "functions.php";
                $const_defaultaccountname = costant::transaction_account_default();
                
                design::input_date("2014-01-01");
                design::input_status("R");
                design::input_type("Withdrawal");
                design::input_account($const_defaultaccountname);
                design::input_toaccount("None");
                if (costant::disable_payee() !== True)
                    {
                        design::input_payee("None");
                    }
                else
                    {
                        design::input_hidden("Payee","None");
                    }
                design::input_amount("0");
                design::input_notes("Empty");
                
            ?>
            <script type="text/javascript">
            var date_today = get_today();
            document.getElementById('Date').value=date_today;
            enable_element ("ToAccount","Type","Transfer");
            disable_element ("Payee","Type","Transfer");
            </script>  
            <!--
                <button type="button" id="Insert" name="Insert" class="btn btn-lg btn-success btn-block" onclick="confirm_if_not_present_in_datalist ('Payee','PayeeList')">Insert</button>
            -->
            <button type="submit" id="Insert" name="Insert" class="btn btn-lg btn-success btn-block">Insert</button>
            <br />
            <br />
        </form>
    </div>
</body>
</html>