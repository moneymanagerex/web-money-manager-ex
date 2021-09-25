<?php
//TODO strict_types
#declare(strict_types=1);

require_once 'functions.php';

if ($b_restricted_auth)
{
    session_start();
    security::redirect_if_not_loggedin();
}

if (!isset($b_debug))
{
    $b_debug = false;
}
