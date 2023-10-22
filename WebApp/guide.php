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
                        Copia le informazioni qui sotto e incollale nell'applicazione desktop.
                    </li>
                    <li>
                        Riapri l'applicazione desktop per sincronizzare i conti, i beneficiari, etc. sulla WebApp.
                    </li>
                    <li>
                        Inizia ad usare la WebApp.
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
        </div>

<?php
include_once '_footer.php';
