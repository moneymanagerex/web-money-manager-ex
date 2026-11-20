<?php
######################################
##  System configuration constants  ##
##         don't touch              ##
######################################
$dbpath = getenv('WEBMMX_DB_FILE') ?: 'MMEX_New_Transaction.db';
$app_name = 'Money Manager EX';
$app_version = '1.2.3';
$api_version = '1.0.1';
$tr_default_status = '';
$tr_default_type = 'Withdrawal';
$attachments_folder = getenv('WEBMMX_ATTACHMENTS_FOLDER') ?: 'attachments';
