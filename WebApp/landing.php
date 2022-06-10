<?php
$b_restricted_auth      = true;
include_once '_common.php';

$s_page_title           = '';
$b_page_logo            = true;
include_once '_header.php';

$pages_list = various::getPagesList();

?>

        <div class="container text_align_center">
<?php
foreach ($pages_list AS $page_url => $page_name):
?>
            <div class="div_btn">
                <a href="<?php echo $page_url ?>.php" class="btn btn-lg btn-success btn-block"><?php
                echo $page_name
                ?></a>
            </div>
<?php
endforeach;

include_once '_btn_logout.php';

?>
        </div>
<?php

include_once '_footer.php';
