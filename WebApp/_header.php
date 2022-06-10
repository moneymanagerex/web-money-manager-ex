<?php
    $s_debug = '';
    // $s_debug = '?v=' . date("YmdHis");
    if (!isset($a_head_css_add)) { $s_head_css_add = ''; } else { $s_head_css_add = '        ' . implode ("\n" . '        ', $a_head_css_add) . "\n"; }
    if (!isset($a_head_js_add)) { $s_head_js_add = ''; } else { $s_head_js_add = '        ' . implode ("\n" . '        ', $a_head_js_add) . "\n"; }
    if ($b_debug) {
        $s_debug = '?'.microtime(true);
        $s_head_css_add = str_replace('.css', '.css' . $s_debug, $s_head_css_add);
        $s_head_js_add = str_replace('.js', '.js' . $s_debug, $s_head_js_add);
    }
?><!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1" />
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="MMEX">
        <meta name="apple-mobile-web-app-capable" content="yes" />

        <title><?php 
            echo various::getPageTitle($s_page_title)
        ?></title>

        <link rel="icon" href="res/favicon.ico" />
        <link rel="apple-touch-icon" href="res/mmex.png" />

        <link rel="stylesheet" type="text/css" href="res/bootstrap-3.3.6.min.css<?php echo $s_debug; ?>" />
        <link rel="stylesheet" type="text/css" href="res/bootstrap-theme-3.3.6.min.css<?php echo $s_debug; ?>" />
        <link rel="stylesheet" type="text/css" href="res/style_global-1.2.0.css<?php echo $s_debug; ?>" />

        <!-- optional css -->
<?php echo $s_head_css_add ?>
        <!-- /optional css -->

        <script src="res/jquery-2.1.4.min.js" type="text/javascript"></script>
        <script src="res/bootstrap-3.3.6.min.js" type="text/javascript"></script>
        <script src="res/app/functions-1.2.0.js<?php echo $s_debug; ?>" type="text/javascript"></script>

        <!-- optional js -->
<?php echo $s_head_js_add ?>
        <!-- /optional js -->

    </head>

    <body>
        <div class="container text_align_center app_page_title">
            <?php 
                if (isset($b_page_logo) && $b_page_logo) : 
            ?><h1><strong><?php echo costant::app_name() ?></strong></h1>
            <img src="res/mmex.png" alt="Money Manager Ex Logo" height="150" width="150"/><?php
                endif; 
            ?>

            <?php 
                $page_header_title = various::getPageName($s_page_title);
                if (!empty($page_header_title)) : 
            ?><h2><strong><?php echo $page_header_title; ?></strong></h2><?php
                endif; 
            ?>
        </div>

