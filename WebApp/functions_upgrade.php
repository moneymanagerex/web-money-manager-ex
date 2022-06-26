<?php

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
                            case '1.2.0':
                                db_upgrade::upgrade_version('1.2.1');
                                break;

                            case '1.1.0':
                                db_upgrade::upgrade_version('1.2.0');
                                break;
                            case '1.0.4':
                                db_upgrade::upgrade_version('1.1.0');
                                break;
                            case '0.9.2':
                                db_upgrade::to_0_9_3();
                                break;
                            case '0.9.3':
                                db_upgrade::upgrade_version('0.9.4');
                                break;
                            case '0.9.4':
                                db_upgrade::upgrade_version('0.9.5');
                                break;
                            case '0.9.5':
                                db_upgrade::upgrade_version('0.9.6');
                                break;
                            case '0.9.6':
                                db_upgrade::to_0_9_7();
                                break;
                            case '0.9.7':
                                db_upgrade::upgrade_version('0.9.8');
                                break;
                            case '0.9.8':
                                db_upgrade::to_0_9_9();
                                break;
                            case '0.9.9':
                                db_upgrade::upgrade_version('1.0.0');
                                break;
                            case '1.0.0':
                                db_upgrade::upgrade_version('1.0.1');
                                break;
                            case '1.0.1':
                                db_upgrade::upgrade_version('1.0.2');
                                break;
                            case '1.0.2':
                                db_upgrade::upgrade_version('1.0.3');
                                break;
                            case '1.0.3':
                                db_upgrade::upgrade_version('1.0.4');
                                break;
                            case $app_version;
                                break;
                            default:
                                various::send_alert_and_redirect('Database version is not compatible: DB Version = '.db_function::db_version()." - APP Version = ${app_version}",'error.php');
                                break 2;
                        }
                }
            if ($start_db_version !== $app_version && db_function::db_version() == $app_version)
                {return 'update_done';}
            else
                {return 'update_not_need';}
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
