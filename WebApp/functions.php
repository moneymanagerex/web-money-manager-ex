<?php
require_once "configuration_system.php";
if (file_exists("configuration_user.php"))
    {
        require_once "configuration_user.php";
    }


#########################
###  Design function  ###
#########################
class design
{
    //Create date input element
    function input_date ($TrDateDefault)
        {
            echo "<div class='form-group'>";
                echo "<label for='Date'>Date</label>";
                echo "<input id = 'Date' type='date' name='Date' class='form-control'   value = '${TrDateDefault}'/>";
                echo "<span class='help-block'></span>";
            echo "</div>";
                
        }
        
        
        
    //Create status input element
    function input_status ($TrStatusDefault)
        {
            $StatusArrayDesc = array ("None", "Reconciled", "Void", "Follow Up", "Duplicate");
            $StatusArrayDB = array ("", "R", "V", "F", "D");
            
            echo "<div class='form-group'>";
                echo "<label for='Status'>Status</label>";
                echo "<select id ='Status' name='Status' class='form-control'>";
                for ($i = 0; $i < sizeof($StatusArrayDesc); $i++)
                {
                        if ($StatusArrayDB[$i] == $TrStatusDefault)
                            {
                                echo "<option value = '${StatusArrayDB[$i]}' selected> ${StatusArrayDesc[$i]} </option>";
                            }
                        else
                            {
                                echo "<option value = '${StatusArrayDB[$i]}'> ${StatusArrayDesc[$i]} </option>";
                            }
                }
                echo "</select>";
                echo "<span class='help-block'></span>";
            echo "</div>";        
        }
    
        
            
    //Create type input element
    function input_type ($TrTypeDefault)
        {
            $TypeArrayDesc = array ("Withdrawal", "Deposit", "Transfer");
            
            echo "<div class='form-group'>";
                echo "<label for='Type'>Type</label>";
                echo "<select id ='Type' name='Type' class='form-control'  onchange='enable_element(\"ToAccount\",\"Type\",\"Transfer\"); disable_element(\"Payee\",\"Type\",\"Transfer\")'>";
                for ($i = 0; $i < sizeof($TypeArrayDesc); $i++)
                {
                        if ($TypeArrayDesc[$i] == $TrTypeDefault)
                            {
                                echo "<option value='${TypeArrayDesc[$i]}' selected> ${TypeArrayDesc[$i]} </option>";
                            }
                        else
                            {
                                echo "<option value='${TypeArrayDesc[$i]}'> ${TypeArrayDesc[$i]} </option>";
                            }
                }
                echo "</select>";
                echo "<span class='help-block'></span>";
            echo "</div>";        
        }
    
                
        
    //Create account input element
    function input_account ($TrAccountDefault)
        {
            $AccountArrayDesc = db_function::bankaccount_select_all();
            if (sizeof($AccountArrayDesc) == 0)
                {
                    $AccountArrayDesc[sizeof($AccountArrayDesc)] = "None";
                }
            
            echo "<div class='form-group'>";
                echo "<label for='Account'>Account</label>";
                echo "<select id ='Account' name='Account' class='form-control'>";
                for ($i = 0; $i < sizeof($AccountArrayDesc); $i++)
                {
                        if ($AccountArrayDesc[$i] == $TrAccountDefault)
                            {
                                echo "<option selected> ${AccountArrayDesc[$i]} </option>";
                            }
                        else
                            {
                                echo "<option> ${AccountArrayDesc[$i]} </option>";
                            }
                }
                echo "</select>";
                echo "<span class='help-block'></span>";
            echo "</div>";              
        }
    
    
    
    //Create toaccount input element
    function input_toaccount ($TrToAccountDefault)
        {
            $ToAccountArrayDesc = db_function::bankaccount_select_all();
            array_unshift($ToAccountArrayDesc,"None");

            echo "<div class='form-group'>";
                echo "<label for='ToAccount'>To Account</label>";
                echo "<select id ='ToAccount' name='ToAccount' class='form-control'>";
                for ($i = 0; $i < sizeof($ToAccountArrayDesc); $i++)
                {
                        if ($ToAccountArrayDesc[$i] == $TrToAccountDefault)
                            {
                                echo "<option selected> ${ToAccountArrayDesc[$i]} </option>";
                            }
                        else
                            {
                                echo "<option> ${ToAccountArrayDesc[$i]} </option>";
                            }
                }
                echo "</select>";
                echo "<span class='help-block'></span>";
            echo "</div>";              
        }
        
        
        
    //Create payee input element
    function input_payee ($TrPayeeDefault)
        {
            $PayeeArrayDesc = db_function::payee_select_all();
            array_unshift($PayeeArrayDesc,"None");

            echo "<div class='form-group'>";
                echo "<label for='Payee'>Payee</label>";
                if ($TrPayeeDefault <> "None")
                    {
                        echo "<input id='Payee' type='text' name='Payee' class='form-control' placeholder='Choose a payee' list='PayeeList' autocomplete='off' value='${TrPayeeDefault}' required />";
                    }
                else
                    {
                        echo "<input id='Payee' type='text' name='Payee' class='form-control' placeholder='Choose a payee' list='PayeeList' autocomplete='off' required />";
                    }
                echo "<datalist id='PayeeList'>";
                    for ($i = 0; $i < sizeof($PayeeArrayDesc); $i++)
                    {
                        echo "<option value='${PayeeArrayDesc[$i]}'></option>";
                    }
                echo "</datalist>";
                echo "<span class='help-block'></span>";
            echo "</div>";              
        }
        
    //Create amount input element
    function input_amount ($TrAmountDefault)
        {
            echo "<div class='form-group'>";
                echo "<label for='Amount'>Amount</label>";
                if ($TrAmountDefault <> 0)
                    {
                        echo "<input id='Amount' type='number' name='Amount' class='form-control' placeholder='New transaction amount' min='0' step ='0.01' value='${TrAmountDefault}' required />";
                    }
                else
                    {
                        echo "<input id='Amount' type='number' name='Amount' class='form-control' placeholder='New transaction amount' min='0' step ='0.01' required />";
                    }
                echo "<span class='help-block'></span>";
            echo "</div>";
                
        }
    
    
    //Create notes input element
    function input_notes ($TrNotesDefault)
        {
            echo "<div class='form-group'>";
                echo "<label for='Notes'>Notes</label>";
                if ($TrNotesDefault <> "Empty")
                    {
                        echo "<textarea id='Notes' name='Notes' class='form-control' rows='5' placeholder='New transaction notes'> ${TrNotesDefault} </textarea>";
                    }
                else
                    {
                        echo "<textarea id='Notes' name='Notes' class='form-control' rows='5' placeholder='New transaction notes'></textarea>";
                    }
                echo "<span class='help-block'></span>";
            echo "</div>";
                
        }
        
        
    //Create Hidden Field    
    function input_hidden ($FieldName,$Value)
        {
            echo "<input type='hidden' id = '${FieldName}' name='${FieldName}' value='${Value}' />";
        }
        
    //Create setting input element
    function settings ($VarName,$VarValue,$PlaceHolder,$InputType,$Required)
        {
            echo "<div class='form-group'>";
                echo "<label for='Set_${VarName}'>".str_replace("_"," ",$VarName)."</label>";
                if ($VarValue == "")
                    {
                        if ($Required == True)
                            {
                                echo "<input id='Set_${VarName}' type='${InputType}' name='Set_${VarName}' class='form-control' placeholder='${PlaceHolder}' required />";
                            }
                        elseif ($Required == False)
                            {
                                echo "<input id='Set_${VarName}' type='${InputType}' name='Set_${VarName}' class='form-control' placeholder='${PlaceHolder}' />";
                            }
                    }
                else
                    {
                        if ($Required == True)
                            {
                                echo "<input id='Set_${VarName}' type='${InputType}' name='Set_${VarName}' class='form-control' value='${VarValue}' required />";
                            }
                        elseif ($Required == False)
                            {
                                echo "<input id='Set_${VarName}' type='${InputType}' name='Set_${VarName}' class='form-control' value='${VarValue}' />";
                            } 
                    }
                echo "<span class='help-block'></span>";
            echo "</div>";
                
        }
        
          
    //Create password input element    
    function settings_password ($VarName,$PlaceHolder,$Required,$OnChange)
        {
            echo "<div class='form-group'>";
                echo "<label for='Set_${VarName}'>".str_replace("_"," ",$VarName)."</label>";
                if ($OnChange == "")
                    {
                        if ($Required == True)
                            {
                                echo "<input id='Set_${VarName}' type='Password' name='Set_${VarName}' class='form-control' placeholder='${PlaceHolder}' required />";
                            }
                        elseif ($Required == False)
                            {
                                echo "<input id='Set_${VarName}' type='Password' name='Set_${VarName}' class='form-control' placeholder='${PlaceHolder}' />";
                            }
                    }
                else
                    {
                        if ($Required == True)
                            {
                                echo "<input id='Set_${VarName}' type='Password' name='Set_${VarName}' class='form-control' placeholder='${PlaceHolder}' onchange='${OnChange}' required />";
                            }
                        elseif ($Required == False)
                            {
                                echo "<input id='Set_${VarName}' type='Password' name='Set_${VarName}' class='form-control' placeholder='${PlaceHolder}' onchange='${OnChange}' />";
                            } 
                    }
                echo "<span class='help-block'></span>";
            echo "</div>";
                
        }
        
        
    //Design setting default account
    function settings_default_account ($TrAccountDefault)
        {
            $AccountArrayDesc = db_function::bankaccount_select_all();
            if (sizeof($AccountArrayDesc) == 0)
                {
                    $AccountArrayDesc[sizeof($AccountArrayDesc)] = "None";
                }
            
            echo "<div class='form-group'>";
                echo "<label for='Default_Account'> Default Account</label>";
                echo "<select id ='Default_Account' name='Default_Account' class='form-control'>";
                for ($i = 0; $i < sizeof($AccountArrayDesc); $i++)
                {
                        if ($AccountArrayDesc[$i] == $TrAccountDefault)
                            {
                                echo "<option selected> ${AccountArrayDesc[$i]} </option>";
                            }
                        else
                            {
                                echo "<option> ${AccountArrayDesc[$i]} </option>";
                            }
                }
                echo "</select>";
                echo "<span class='help-block'></span>";
            echo "</div>";              
        }
        
        
    //Design section legend
    function section_legened ($Text)
        {
                echo "<h4>${Text}</h4>";
                echo "<hr>";
        }
        
    //Design table cell
    function table_cell ($value,$css_class)
        {
            echo "<td class='${css_class}'>";
            echo $value;
            echo "</td>";
        }
}



#########################
### Database function ###
#########################
class db_function
{
    // Create database if not exist
    public function db_create()
        {
            $const_dbpath = costant::database_path();
            $const_app_version = costant::app_version();
            $error = "ok";
            
            try
                {
                    $db = new PDO("sqlite:${const_dbpath}");
        
                    $db -> exec   ("CREATE TABLE IF NOT EXISTS [New_Transaction](
                                            ID          INTEGER     PRIMARY KEY  AUTOINCREMENT,
                                            Date        DATE        NOT NULL,
                                            Account     TEXT        NOT NULL,
                                            ToAccount   TEXT,
                                            Status      TEXT        NOT NULL,
                                            Type        TEXT        NOT NULL,
                                            Payee       TEXT,
                                            Category    TEXT,
                                            SubCategory TEXT,
                                            Amount      NUMERIC     NOT NULL,
                                            Notes       TEXT
                                        );");
                    $db -> exec     ("CREATE TABLE IF NOT EXISTS [Account_List] ( 
                                            AccountName TEXT PRIMARY KEY NOT NULL 
                                        );");
                    $db -> exec     ("CREATE TABLE IF NOT EXISTS [Payee_List] ( 
                                            PayeeName TEXT PRIMARY KEY NOT NULL 
                                        );");
                    $db -> exec     ("CREATE TABLE IF NOT EXISTS [Parameters] ( 
                                            Parameter   TEXT PRIMARY KEY NOT NULL,
                                            Value       TEXT
                                        );");
                    $db -> exec     ("INSERT or IGNORE INTO Parameters VALUES ('Version','${const_app_version}');");
                    $db = null;
                }
            catch(PDOException $e)
                {
                    $error = $e->getMessage();
                }
            
            return $error;
        }
    
    
    // Get database version
    function db_version ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $statement = $db->query("SELECT Value FROM Parameters WHERE Parameter = 'Version';");
            $db_version=$statement->fetchColumn(0);
            
            $db = null;
            return $db_version;
        }
        
    // Vacuum database
    function db_vacuum ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $db->exec ("VACUUM");
            
            $db = null;
        }
        
        
    // Insert transaction 
    function transaction_insert ($TrDate, $TrStatus, $TrType, $TrAccount, $TrToAccount, $TrPayee, $TrAmount, $TrNotes)
        {
            $none = "None";
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $statement = $db -> prepare("INSERT INTO New_Transaction (Date, Status, Type, Account, ToAccount, Payee, Category, SubCategory, Amount, Notes)
                                        VALUES(:TrDate, :TrStatus, :TrType, :TrAccount, :TrToAccount, :TrPayee, :TrCategory, :TrSubCategory, :TrAmount, :TrNotes);");
                $statement->bindParam(":TrDate",$TrDate);
                $statement->bindParam(":TrStatus",$TrStatus);
                $statement->bindParam(":TrType",$TrType);
                $statement->bindParam(":TrAccount",$TrAccount);
                $statement->bindParam(":TrToAccount",$TrToAccount);
                $statement->bindParam(":TrPayee",$TrPayee);
                $statement->bindParam(":TrCategory",$none);
                $statement->bindParam(":TrSubCategory",$none);
                $statement->bindParam(":TrAmount",$TrAmount);
                $statement->bindParam(":TrNotes",$TrNotes);
            $statement-> execute ();
            
            $db = null;
        }
    
    

    // Get transaction Max ID
    function transaction_select_maxid ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $statement = $db->query("SELECT MAX(ID) FROM New_Transaction;");
            $resultcount=$statement->fetchColumn(0);
            $transaction_maxid = intval($resultcount);
            
            $db = null;
            return $transaction_maxid;
        }
    
    
    
    // Select all transaction
    function transaction_select_all ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $results = $db -> query("SELECT * FROM New_Transaction;");
            $resultarray = array();
            if($results !== false)
                {
                    $resultarray = $results->fetchall(PDO::FETCH_ASSOC);
                }
            $db = null;
            return $resultarray;
        }
        
        
        
    // Select all transaction order by date
    function transaction_select_all_order_by_date ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $results = $db -> query("SELECT * FROM New_Transaction ORDER BY Date;");
            $resultarray = array();
            if($results !== false)
                {
                    $resultarray = $results->fetchall(PDO::FETCH_ASSOC);
                }
            $db = null;
            return $resultarray;
        }
    
    
    
    // Select one transaction
    function transaction_select_one ($TrEditNr)
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $statement = $db-> prepare("SELECT * FROM New_Transaction WHERE ID = :TrEditNr;");
                    $statement->bindParam(":TrEditNr",$TrEditNr);
            $statement-> execute ();
            $resultarray = array();
            $resultarray = $statement->fetch(PDO::FETCH_ASSOC);
            if(!$resultarray) {$resultarray = array();}
            
            $db = null;
            return $resultarray;
        }
    
    
    
    //Delete all transaction
    function transaction_delete_all ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $db->exec   ("DELETE FROM New_Transaction;");
            
            $db = null;
        }
        
        
        
    //Delete group transaction
    function transaction_delete_group ($TrDeleteArr)
        {
            $const_dbpath = costant::database_path();
            $N = count($TrDeleteArr);
            $SQLDelete = "";
            for($i=0; $i < $N; $i++)
            {
                $SQLDelete = $SQLDelete.$TrDeleteArr[$i] . ",";
            }
            $SQLDelete = rtrim($SQLDelete, ",");
            $db = new PDO("sqlite:${const_dbpath}");
            $db->exec   ("DELETE FROM New_Transaction WHERE ID IN (${SQLDelete});");
            
            $db = null;
        }
    
    
    
    // Update transaction 
    function transaction_update ($TrEditedId,$TrDate,$TrStatus,$TrType,$TrAccount,$TrToAccount,$TrPayee,$TrAmount,$TrNotes)
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $statement = $db-> prepare("UPDATE New_Transaction SET Date = :TrDate, Status = :TrStatus, Type = :TrType,
                                        Account = :TrAccount, ToAccount = :TrToAccount, Payee = :TrPayee, Amount = :TrAmount, Notes = :TrNotes
                                        WHERE ID = :TrEditedId;");
                $statement->bindParam(":TrEditedId",$TrEditedId);
                $statement->bindParam(":TrDate",$TrDate);
                $statement->bindParam(":TrStatus",$TrStatus);
                $statement->bindParam(":TrType",$TrType);
                $statement->bindParam(":TrAccount",$TrAccount);
                $statement->bindParam(":TrToAccount",$TrToAccount);
                $statement->bindParam(":TrPayee",$TrPayee);
                $statement->bindParam(":TrAmount",$TrAmount);
                $statement->bindParam(":TrNotes",$TrNotes);
            $statement-> execute ();
            
            $db = null;
        }
        
    
    
    // Insert bank accounts
    function bankaccount_insert ($bankaccounts_array)
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            for ($i = 0; $i < sizeof($bankaccounts_array); $i++)
                {
                    $statement = $db -> prepare("INSERT or IGNORE INTO Account_list (AccountName) VALUES (:AccountName);");
                    $statement->bindParam(":AccountName",$bankaccounts_array[$i]);
                    $statement-> execute ();
                }
            
            $db = null;
        }
        
        
        
    //Delete all bank accounts
    function bankaccount_delete_all ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $db->exec   ("DELETE FROM Account_list;");
            
            $db = null;
        }
   
   
   
   //Select all bank accounts
   public function bankaccount_select_all ()
    {
        $const_dbpath = costant::database_path();
        $db = new PDO("sqlite:${const_dbpath}");
        
        $results = $db -> query("SELECT AccountName FROM Account_list ORDER BY AccountName COLLATE nocase;");    
        $resultarray = array();
        if($results !== false)
            {
                $resultarray = $results->fetchAll(PDO::FETCH_COLUMN, 0);
            }
        $db = null;
        return $resultarray;
    }
    
    
    
    //Select all payee
   public function payee_select_all ()
    {
        $const_dbpath = costant::database_path();
        $db = new PDO("sqlite:${const_dbpath}");
        
        $results = $db -> query("SELECT PayeeName FROM Payee_List ORDER BY PayeeName COLLATE nocase;");    
        $resultarray = array();
        if($results !== false)
            {
                $resultarray = $results->fetchAll(PDO::FETCH_COLUMN, 0);
            }
        
        $db = null;
        return $resultarray;
    }
   
   
   // Insert all payees accounts
    function payee_insert ($payees_array)
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            for ($i = 0; $i < sizeof($payees_array); $i++)
                {
                    if ($payees_array[$i] !== "None")
                        {
                            $statement = $db -> prepare("INSERT or IGNORE INTO Payee_list (PayeeName) VALUES (:PayeeName);");
                            $statement->bindParam(":PayeeName",$payees_array[$i]);
                            $statement-> execute ();
                        }
                }
            
            $db = null;
        }
    
    
    //Delete all payee
    function payee_delete_all ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $db->exec   ("DELETE FROM Payee_list;");
            
            $db = null;
        }
}



#########################
###     Security      ###
#########################
class security
{
    function redirect_if_not_loggedin ()
        {
            $disable_authentication = costant::disable_authentication();
            if (($disable_authentication) == False)
            {
                if(isset($_SESSION["login_string"],$_SESSION["username"]))
                {
                    $password = costant::login_password(); 
                    $login_string = $_SESSION["login_string"];
                    $username = $_SESSION["username"];
                    $user_browser = $_SERVER["HTTP_USER_AGENT"];
                    
                    $login_check = hash('sha512', $password . $user_browser);
                    if ($login_check !== $login_string)
                        {
                            header("Location: index.php");
                        }
                }
                else
                {
                    header("Location: index.php");
                }
            }
        }
        
        
    function generate_guid()
        {
            if (function_exists('com_create_guid'))
                {
                    return com_create_guid();
                }
            else
                {
                    mt_srand((double)microtime()*10000);
                    $charid = strtoupper(md5(uniqid(rand(), true)));
                    $hyphen = chr(45);// "-"
                    $uuid = chr(123)// "{"
                    .substr($charid, 0, 8).$hyphen
                    .substr($charid, 8, 4).$hyphen
                    .substr($charid,12, 4).$hyphen
                    .substr($charid,16, 4).$hyphen
                    .substr($charid,20,12)
                    .chr(125);// "}"
                    return $uuid;
                }
        }
}

#########################
### Database  upgrade ###
#########################
class db_upgrade
{
    function upgrade_db ()
        {
            $start_db_version = db_function::db_version();
            $app_version = costant::app_version();
            while (db_function::db_version() !== $app_version)
                {
                    switch (db_function::db_version())
                        {
                            case "0.9.2":
                                db_upgrade::to_0_9_3();
                                break;
                            case "0.9.3":
                                db_upgrade::upgrade_version("0.9.4");
                                break;
                            case "0.9.4":
                                db_upgrade::upgrade_version("0.9.5");
                                break;
                            case "0.9.5":
                                db_upgrade::upgrade_version("0.9.6");
                                break;
                            case "0.9.6":
                                db_upgrade::to_0_9_7();
                                break;
                            case $app_version;
                                break;
                            default:
                                various::send_alert_and_redirect("Database version not compliant: DB Version = ".db_function::db_version()." - APP Version = ${app_version}","error.php");
                                break 2;
                        }
                }
            if ($start_db_version !== $app_version && db_function::db_version() == $app_version)
                {
                    return "update_done";
                }
            else
                {
                    return "update_not_need";
                }
        }
        
    function to_0_9_3 ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $db->exec   ("ALTER TABLE New_Transaction RENAME TO New_Transaction_Old");
            db_function::db_create();
            $db->exec   ("INSERT INTO New_Transaction (Date, Status, Type, Account, ToAccount, Payee, Amount, Notes) SELECT Date, Status, Type, Account, ToAccount, 'None', Amount, Notes FROM New_Transaction_Old");
            $db->exec   ("DROP TABLE New_Transaction_Old");
            $db->exec   ("UPDATE Parameters SET Value = '0.9.3' WHERE Parameter = 'Version';");           
            $db = null;
            
            $parameterarray = array
                (
                    "disable_authentication"=> costant::disable_authentication() ? 'True' : 'False',
                    "user_username"         => costant::login_username(),
                    "user_password"         => costant::login_password(),
                    "disable_payee"         => "False",
                    "desktop_guid"          => costant::desktop_guid(),
                    "defaultaccountname"    => costant::transaction_account_default()
                );
            various::update_configuration_file($parameterarray);
        }
    
    function to_0_9_7 ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $db->exec   ("ALTER TABLE New_Transaction RENAME TO New_Transaction_Old");
            db_function::db_create();
            $db->exec   ("INSERT INTO New_Transaction (Date, Status, Type, Account, ToAccount, Payee, Category, SubCategory, Amount, Notes)
                            SELECT Date, Status, Type, Account, ToAccount, Payee, 'None', 'None', Amount, Notes FROM New_Transaction_Old");
            $db->exec   ("DROP TABLE New_Transaction_Old");
            $db->exec   ("UPDATE Parameters SET Value = '0.9.7' WHERE Parameter = 'Version';");           
            $db = null;
        }

    function upgrade_version ($version)
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $db->exec   ("UPDATE Parameters SET Value = '${version}' WHERE Parameter = 'Version';");           
            $db = null;
        }
}



##########################
###  Various function  ###
##########################
class various
{
    function send_alert_and_redirect ($AlertMessage, $AlertRedirect)
        {
                echo "<script src='functions.js' type='text/javascript'></script>";
                echo "<script language='javascript'>";
                if ($AlertRedirect <> "None")
                {
                    echo "send_alert_and_redirect ('${AlertMessage}','${AlertRedirect}')";
                }
                echo "</script>";
        }
        
    function update_configuration_file ($ParameterArray)
        {
                $configfile="configuration_user.php"; 
                
                if (file_exists($configfile))
                    {
                        unlink($configfile);
                    }
                
                $fileopen = fopen($configfile, 'w');
                fwrite($fileopen, "<?php"."\n");
                fwrite($fileopen, "#######################"."\n");
                fwrite($fileopen, "##    User Setting   ##"."\n");
                fwrite($fileopen, "#######################"."\n");
                fwrite($fileopen, "\n");
                
                foreach ($ParameterArray as $key => $value)
                    {
                        fwrite($fileopen, "\$${key} = \"${value}\";\n");   
                    }
                    
                fwrite($fileopen, "?>");
                fclose($fileopen);
        }
}

##########################
### Constant  function ###
##########################
class costant
    {
        function login_username ()
            {
                global $user_username;
                return $user_username;
            }
        
        function login_password ()
            {
                global $user_password;
                return $user_password;
            }
        
        function transaction_account_default ()
            {
                global $defaultaccountname;
                return $defaultaccountname;
            }
        
        function database_path ()
            {
                global $dbpath;
                return $dbpath;
            }
        
        function desktop_guid ()
            {
                global $desktop_guid;
                return $desktop_guid;
            }
            
        function app_version ()
            {
                global $app_version;
                return $app_version;
            }
            
        function api_version ()
            {
                global $api_version;
                return $api_version;
            }
        
        function import_delimiter ()
            {
                global $import_delimiter;
                return $import_delimiter;
            }
        
        function disable_authentication ()
            {
                global $disable_authentication;
                if (($disable_authentication) == "True")
                    {
                        return True;
                    }
                else
                    {
                        return False;
                    }
            }
        
        function disable_payee ()
            {
                global $disable_payee;
                if (($disable_payee) == "True")
                    {
                        return True;
                    }
                else
                    {
                        return False;
                    }
            }
            
        function current_page_url ()
            {
             $pageURL = 'http';
             #if ($_SERVER["HTTPS"] == "on")
             #    {
             #       $pageURL .= "s";
             #    }
             $pageURL .= "://";
             if ($_SERVER["SERVER_PORT"] != "80")
                {
                    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
                }
             else
                {
                    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
                 }
             return $pageURL;
            }
    }
?>