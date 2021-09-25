<?php
    $s_debug = '';
    if ($b_debug) { $s_debug = '?'.microtime(true); }
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1" />
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="MMEX">
    <meta name="apple-mobile-web-app-capable" content="yes" />

    <title><?php echo $s_page_title ?> | Money Manager EX</title>

    <link rel="icon" href="res/favicon.ico" />
    <link rel="apple-touch-icon" href="res/mmex.png" />

    <link rel="stylesheet" type="text/css" href="res/bootstrap-3.3.6.min.css<?php echo $s_debug; ?>" />
    <link rel="stylesheet" type="text/css" href="res/bootstrap-theme-3.3.6.min.css<?php echo $s_debug; ?>" />
    <link rel="stylesheet" type="text/css" href="res/style_global-1.1.0.css<?php echo $s_debug; ?>" />

    <script src="res/jquery-2.1.4.min.js" type="text/javascript"></script>
    <script src="res/bootstrap-3.3.6.min.js" type="text/javascript"></script>
    <script src="res/app/functions-1.1.0.js<?php echo $s_debug; ?>" type="text/javascript"></script>

</head>

<body>
