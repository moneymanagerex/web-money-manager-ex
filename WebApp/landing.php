<?php
$b_restricted_auth      = true;
include_once '_common.php';

$s_page_title           = '';
$b_page_logo            = true;
include_once '_header.php';
?>

<?php
#require_once "functions.php";
#session_start();
#security::redirect_if_not_loggedin();
?>

        <div class="container text_align_center">
            <a href="new_transaction.php" class="btn btn-lg btn-success btn-block">New transaction</a>
            <br />
            <a href="show.php" class="btn btn-lg btn-success btn-block">Show transactions</a>
            <br />
            <a href="settings.php" class="btn btn-lg btn-success btn-block">Edit settings</a>
            <br />
            <a href="guide.php" class="btn btn-lg btn-success btn-block">Guide</a>
            <br />
            <a href="about.php" class="btn btn-lg btn-success btn-block">About</a>
            <br />
            <a href="logout.php" class="btn btn-lg btn-success btn-block">Logout</a>
            <br />
        </div>

<?php
include_once '_footer.php';
