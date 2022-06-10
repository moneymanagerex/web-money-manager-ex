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
                        Copy the data below and paste them into your desktop application.
                    </li>
                    <li>
                        Re-open desktop application to synchronise bank account(s), etc. to the WebApp from Desktop.
                    </li>
                    <li>
                        Start using the WebApp version.
                    </li>
                </ol>
            </p>
        </div>
        <div class="container text_align_center">
            <h4>
                WebApp URL:
            </h4>
            <p>
                <strong>
                    <?php echo $CurrentPage ?>
                </strong>
            </p>
            <br />
            <h4>
                Desktop GUID:
            </h4>
            <p>
                <strong>
                    <?php echo costant::desktop_guid() ?>
                </strong>
            </p>

            <?php
                include_once '_btn_back.php';
            ?>
        </div>

<?php
include_once '_footer.php';
