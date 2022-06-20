<?php


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
    public static function transaction_select_all_order_by_date (String $s_direction = 'DESC')
        {
            $const_dbpath = costant::database_path();
            $db = new PDO("sqlite:${const_dbpath}");
            $db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);

            $results = $db -> query("SELECT * FROM New_Transaction ORDER BY Date $s_direction, id $s_direction;");
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
    public static function subcategory_select_all ($Category)
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
