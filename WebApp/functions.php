<?php
require_once "configuration_system.php";
if (file_exists("configuration_user.php"))
    {require_once "configuration_user.php";}


#########################
###  Design function  ###
#########################
class design
{
    //Create date input element
    public static function input_date ($TrDateDefault)
        {
            echo "<div class='form-group'>";
                echo "<label for='Date'>Date</label>";
                echo "<input id = 'Date' type='date' name='Date' class='form-control'   value = '${TrDateDefault}'/>";
                echo "<span class='help-block'></span>";
            echo "</div>\n";
                
        }
        
        
    //Create status input element
    public static function input_status ($TrStatusDefault)
        {
            $StatusArrayDesc = array ("None", "Reconciled", "Void", "Follow Up", "Duplicate");
            $StatusArrayDB = array ("", "R", "V", "F", "D");
            
            echo "<div class='form-group'>";
                echo "<label for='Status'>Status</label>";
                echo "<select id ='Status' name='Status' class='form-control'>";
                for ($i = 0; $i < sizeof($StatusArrayDesc); $i++)
                {
                    if ($StatusArrayDB[$i] == $TrStatusDefault)
                        {echo "<option value = '${StatusArrayDB[$i]}' selected> ${StatusArrayDesc[$i]} </option>";}
                    else
                        {echo "<option value = '${StatusArrayDB[$i]}'> ${StatusArrayDesc[$i]} </option>";}
                }
                echo "</select>";
                echo "<span class='help-block'></span>";
            echo "</div>\n";        
        }
    
                
    //Create type input element
    public static function input_type ($TrTypeDefault)
        {
            $TypeArrayDesc = array ("Withdrawal", "Deposit", "Transfer");
            
            echo "<div class='form-group'>";
                echo "<label for='Type'>Type</label>";
                echo "<select id ='Type' name='Type' class='form-control' onchange='enable_element(\"ToAccount\",\"Type\",\"Transfer\"); disable_element(\"Payee\",\"Type\",\"Transfer\")'>";
                for ($i = 0; $i < sizeof($TypeArrayDesc); $i++)
                {
                    if ($TypeArrayDesc[$i] == $TrTypeDefault)
                        {echo "<option value='${TypeArrayDesc[$i]}' selected> ${TypeArrayDesc[$i]} </option>";}
                    else
                        {echo "<option value='${TypeArrayDesc[$i]}'> ${TypeArrayDesc[$i]} </option>";}
                }
                echo "</select>";
                echo "<span class='help-block'></span>";
            echo "</div>\n";        
        }
                
        
    //Create account input element
    public static function input_account ($TrAccountDefault)
        {
            $AccountArrayDesc = db_function::bankaccount_select_all();
            if (sizeof($AccountArrayDesc) == 0)
                {$AccountArrayDesc[0] = "None";}
            
            echo "<div class='form-group'>";
                echo "<label for='Account'>Account</label>";
                echo "<select id ='Account' name='Account' class='form-control'>";
                for ($i = 0; $i < sizeof($AccountArrayDesc); $i++)
                {
                    if ($AccountArrayDesc[$i] == $TrAccountDefault)
                        {echo "<option value=\"${AccountArrayDesc[$i]}\" selected> ${AccountArrayDesc[$i]} </option>";}
                    else
                        {echo "<option value=\"${AccountArrayDesc[$i]}\"> ${AccountArrayDesc[$i]} </option>";}
                }
                echo "</select>";
                echo "<span class='help-block'></span>";
            echo "</div>\n";              
        }
    
    
    //Create toaccount input element
    public static function input_toaccount ($TrToAccountDefault)
        {
            $ToAccountArrayDesc = db_function::bankaccount_select_all();
            array_unshift($ToAccountArrayDesc,"None");

            echo "<div class='form-group'>";
                echo "<label for='ToAccount'>To Account</label>";
                echo "<select id ='ToAccount' name='ToAccount' class='form-control'>";
                for ($i = 0; $i < sizeof($ToAccountArrayDesc); $i++)
                {
                    if ($ToAccountArrayDesc[$i] == $TrToAccountDefault)
                        {echo "<option value=\"${ToAccountArrayDesc[$i]}\" selected> ${ToAccountArrayDesc[$i]} </option>";}
                    else
                        {echo "<option value=\"${ToAccountArrayDesc[$i]}\"> ${ToAccountArrayDesc[$i]} </option>";}
                }
                echo "</select>";
                echo "<span class='help-block'></span>";
            echo "</div>\n";              
        }
        
        
    //Create payee input element
    public static function input_payee ($TrPayeeDefault)
        {
            $PayeeArrayDesc = db_function::payee_select_all_name();
            array_unshift($PayeeArrayDesc,"None");

            echo "<div class='form-group'>";
                echo "<label for='Payee'>Payee</label>";
                echo "<input id='Payee' type='text' name='Payee' class='form-control' placeholder='Choose a payee' autocomplete = 'off' required />";
                echo "<span class='help-block'></span>";
            echo "</div>\n";
            
            echo "<script type='text/javascript'>";
                echo "var PayeeList = " . json_encode($PayeeArrayDesc) . ";";
                echo "$('#Payee').typeahead({hint: true, highlight: true, minLength: 0},{name: 'PayeeList', limit:15, displayKey: 'value', source: substringMatcher(PayeeList)});";
                if ($TrPayeeDefault != "")
                    {echo "document.getElementById('Payee').value='${TrPayeeDefault}'";}
            echo "</script>";
        }
    
    
    //Create category input element
    public static function input_category ($TrCategoryDefault)
        {
            $CategoryArrayDesc = db_function::category_select_distinct();
            array_unshift($CategoryArrayDesc,"None");

            echo "<div class='form-group'>";
                echo "<label for='Category'>Category</label>";
                echo "<input id='Category' type='text' name='Category' class='form-control' placeholder='Choose a category' autocomplete = 'off' required />";
                echo "<span class='help-block'></span>";
            echo "</div>\n";
            
            echo "<script type='text/javascript'>";
                echo "var CategoryList = " . json_encode($CategoryArrayDesc) . ";";
                echo "$('#Category').typeahead({hint: true, highlight: true, minLength: 0},{name: 'CategoryList', limit:15, displayKey: 'value', source: substringMatcher(CategoryList)});";
                if ($TrCategoryDefault != "")
                    {echo "document.getElementById('Category').value='${TrCategoryDefault}';";}
            echo "</script>";
        }
        
        
    //Create subcategory input element
    public static function input_subcategory ($TrSubCategoryDefault)
        {
            echo "<div class='form-group'>";
                echo "<label for='SubCategory'>SubCategory</label>";
                echo "<input id='SubCategory' type='text' name='SubCategory' class='form-control' placeholder='Choose a subcategory' autocomplete='off' />";
                echo "<span class='help-block'></span>";
            echo "</div>\n";
            
            echo "<script type='text/javascript'>";
                if ($TrSubCategoryDefault != "")
                    {echo "document.getElementById('SubCategory').value='${TrSubCategoryDefault}';";}
            echo "</script>";
        }
        
        
    //Create amount input element
    public static function input_amount ($TrAmountDefault)
        {
            echo "<div class='form-group'>";
                echo "<label for='Amount'>Amount</label>";
                if ($TrAmountDefault <> 0)
                    {
                        echo "<input id='Amount' type='number' name='Amount' class='form-control' placeholder='New transaction amount' min='0.01' step ='0.01' value='${TrAmountDefault}' required />";
                    }
                else
                    {
                        echo "<input id='Amount' type='number' name='Amount' class='form-control' placeholder='New transaction amount' min='0.01' step ='0.01' required />";
                    }
                echo "<span class='help-block'></span>";
            echo "</div>\n";
                
        }
    
    
    //Create notes input element
    public static function input_notes ($TrNotesDefault)
        {
            echo "<div class='form-group'>";
                echo "<label for='Notes'>Notes</label>";
                if ($TrNotesDefault <> "Empty")
                    {
                        echo "<textarea id='Notes' name='Notes' class='form-control' rows='5' placeholder='New transaction notes'>${TrNotesDefault}</textarea>";
                    }
                else
                    {
                        echo "<textarea id='Notes' name='Notes' class='form-control' rows='5' placeholder='New transaction notes'></textarea>";
                    }
                echo "<span class='help-block'></span>";
            echo "</div>\n";
                
        }
        
        
    //Create Hidden Field    
    public static function input_hidden ($FieldName,$Value)
        {echo "<input type='hidden' id = '${FieldName}' name='${FieldName}' value='${Value}' />";}
     
        
    //Create setting input element
    public static function settings ($VarName,$VarValue,$PlaceHolder,$InputType,$Required)
        {
            echo "<div class='form-group'>";
                echo "<label for='Set_${VarName}'>".str_replace("_"," ",$VarName)."</label>";
                if ($VarValue == "")
                    {
                        if ($Required == True)
                            {
                                echo "<input id='Set_${VarName}' type='${InputType}' name='Set_${VarName}' class='form-control' placeholder='${PlaceHolder}' autocomplete = 'off' required />";
                            }
                        elseif ($Required == False)
                            {
                                echo "<input id='Set_${VarName}' type='${InputType}' name='Set_${VarName}' class='form-control' placeholder='${PlaceHolder}' autocomplete = 'off' />";
                            }
                    }
                else
                    {
                        if ($Required == True)
                            {
                                echo "<input id='Set_${VarName}' type='${InputType}' name='Set_${VarName}' class='form-control' value='${VarValue}' autocomplete = 'off' required />";
                            }
                        elseif ($Required == False)
                            {
                                echo "<input id='Set_${VarName}' type='${InputType}' name='Set_${VarName}' class='form-control' value='${VarValue}' autocomplete = 'off' />";
                            } 
                    }
                echo "<span class='help-block'></span>";
            echo "</div>\n";    
        }
        
    //Create seting checkbox element        
    public static function settings_checkbox ($VarName,$VarValue,$VarDescription)
        {
            echo "<div class='checkbox'>";
                echo "<label>";
                    if ($VarValue == True)
                        {echo "<input id='${VarName}' type='checkbox' name='${VarName}' value='True' checked>${VarDescription}";}
                    else
                        {echo "<input id='${VarName}' type='checkbox' name='${VarName}' value='True'>${VarDescription}";}
                echo "</label>";
            echo "</div>\n";        
        }


    //Create password input element    
    public static function settings_password ($VarName,$PlaceHolder,$Required)
        {
            echo "<div class='form-group'>";
                echo "<label for='Set_${VarName}'>".str_replace("_"," ",$VarName)."</label>";
                    if ($Required == True)
                        {
                            echo "<input id='Set_${VarName}' type='Password' name='Set_${VarName}' class='form-control' placeholder='${PlaceHolder}' required />";
                        }
                    elseif ($Required == False)
                        {echo "<input id='Set_${VarName}' type='Password' name='Set_${VarName}' class='form-control' placeholder='${PlaceHolder}' />";}
                echo "<span class='help-block'></span>";
            echo "</div>\n";  
        }
        
        
    //Design setting default account
    public static function settings_default_account ($TrAccountDefault)
        {
            $AccountArrayDesc = db_function::bankaccount_select_all();
            if (sizeof($AccountArrayDesc) == 0)
                {$AccountArrayDesc[0] = "None";}
            
            echo "<div class='form-group'>";
                echo "<label for='Default_Account'> Default Account</label>";
                echo "<select id ='Default_Account' name='Default_Account' class='form-control'>";
                for ($i = 0; $i < sizeof($AccountArrayDesc); $i++)
                {
                    if ($AccountArrayDesc[$i] == $TrAccountDefault)
                        {echo "<option selected> ${AccountArrayDesc[$i]} </option>";}
                    else
                        {echo "<option> ${AccountArrayDesc[$i]} </option>";}
                }
                echo "</select>";
                echo "<span class='help-block'></span>";
            echo "</div>\n";              
        }
        
        
    //Design section legend
    public static function section_legened ($Text)
        {
                echo "<h4>${Text}</h4>";
                echo "<hr>";
        }
        
        
    //Design table cell
    public static function table_cell ($value,$css_class,$s_extra='')
        {
            echo '<td class="' . $css_class .'" ' . $s_extra . '>';
            echo $value;
            echo '</td>';
        }
}



#########################
### Database function ###
#########################
class db_function
{
    // Create database if not exist
    public static  function db_create()
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
                                            PayeeName TEXT PRIMARY KEY NOT NULL,
                                            DefCateg TEXT,
                                            DefSubCateg TEXT
                                        );");
                    $db -> exec     ("CREATE TABLE IF NOT EXISTS [Category_List] ( 
                                            CategoryName    TEXT NOT NULL,
                                            SubCategoryName TEXT,
                                            CONSTRAINT 'Category_Key' PRIMARY KEY
                                                (CategoryName COLLATE 'NOCASE', SubCategoryName COLLATE 'NOCASE') 
                                        );");
                    $db -> exec     ("CREATE TABLE IF NOT EXISTS [Parameters] ( 
                                            Parameter   TEXT PRIMARY KEY NOT NULL,
                                            Value       TEXT
                                        );");
                    $db -> exec     ("INSERT or IGNORE INTO Parameters VALUES ('Version','${const_app_version}');");
                    $db = null;
                }
            catch(PDOException $e)
                {$error = $e->getMessage();}
            
            return $error;
        }
    
    
    // Get database version
    public static function db_version ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $statement = $db->query("SELECT Value FROM Parameters WHERE Parameter = 'Version';");
            $db_version=$statement->fetchColumn(0);
            
            $db = null;
            return $db_version;
        }
        
        
    // Vacuum database
    public static function db_vacuum ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $db->exec ("VACUUM");
            
            $db = null;
        }
        
        
    // Insert transaction 
    public static function transaction_insert ($TrDate, $TrStatus, $TrType, $TrAccount, $TrToAccount, $TrPayee, $TrCategory, $TrSubCategory, $TrAmount, $TrNotes)
        {
            $ID = 0;
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
                $statement->bindParam(":TrCategory",$TrCategory);
                $statement->bindParam(":TrSubCategory",$TrSubCategory);
                $statement->bindParam(":TrAmount",$TrAmount);
                $statement->bindParam(":TrNotes",$TrNotes);
            $statement-> execute ();
            
            $ID = $db->lastInsertId();
            $db = null;
            return $ID;
        }
    
    
    // Get transaction Max ID
    public static function transaction_select_maxid ()
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
    public static function transaction_select_all ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $results = $db -> query("SELECT * FROM New_Transaction;");
            $resultarray = array();
            if($results !== false)
                {$resultarray = $results->fetchall(PDO::FETCH_ASSOC);}
            $db = null;
            return $resultarray;
        }
        
        
    // Select all transaction order by date
    public static function transaction_select_all_order_by_date ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $results = $db -> query("SELECT * FROM New_Transaction ORDER BY Date;");
            $resultarray = array();
            if($results !== false)
                {$resultarray = $results->fetchall(PDO::FETCH_ASSOC);}
            $db = null;
            return $resultarray;
        }
    
    
    // Select one transaction
    public static function transaction_select_one ($TrEditNr)
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
    public static function transaction_delete_all ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $db->exec   ("DELETE FROM New_Transaction;");
            
            $db = null;
        }
        
        
    //Delete group transaction
    public static function transaction_delete_group ($TrDeleteArr)
        {
            $const_dbpath = costant::database_path();
            $N = count($TrDeleteArr);
            $SQLDelete = "";
            for($i=0; $i < $N; $i++)
                {$SQLDelete = $SQLDelete.$TrDeleteArr[$i] . ",";}
            $SQLDelete = rtrim($SQLDelete, ",");
            
            $db = new PDO("sqlite:${const_dbpath}");
            $db->exec   ("DELETE FROM New_Transaction WHERE ID IN (${SQLDelete});");
            
            $db = null;
        }
    
    
    // Update transaction 
    public static function transaction_update ($TrEditedId,$TrDate,$TrStatus,$TrType,$TrAccount,$TrToAccount,$TrPayee,$TrCategory,$TrSubCategory,$TrAmount,$TrNotes)
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $statement = $db-> prepare("UPDATE New_Transaction SET Date = :TrDate, Status = :TrStatus, Type = :TrType,
                                        Account = :TrAccount, ToAccount = :TrToAccount, Payee = :TrPayee,
                                        Category = :TrCategory, SubCategory = :TrSubCategory, Amount = :TrAmount, Notes = :TrNotes
                                        WHERE ID = :TrEditedId;");
                $statement->bindParam(":TrEditedId",$TrEditedId);
                $statement->bindParam(":TrDate",$TrDate);
                $statement->bindParam(":TrStatus",$TrStatus);
                $statement->bindParam(":TrType",$TrType);
                $statement->bindParam(":TrAccount",$TrAccount);
                $statement->bindParam(":TrToAccount",$TrToAccount);
                $statement->bindParam(":TrPayee",$TrPayee);
                $statement->bindParam(":TrCategory",$TrCategory);
                $statement->bindParam(":TrSubCategory",$TrSubCategory);
                $statement->bindParam(":TrAmount",$TrAmount);
                $statement->bindParam(":TrNotes",$TrNotes);
            $statement-> execute ();
            
            $db = null;
        }
    
    
    // Insert bank accounts
    public static function bankaccount_insert_json ($BankAccountJSON)
        {
            $bankaccounts_json_list = json_decode ($BankAccountJSON,true);
            $BankAccountList = $bankaccounts_json_list["Accounts"];
            $const_dbpath = costant::database_path();
            
            $db = new PDO("sqlite:${const_dbpath}");
            $statement = $db -> prepare("INSERT or IGNORE INTO Account_list (AccountName) VALUES (:AccountName);");
            $db->beginTransaction();
            $BankAccountListSize = sizeof($BankAccountList);
            for ($i = 0; $i < $BankAccountListSize; $i++)
            {
                if ($BankAccountList[$i]["AccountName"] != "")
                {
                    $statement->bindParam(":AccountName",$BankAccountList[$i]["AccountName"]);
                    $statement->execute ();
                }
            }
            
            $db->commit();
            $db = null;
        }
        
        
    //Delete all bank accounts
    public static function bankaccount_delete_all ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $db->exec   ("DELETE FROM Account_list;");
            
            $db = null;
        }
   
   
   //Select all bank accounts
   public static function bankaccount_select_all ()
    {
        $const_dbpath = costant::database_path();
        $db = new PDO("sqlite:${const_dbpath}");
        
        $results = $db -> query("SELECT AccountName FROM Account_list ORDER BY AccountName COLLATE nocase;");    
        $resultarray = array();
        if($results !== false)
            {$resultarray = $results->fetchAll(PDO::FETCH_COLUMN, 0);}
        $db = null;
        return $resultarray;
    }
    
    
    //Select all payee
   public static function payee_select_all_name ()
    {
        $const_dbpath = costant::database_path();
        $db = new PDO("sqlite:${const_dbpath}");
        
        $results = $db -> query("SELECT PayeeName FROM Payee_List ORDER BY PayeeName COLLATE nocase;");    
        $resultarray = array();
        if($results !== false)
            {$resultarray = $results->fetchAll(PDO::FETCH_COLUMN, 0);}
        
        $db = null;
        return $resultarray;
    }


    //Select one payee
   public static function payee_select_one ($PayeeName)
    {
        $const_dbpath = costant::database_path();
        $db = new PDO("sqlite:${const_dbpath}");
        
        $statement = $db-> prepare("SELECT * FROM Payee_List WHERE PayeeName = :TrPayeeName COLLATE nocase;");
                $statement->bindParam(":TrPayeeName",$PayeeName);
        $statement-> execute ();
        $resultarray = array();
        $resultarray = $statement->fetch(PDO::FETCH_ASSOC);
        if(!$resultarray) {$resultarray = array();}
        
        $db = null;
        return $resultarray;
    }
   
   
    // Insert single payee account
    public static function payee_insert_single ($Payee,$Category,$SubCategory)
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $statement = $db -> prepare("INSERT or IGNORE INTO Payee_list (PayeeName,DefCateg,DefSubCateg)
                                        VALUES (:PayeeName,:DefCateg,:DefSubCateg);");
            $statement->bindParam(":PayeeName",$Payee);
            $statement->bindParam(":DefCateg",$Category);
            $statement->bindParam(":DefSubCateg",$SubCategory);
            $statement-> execute ();
            
            $db = null;
        }
        
    // Insert all payee account
    public static function payee_insert_json ($PayeeJSON)
        {
            $payees_json_list = json_decode ($PayeeJSON,true);
            $PayeeList = $payees_json_list["Payees"];
            $const_dbpath = costant::database_path();
            
            $db = new PDO("sqlite:${const_dbpath}");
            $statement = $db -> prepare("INSERT or IGNORE INTO Payee_list (PayeeName,DefCateg,DefSubCateg)
                                        VALUES (:PayeeName,:DefCateg,:DefSubCateg);");
            $db->beginTransaction();
            $PayeeListSize = sizeof($PayeeList);
            for ($i = 0; $i < $PayeeListSize; $i++)
            {
                if ($PayeeList[$i]["PayeeName"] != "")
                {
                    $statement->bindParam(":PayeeName",$PayeeList[$i]["PayeeName"]);
                    $statement->bindParam(":DefCateg",$PayeeList[$i]["DefCateg"]);
                    $statement->bindParam(":DefSubCateg",$PayeeList[$i]["DefSubCateg"]);
                    $statement->execute ();
                }
            }
            
            $db->commit();
            $db = null;
        }
    
    
    //Delete all payee
    public static function payee_delete_all ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $db->exec   ("DELETE FROM Payee_list;");
            
            $db = null;
        }
        
        
    // Update single payee account
    public static function payee_update_single ($Payee,$Category,$SubCategory)
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $statement = $db -> prepare("UPDATE Payee_list SET DefCateg = :DefCateg, DefSubCateg = :DefSubCateg
                                        WHERE PayeeName = :PayeeName");
            $statement->bindParam(":PayeeName",$Payee);
            $statement->bindParam(":DefCateg",$Category);
            $statement->bindParam(":DefSubCateg",$SubCategory);
            $statement-> execute ();
            
            $db = null;
        }


    //Delete all categories
    public static function category_delete_all ()
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            
            $db->exec   ("DELETE FROM Category_list;");
            
            $db = null;
        }
    
    
    //Select all categories
    public static function category_select_all ()
    {
        $const_dbpath = costant::database_path();
        $db = new PDO("sqlite:${const_dbpath}");
        
        $results = $db -> query("SELECT * FROM Category_List ORDER BY CategoryName COLLATE nocase;");    
        $resultarray = array();
        if($results !== false)
            {$resultarray = $results->fetchall(PDO::FETCH_ASSOC);}
        
        $db = null;
        return $resultarray;
    }
   
   
    //Select distinct categories
    public static function category_select_distinct ()
    {
        $const_dbpath = costant::database_path();
        $db = new PDO("sqlite:${const_dbpath}");
        
        $results = $db -> query("SELECT DISTINCT CategoryName FROM Category_List ORDER BY CategoryName COLLATE nocase;");    
        $resultarray = array();
        if($results !== false)
            {$resultarray = $results->fetchall(PDO::FETCH_COLUMN, 0);}
        
        $db = null;
        return $resultarray;
    }
    
    
    //Select all subcategory
    public static function subactegory_select_all ($Category)
    {
        $const_dbpath = costant::database_path();
        $db = new PDO("sqlite:${const_dbpath}");
        
        $statement = $db-> prepare("SELECT SubCategoryName FROM Category_List WHERE CategoryName = :CategName; COLLATE nocase ");
                $statement->bindParam(":CategName",$Category);
        $statement-> execute ();
        $resultarray = array();
        if($statement !== false)
            {$resultarray = $statement->fetchall(PDO::FETCH_ASSOC);}
        
        $db = null;
        return $resultarray;
    }
   
   
    /**
     *  Insert all categories
     *
     *  @param String           list of categories and subcategories
     *
     *  return void
     */
    public static function category_insert_json (String $CategoryJSON) : void
        {
            $categories_json_list = json_decode ($CategoryJSON,true);
            $CategoryList = $categories_json_list['Categories'];
            $const_dbpath = costant::database_path();
            
            $db = new PDO("sqlite:${const_dbpath}");
            $statement = $db -> prepare('INSERT or IGNORE INTO Category_list (CategoryName,SubCategoryName)
                                        VALUES (:CategoryName,:SubCategoryName);');
            $db->beginTransaction();
            $CategoryListSize = sizeof($CategoryList);
            /**
             *  keep list of inserted categories
             */
            $a_categories_done = [];
            for ($i = 0; $i < $CategoryListSize; $i++)
            {
                $s_category = $CategoryList[$i]['CategoryName'];
                if ($s_category != '')
                {
                    /**
                     *  if category is inserted for the first time
                     *  then also insert it without a subcategory
                     *  so later can select only parent category
                     */
                    $s_subcategory = 'None';
                    if (!in_array($s_category, $a_categories_done))
                    {
                        $statement->bindParam(':CategoryName',$s_category);
                        $statement->bindParam(':SubCategoryName',$s_subcategory);
                        $statement->execute();
                        $a_categories_done[] = $s_category;
                    }

                    $s_subcategory = $CategoryList[$i]['SubCategoryName'];
                    if ($s_subcategory != '' && $s_subcategory != 'None' && !is_null($s_subcategory))
                    {
                        $statement->bindParam(':CategoryName',$s_category);
                        $statement->bindParam(':SubCategoryName',$s_subcategory);
                        $statement->execute();
                    }
                }
            }
            
            $db->commit();
            $db = null;
        }
    
    
    // Insert one category
    public static function category_insert_single ($Category,$SubCategory)
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");

            $statement = $db -> prepare("INSERT or IGNORE INTO Category_List (CategoryName, SubCategoryName)
                                        VALUES (:CategoryName, :SubCategoryName);");
            $statement->bindParam(":CategoryName",$Category);
            $statement->bindParam(":SubCategoryName",$SubCategory);
            $statement-> execute ();
            
            $db = null;
        }
}



#########################
###     Security      ###
#########################
class security
{
    public static function redirect_if_not_loggedin ()
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
                        {header("Location: index.php");}
                }
                else
                {header("Location: index.php");}
            }
        }
        
        
    public static function generate_guid()
        {
            if (function_exists('com_create_guid'))
                {return com_create_guid();}
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
    public static function upgrade_db ()
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
                            case "0.9.7":
                                db_upgrade::upgrade_version("0.9.8");
                                break;
                            case "0.9.8":
                                db_upgrade::to_0_9_9();
                                break;
                            case "0.9.9":
                                db_upgrade::upgrade_version("1.0.0");
                                break;
                            case "1.0.0":
                                db_upgrade::upgrade_version("1.0.1");
                                break;
                            case "1.0.1":
                                db_upgrade::upgrade_version("1.0.2");
                                break;
							case "1.0.2":
                                db_upgrade::upgrade_version("1.0.3");
                                break;
                            case "1.0.3":
                                db_upgrade::upgrade_version("1.0.4");
                                break;
                            case $app_version;
                                break;
                            default:
                                various::send_alert_and_redirect("Database version not compliant: DB Version = ".db_function::db_version()." - APP Version = ${app_version}","error.php");
                                break 2;
                        }
                }
            if ($start_db_version !== $app_version && db_function::db_version() == $app_version)
                {return "update_done";}
            else
                {return "update_not_need";}
        }
        
        
    public static function to_0_9_3 ()
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
                    "defaultaccountname"    => costant::transaction_default_account()
                );
            various::update_configuration_file($parameterarray);
        }
    
    
    public static function to_0_9_7 ()
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
        
        
    public static function to_0_9_9 ()
        {
            $parameterarray = array
                (
                    "disable_authentication"=> costant::disable_authentication() ? "True" : "False",
                    "user_username"         => costant::login_username(),
                    "user_password"         => costant::login_password(),
                    "disable_payee"         => costant::disable_payee() ? "True" : "False",
                    "disable_category"      => "False",
                    "defaultaccountname"    => costant::transaction_default_account(),
                    "desktop_guid"          => costant::desktop_guid()
                );
            various::update_configuration_file($parameterarray);
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            $db->exec   ("ALTER TABLE Payee_List ADD DefCateg TEXT");
            $db->exec   ("ALTER TABLE Payee_List ADD DefSubCateg TEXT");
            $db->exec   ("UPDATE Payee_List SET DefCateg = 'None', DefSubCateg = 'None'");
            $db->exec   ("UPDATE New_Transaction SET Category = 'None', SubCategory = 'None'");
            $db->exec   ("UPDATE Parameters SET Value = '0.9.9' WHERE Parameter = 'Version';");           
            $db = null;
        }


    public static function upgrade_version ($version)
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
    public static function send_alert_and_redirect ($AlertMessage, $AlertRedirect)
        {
            echo "<script src='res/app/functions-1.0.4.js' type='text/javascript'></script>";
            echo "<script language='javascript'>";
            if ($AlertRedirect <> "None")
                {echo "send_alert_and_redirect ('${AlertMessage}','${AlertRedirect}')";}
            echo "</script>";
        }
        
    public static function update_configuration_file ($ParameterArray)
        {
            $configfile="configuration_user.php"; 
            
            if (file_exists($configfile))
                {unlink($configfile);}
            
            $fileopen = fopen($configfile, 'w');
            fwrite($fileopen, "<?php"."\n");
            fwrite($fileopen, "#######################"."\n");
            fwrite($fileopen, "##    User Setting   ##"."\n");
            fwrite($fileopen, "#######################"."\n");
            fwrite($fileopen, "\n");
            
            foreach ($ParameterArray as $key => $value)
                {fwrite($fileopen, "\$${key} = \"${value}\";\n");}
                
            fwrite($fileopen, "?>");
            fclose($fileopen);
        }
        
    public static function debug_to_file ($Value)
        {
            ob_start();
            var_dump($Value);
            $data = ob_get_clean();
            $fp = fopen("debug.txt", "w");
            fwrite($fp, $data);
            fwrite($fp, "\n");
            fwrite($fp, "///////////////////////////////////////////");
            fwrite($fp, "\n");
            fclose($fp);
        }
}



##########################
#  Attachments function  #
##########################
class attachments
{
    public static function get_attachments_filename_array($TrID,$bIncludeZero=false)
        {
            $i=0;
            $AttachmentsArray = array();

            if ($handle = opendir(costant::attachments_folder()))
                {
                    while (false !== ($entry = readdir($handle)))
                    {
                        if
                        (
                            (strpos($entry,"Transaction_".$TrID) == 0 && strpos($entry,"Transaction_".$TrID) !== false)
                            ||
                            ($bIncludeZero && strpos($entry,"Transaction_0") == 0 && strpos($entry,"Transaction_0") !== false)
                        )
                            {
                            $AttachmentsArray[$i] = $entry;
                            $i++;
                            }
                    }
                    closedir($handle);
                }
            return $AttachmentsArray;
        }
  
    public static function get_number_of_attachments($TrID)
        {
            $LastAttachNum = 0;
            if ($handle = opendir(costant::attachments_folder()))
                {
                    while (false !== ($entry = readdir($handle)))
                    {
                        if (strpos($entry,"Transaction_".$TrID) == 0 && strpos($entry,"Transaction_".$TrID) !== false)
                        {
                            $AttachNumb = substr($entry,strpos($entry,"Attach")+6,strpos($entry,".")-(strpos($entry,"Attach")+6));
                            if ($AttachNumb > $LastAttachNum)
                                $LastAttachNum = $AttachNumb;
                        }
                    }
                    closedir($handle);
                }
            return $LastAttachNum;
        }
    
    public static function delete_zero()
        {
            if ($handle = opendir(costant::attachments_folder()))
                {
                    while (false !== ($entry = readdir($handle)))
                    {
                        if (strpos($entry,"Transaction_0") == 0 && strpos($entry,"Transaction_0") !== false)
                        {
                            unlink(costant::attachments_folder()."/".$entry);
                        }
                    }
                    closedir($handle);
                }
            return true;
        }
        
    public static function rename_zero($TrID)
        {
            if ($handle = opendir(costant::attachments_folder()))
                {
                    while (false !== ($entry = readdir($handle)))
                    {
                        if (strpos($entry,"Transaction_0") == 0 && strpos($entry,"Transaction_0") !== false)
                        {
                            $NewFileName = str_replace("Transaction_0","Transaction_".$TrID,$entry);
                            rename(costant::attachments_folder()."/".$entry,costant::attachments_folder()."/".$NewFileName);
                        }
                    }
                    closedir($handle);
                }
            return true;
        }
    
    public static function delete_group($TrID_Array)
        {
            $N = count($TrID_Array);
            if ($handle = opendir(costant::attachments_folder()))
            {
                while (false !== ($entry = readdir($handle)))
                {
                    for($i=0; $i < $N; $i++)
                    {
                        $TrID = $TrID_Array[$i];
                        if (strpos($entry,"Transaction_".$TrID) == 0 && strpos($entry,"Transaction_".$TrID) !== false)
                        {
                            unlink(costant::attachments_folder()."/".$entry);
                        }
                    }
                }
                closedir($handle);
            }
            return true;
        }
    public static function delete_attachment_by_name($FileName)
        {
            $FullPath = costant::attachments_folder()."/".$FileName;
            if (!empty($FileName) && file_exists($FullPath))
                unlink($FullPath);
        }
}    


   
##########################
### Constant  function ###
##########################
class costant
    {
        public static function login_username ()
            {
                global $user_username;
                return $user_username;
            }
        
        public static function login_password ()
            {
                global $user_password;
                return $user_password;
            }
        
        public static function transaction_default_account ()
            {
                global $defaultaccountname;
                return $defaultaccountname;
            }
            
        public static function transaction_default_status ()
            {
                global $tr_default_status;
                return $tr_default_status;
            }
            
        public static function transaction_default_type ()
            {
                global $tr_default_type;
                return $tr_default_type;
            }
        public static function attachments_folder ()
            {
                global $attachments_folder;
                return $attachments_folder;
            }
        public static function database_path ()
            {
                global $dbpath;
                return $dbpath;
            }
             
        public static function desktop_guid ()
            {
                global $desktop_guid;
                return $desktop_guid;
            }
            
        public static function app_version ()
            {
                global $app_version;
                return $app_version;
            }
            
        public static function api_version ()
            {
                global $api_version;
                return $api_version;
            }
        
        public static function disable_authentication ()
            {
                global $disable_authentication;
                if ($disable_authentication == "True")
                    {return True;}
                else
                    {return False;}
            }
        
        public static function disable_payee ()
            {
                global $disable_payee;
                if ($disable_payee == "True")
                    {return True;}
                else
                    {return False;}
            }
        
        public static function disable_category ()
            {
                global $disable_category;
                if ($disable_category == "True")
                    {return True;}
                else
                    {return False;}
            }
            
        public static function current_page_url ()
            {
             $pageURL = 'http';
             if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
                $pageURL .= "s";
             $pageURL .= "://";
             if ($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443")
                {$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];}
             else
                {$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];}
             return $pageURL;
            }
    }
?>
