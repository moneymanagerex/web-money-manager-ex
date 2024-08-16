<?php
//TODO strict_types
#declare(strict_types=1);

require_once 'functions.php';

if ($b_restricted_auth)
{

    $const_username = costant::login_username();
    $const_password = costant::login_password();

    if (isset($const_username) AND isset($const_password))
    {
        session_start();
        security::redirect_if_not_loggedin();
    }
}

if (!isset($b_debug))
{
    $b_debug = false;
}