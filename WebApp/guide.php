<?php

$b_restricted_auth      = true;
$b_page_logo            = true;

include_once '_common.php';
include_once '_header.php';

$CurrentPage = str_replace('/guide.php','',costant::current_page_url());
?>

        <div class="container">
            <p>
                <ol>
                    <li>
                        <?php echo $lang["guide.step1"] ?>
                    </li>
                    <li>
                        <?php echo $lang["guide.step2"] ?>
                    </li>
                    <li>
                        <?php echo $lang["guide.step3"] ?>
                    </li>
                </ol>
            </p>
        </div>
        <div class="container text_align_center">
            <h4>
                <?php echo $lang["guide.webapp-url"] ?>
            </h4>
            <p>
                <strong>
                    <?php echo $CurrentPage ?>
                </strong>
            </p>
            <br />
            <h4>
                <?php echo $lang["guide.desktop-guid"] ?>
            </h4>
            <p>
                <strong>
                    <?php echo costant::desktop_guid() ?>
                </strong>
            </p>
        </div>

<?php
include_once '_footer.php';
