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
                echo "<input id = 'Date' type='date' name='Date' class='form-control'   value = '".$TrDateDefault."'/>";
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
                echo "<select id ='Status' name='Status' class='form-control' required>";
                for ($i = 0; $i < sizeof($StatusArrayDesc); $i++)
                {
                        if ($StatusArrayDB[$i] == $TrStatusDefault)
                            {
                                echo "<option value = '".$StatusArrayDB[$i]."' selected>".$StatusArrayDesc[$i]."</option>";
                            }
                        else
                            {
                                echo "<option value = '".$StatusArrayDB[$i]."'>".$StatusArrayDesc[$i]."</option>";
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
                echo "<select id ='Type' name='Type' class='form-control'  onchange='disable_element(".'"'."ToAccount".'","'."Type".'","'."Transfer".'"'.")' required>";
                for ($i = 0; $i < sizeof($TypeArrayDesc); $i++)
                {
                        if ($TypeArrayDesc[$i] == $TrTypeDefault)
                            {
                                echo "<option value='".$TypeArrayDesc[$i]."'selected>".$TypeArrayDesc[$i]."</option>";
                            }
                        else
                            {
                                echo "<option value='".$TypeArrayDesc[$i]."'>".$TypeArrayDesc[$i]."</option>";
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
            $AccountArrayDesc[sizeof($AccountArrayDesc)] = "None";
            
            echo "<div class='form-group'>";
                echo "<label for='Account'>Account</label>";
                echo "<select id ='Account' name='Account' class='form-control' required>";
                for ($i = 0; $i < sizeof($AccountArrayDesc); $i++)
                {
                        if ($AccountArrayDesc[$i] == $TrAccountDefault)
                            {
                                echo "<option selected>".$AccountArrayDesc[$i]."</option>";
                            }
                        else
                            {
                                echo "<option>".$AccountArrayDesc[$i]."</option>";
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
                echo "<select id ='ToAccount' name='ToAccount' class='form-control' required>";
                for ($i = 0; $i < sizeof($ToAccountArrayDesc); $i++)
                {
                        if ($ToAccountArrayDesc[$i] == $TrToAccountDefault)
                            {
                                echo "<option selected>".$ToAccountArrayDesc[$i]."</option>";
                            }
                        else
                            {
                                echo "<option>".$ToAccountArrayDesc[$i]."</option>";
                            }
                }
                echo "</select>";
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
                        echo "<input id='Amount' type='number' name='Amount' class='form-control' placeholder='New transaction amount' min='0' step ='0.01' value='".$TrAmountDefault."' required />";
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
                        echo "<textarea id='Notes' name='Notes' class='form-control' rows='5' placeholder='New transaction notes'>".$TrNotesDefault."</textarea>";
                    }
                else
                    {
                        echo "<textarea id='Notes' name='Notes' class='form-control' rows='5' placeholder='New transaction notes'></textarea>";
                    }
                echo "<span class='help-block'></span>";
            echo "</div>";
                
        }
        
        
        
    //Create setting input element
    function input_setting ($VarName,$VarValue,$PlaceHolder,$InputType,$Required)
        {
            echo "<div class='form-group'>";
                echo "<label for='Set_".$VarName."'>".str_replace("_"," ",$VarName)."</label>";
                if ($VarValue == "")
                    {
                        if ($Required == True)
                            {
                                echo "<input id='Set_".$VarName."' type='".$InputType."' name='Set_".$VarName."' class='form-control' placeholder='".$PlaceHolder."' required />";
                            }
                        elseif ($Required == False)
                            {
                                echo "<input id='Set_".$VarName."' type='".$InputType."' name='Set_".$VarName."' class='form-control' placeholder='".$PlaceHolder."' />";
                            }
                    }
                else
                    {
                        if ($Required == True)
                            {
                                echo "<input id='Set_".$VarName."' type='".$InputType."' name='Set_".$VarName."' class='form-control' value='".$VarValue."' required />";
                            }
                        elseif ($Required == False)
                            {
                                echo "<input id='Set_".$VarName."' type='".$InputType."' name='Set_".$VarName."' class='form-control' value='".$VarValue."' />";
                            } 
                    }
                echo "<span class='help-block'></span>";
            echo "</div>";
                
        }
    //Create password input element    
    function input_setting_password ($VarName,$PlaceHolder,$Required,$OnChange)
        {
            echo "<div class='form-group'>";
                echo "<label for='Set_".$VarName."'>".str_replace("_"," ",$VarName)."</label>";
                if ($OnChange == "")
                    {
                        if ($Required == True)
                            {
                                echo "<input id='Set_".$VarName."' type='Password' name='Set_".$VarName."' class='form-control' placeholder='".$PlaceHolder."' required />";
                            }
                        elseif ($Required == False)
                            {
                                echo "<input id='Set_".$VarName."' type='Password' name='Set_".$VarName."' class='form-control' placeholder='".$PlaceHolder."' />";
                            }
                    }
                else
                    {
                        if ($Required == True)
                            {
                                echo "<input id='Set_".$VarName."' type='Password' name='Set_".$VarName."' class='form-control' onchange='".$OnChange."' required />";
                            }
                        elseif ($Required == False)
                            {
                                echo "<input id='Set_".$VarName."' type='Password' name='Set_".$VarName."' class='form-control' onchange='".$OnChange."' />";
                            } 
                    }
                echo "<span class='help-block'></span>";
            echo "</div>";
                
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
                    $db = new PDO("sqlite:".$const_dbpath);
        
                    $db -> exec   ("CREATE TABLE IF NOT EXISTS [New_Transaction](
                                            ID          INTEGER     PRIMARY KEY  AUTOINCREMENT,
                                            Date        DATE        NOT NULL,
                                            Account     TEXT        NOT NULL,
                                            ToAccount   TEXT,
                                            Status      TEXT        NOT NULL,
                                            Type        TEXT        NOT NULL,
                                            Amount      NUMERIC     NOT NULL,
                                            Notes       TEXT
                                        );");
                    $db -> exec     ("CREATE TABLE IF NOT EXISTS [Account_List] ( 
                                            AccountName TEXT PRIMARY KEY NOT NULL 
                                        );");
                    $db -> exec     ("CREATE TABLE IF NOT EXISTS [Parameters] ( 
                                            Parameter   TEXT PRIMARY KEY NOT NULL,
                                            Value       TEXT
                                        );");
                    $db -> exec     ("INSERT or IGNORE INTO Parameters VALUES ('Version','".$const_app_version."');");
                    $db = null;
                }
            catch(PDOException $e)
                {
                    $error = $e->getMessage();
                }
            
            return $error;
        }
    
    
    
    // Insert transaction 
    function transaction_insert ($TrDate, $TrStatus, $TrType, $TrAccount, $TrToAccount, $TrAmount, $TrNotes)
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:".$const_dbpath);
            
            $statement = $db -> prepare("INSERT INTO New_Transaction (Date, Status, Type, Account, ToAccount, Amount, Notes)
                                        VALUES(:TrDate, :TrStatus, :TrType, :TrAccount, :TrToAccount, :TrAmount, :TrNotes);");
                $statement->bindParam(":TrDate",$TrDate);
                $statement->bindParam(":TrStatus",$TrStatus);
                $statement->bindParam(":TrType",$TrType);
                $statement->bindParam(":TrAccount",$TrAccount);
                $statement->bindParam(":TrToAccount",$TrToAccount);
                $statement->bindParam(":TrAmount",$TrAmount);
                $statement->bindParam(":TrNotes",$TrNotes);
            $statement-> execute ();
            
            $db = null;
        }
    
    

    // Get transaction Max ID
    function transaction_maxid ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:".$const_dbpath);
            
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
            $db = new PDO("sqlite:".$const_dbpath);
            
            $results = $db -> query("SELECT Id, Date, Status, Type, Account, ToAccount, Amount, Notes FROM New_Transaction;");
            $resultarray = array();
            $resultarray = $results->fetchall(PDO::FETCH_ASSOC);

            $db = null;
            return $resultarray;
        }
        
        
        
    // Select all transaction for show
    function transaction_select_all_show ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:".$const_dbpath);
            
            $results = $db -> query("SELECT Id, Date, Type, Account, Amount, Notes FROM New_Transaction;");
            $resultarray = array();
            $resultarray = $results->fetchall(PDO::FETCH_ASSOC);
            
            $db = null;
            return $resultarray;
        }
    
    
    
    // Select one transaction
    function transaction_select_one ($TrEditNr)
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:".$const_dbpath);
            
            $statement = $db-> prepare("SELECT Id, Date, Status, Type, Account, ToAccount, Amount, Notes FROM New_Transaction WHERE ID = :TrEditNr;");
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
            $db = new PDO("sqlite:".$const_dbpath);
            
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
            $db = new PDO("sqlite:".$const_dbpath);
            $db->exec   ("DELETE FROM New_Transaction WHERE ID IN (".$SQLDelete.");");
            
            $db = null;
        }
    
    
    
    // Update transaction 
    function transaction_update ($TrEditedId,$TrDate,$TrStatus,$TrType,$TrAccount,$TrToAccount,$TrAmount,$TrNotes)
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:".$const_dbpath);
            
            $statement = $db-> prepare("UPDATE New_Transaction SET Date = :TrDate, Status = :TrStatus, Type = :TrType,
                                        Account = :TrAccount, ToAccount = :TrToAccount, Amount = :TrAmount, Notes = :TrNotes
                                        WHERE ID = :TrEditedId;");
                $statement->bindParam(":TrEditedId",$TrEditedId);
                $statement->bindParam(":TrDate",$TrDate);
                $statement->bindParam(":TrStatus",$TrStatus);
                $statement->bindParam(":TrType",$TrType);
                $statement->bindParam(":TrAccount",$TrAccount);
                $statement->bindParam(":TrToAccount",$TrToAccount);
                $statement->bindParam(":TrAmount",$TrAmount);
                $statement->bindParam(":TrNotes",$TrNotes);
            $statement-> execute ();
            
            $db = null;
        }
        
    
    
    // Insert bank accounts
    function bankaccount_insert ($bankaccounts_array)
        {
            $const_dbpath = costant::database_path();
            db_function::bankaccount_delete_all();
            $db = new PDO("sqlite:".$const_dbpath);
            
            for ($i = 0; $i < sizeof($bankaccounts_array); $i++)
                {
                    $statement = $db -> prepare("INSERT INTO Account_list (AccountName) VALUES (:AccountName);");
                    $statement->bindParam(":AccountName",$bankaccounts_array[$i]);
                    $statement-> execute ();
                }
            
            $db = null;
        }
        
        
        
    //Delete all bank accounts
    function bankaccount_delete_all ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:".$const_dbpath);
            
            $db->exec   ("DELETE FROM Account_list;");
            
            $db = null;
        }
   
   
   
   //Select all bank accounts
   public function bankaccount_select_all ()
    {
        $const_dbpath = costant::database_path();
        $db = new PDO("sqlite:".$const_dbpath);
        
        $results = $db -> query("SELECT AccountName FROM Account_list ORDER BY AccountName;");    
        $resultarray = array();
        $resultarray = $results->fetchAll(PDO::FETCH_COLUMN, 0);
        
        $db = null;
        return $resultarray;
    }
}



#########################
###     Security      ###
#########################
class security
{
    function redirect_if_not_loggedin ()
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

#########################
### Database  upgrade ###
#########################
class db_upgrade
{
    function to_1_1_0 ()
        {
            $const_dbpath = costant::database_path();
            db_function::db_create();
            $db = new PDO("sqlite:".$const_dbpath);
            
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
                    echo "send_alert_and_redirect ('".$AlertMessage."','".$AlertRedirect."')";
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
                        fwrite($fileopen, "$".$key." = ".'"'.$value.'";'."\n");   
                    }
                    
                fwrite($fileopen, "?>");
                fclose($fileopen);
        }
}

##########################
###  Costant function  ###
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
    }
?>