<?php
$b_restricted_auth      = false;

$s_page_title           = 'About';
$b_page_logo            = true;

include_once '_common.php';
include_once '_header.php';
?>

        <div class="container text_align_center">
            <img src="res/mmex.png" alt="Money Manager Ex Logo" height="150" width="150"/>
            <br />
            <h3><strong>Internal Error</strong></h3>
            <?php
	           echo "<h4> Version ".costant::app_version()."</h4>";
            ?>
            <br />
            <h4>Please contact developer Max Dmitriev</h4>
            <a href="https://github.com/moneymanagerex/web-money-manager-ex" target="_blank"><h4> on GitHub WebPage</h4></a>
            <br />
            <br />
        </div>
    
<?php
include_once '_footer.php';
