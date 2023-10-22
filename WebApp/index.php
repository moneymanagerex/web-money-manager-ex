<?php
require_once 'functions.php';

$error = db_function::db_create();
if ($error !== 'ok')
    {
        echo $error;
    }
else
    {
        $upgrade_result = db_upgrade::upgrade_db();
        if ($upgrade_result == 'update_done')
            {
                various::send_alert_and_redirect('Database aggiornato con successo alla versione '.costant::app_version(), 'index.php');
            }
    }
$username = null;
$password = null;

$const_username = costant::login_username();
$const_password = costant::login_password();
$const_disable_authentication = costant::disable_authentication();

if ($const_disable_authentication == 'True')
    {
        header('Location: landing.php');
    }
       
if ($const_disable_authentication !== 'True' && (!isset($const_username) OR !isset($const_password)))
    {
        header('Location: settings.php');
    }

if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if(!empty($_POST['Username']) && !empty($_POST['Password']))
        {
            $username = $_POST['Username'];
            $password = hash('sha512', $_POST['Password']);
            
            if($username == $const_username && $password == $const_password)
            {
                session_start();
                $user_browser = $_SERVER['HTTP_USER_AGENT'];
                $_SESSION['username'] = $username;
                $_SESSION['login_string'] = hash('sha512', $password . $user_browser);
                header('Location: landing.php');
                
            }
            else
            {
                header('Location: index.php');
            }
        
        }
        else
        {
            header('Location: index.php');
        }
    }
else
{
    $b_restricted_auth      = false;
    include_once '_common.php';

    $s_page_title           = '';
    $b_page_logo            = true;
    include_once '_header.php';
    ?>

        <div class="container text_align_center">
            <form id="login" method="post">
                <div class="form-group">
                    <label for="Username">Username</label>
                    <input id="Username" type="text" name="Username" class="form-control" placeholder="Inserisci lo Username" autofocus required />
                    <span class="help-block"></span>
                </div>
                <div class="form-group">
                    <label for="Password">Password</label>
                    <input id="Password" type="password" name="Password" class="form-control" placeholder="Inserisci la Password" required />
                    <span class="help-block"></span>
                </div>
                <br />
                <button type="submit" id="Login" name="Login" class="btn btn-lg btn-success btn-block" value = "Login">Accedi</button>
            </form>
        </div>
<?php
include_once '_footer.php';
}
