<?php

$b_restricted_auth  = true;
$s_page_title       = 'Show Transaction';
$a_head_js_add[]      = '<script src="res/app/base-1.0.4.js" type="text/javascript"></script>';
$a_head_js_add[]      = '<script src="res/app/show-1.1.0.js" type="text/javascript"></script>';
include_once '_common.php';
include_once '_header.php';

function drawRecordRow(Array $a_transaction, String $s_date) : void
{
    if ($s_date != $a_transaction['Date'])
    {
        $TrDateShow = $a_transaction['Date'];
        echo '<tr>';
        design::table_cell($TrDateShow,'td_size_100 text_align_center', 'colspan="7"');
        echo '</tr>';
    }

    //TRANSACTION ID
    $lineid = $a_transaction['ID'];

    echo '<tr>';

        //DELETE
        echo '<td class="text_align_center">';
            echo '<input class="do-delete" type="checkbox" name="TrDelete[]" value="' . $lineid . '" />';
            echo '&nbsp;<span class="glyphicon glyphicon-edit do-edit TrModify" tr_id="' . $lineid . '"></span>';
            echo '&nbsp;<span class="glyphicon glyphicon-duplicate do-duplicate TrDuplicate" tr_id="' . $lineid . '"></span>';
        echo '</td>';

        //CONDITION
        switch ($a_transaction['Status'])
        {
            case '': $TrStatusShow = ''; break;
            case 'R': $TrStatusShow = 'ok-sign'; break;
            case 'V': $TrStatusShow = 'remove-sign'; break;
            case 'F': $TrStatusShow = 'question-sign'; break;
            case 'D': $TrStatusShow = 'duplicate'; break;
            default: $TrStatusShow = $a_transaction['Status'];
        }

        //TYPE
        switch ($TrTypeShow = $a_transaction['Type'])
        {
            case 'Withdrawal': $TrTypeShowFormatted = 'minus'; break;
            case 'Deposit': $TrTypeShowFormatted = 'plus'; break;
            case 'Transfer': $TrTypeShowFormatted = 'transfer'; break;
            default: $TrStatusShow = $a_transaction['Type'];
        }

        design::table_cell(
            '<span class="glyphicon glyphicon-' . $TrTypeShowFormatted . '"></span>&nbsp;'.
            '<span class="glyphicon glyphicon-' . $TrStatusShow . '"></span>'
            , '');

        //AMOUNT
        $TrAmountShow = number_format($a_transaction['Amount'],2,',','');
        design::table_cell($TrAmountShow,'text_align_right td_size_5');

        //NOTES
        $TrNotesShow = $a_transaction['Notes'];
        $NotesHTMLCode = '';
        if ($TrNotesShow != '' && $TrNotesShow != 'None')
            $NotesHTMLCode .= '<span class="glyphicon glyphicon-comment" data-toggle="tooltip" title="' . $TrNotesShow . '" id="tooltip_notes_' . $lineid . '"></span> ';
        if (attachments::get_number_of_attachments($lineid) > 0)
            $NotesHTMLCode .= '<span class="glyphicon glyphicon-paperclip"></span>';
        design::table_cell($NotesHTMLCode,'text_align_center');

        //ACCOUNT
        $TrAccountShow = $a_transaction['Account'];
        $TrToAccountShow = $a_transaction['ToAccount'];
        if ($TrTypeShow == 'Transfer')
        {
            design::table_cell('<span data-toggle="tooltip" title="Transfer to: ' . $TrToAccountShow . '" id="tooltip_account_' . $lineid .'">' . $TrAccountShow . '</span>','');
        }
        else
            {design::table_cell($TrAccountShow,'');}

        //PAYEE
        $TrPayeeShow = $a_transaction['Payee'];
        if (costant::disable_payee() == False)
            {design::table_cell($TrPayeeShow,'transaction-extra-columns');}

        //CATEGORY
        $TrCategoryShow = $a_transaction['Category'];
        $TrSubCategoryShow = $a_transaction['SubCategory'];
        if (costant::disable_category() == False && $TrSubCategoryShow != 'None' && !empty($TrSubCategoryShow))
        {
            design::table_cell('<span data-toggle="tooltip" title="Subcategory: ' . $TrSubCategoryShow . '" id="tooltip_category_' . $lineid . '">' . $TrCategoryShow . '&nbsp;<span class="glyphicon glyphicon-equalizer"></span></span></span>','');
        }
        else if (costant::disable_category() == False)
                {design::table_cell($TrCategoryShow,'');}

    echo '</tr>';
}

$recordmaxid = db_function::transaction_select_maxid();
if ($recordmaxid > 0 )
    {
        $resultarray = db_function::transaction_select_all_order_by_date('DESC');
        echo '<div class="container">';
            echo '<h3 class="text_align_center">' . (count($resultarray) == 0 ? 'No' : 'Current'). ' pending transaction' . (count($resultarray) != 1 ? 's' : ''). '</h3>';
            echo '<br />';
            echo '<div class="table-responsive">';

                echo '<a href="landing.php" class="btn btn-lg btn-success btn-block">Return to menu</a>';
                echo '<br />';

                echo '<form id="Show_Function" class="form-show-function" method="post" action="show_function.php">';

                echo '<input class="btn-edit" type="hidden" id="btn_action" name="btn_action" value="" />';
                /**
                 *  edit selected transaction
                 */
                echo '<input class="do-edit" type="hidden" id="TrEdit" name="TrEdit[]" value="" />';

                /**
                 *  delete button
                 */
                echo '<button type="submit" id="TrDelete" name="btn_action" value="Delete" class="btn btn-lg btn-danger btn-block">Delete all selected</button>';

                /**
                 *  new button
                 */
                echo '<input type="button" class="btn btn-lg btn-success btn-block" id="btn_new" value="New transaction" onclick='."'top.location.href = ".'"new_transaction.php"'."' />";
                echo '<br />';

                echo '<table class="table table-hover table-condensed">';
                #echo '<table class = "table table-hover table-condensed table-bordered">'; //TABLE BORDERED FOR DEBUG
                    echo '<thead>';
                        echo '<tr>';
                            echo "<th class='text_align_center'><span class='glyphicon glyphicon-trash'></span> <span class='transaction-extra-columns'>Delete</span></th>";
                            echo "<th class=''><span class='glyphicon glyphicon-info-sign'></span> <span class='transaction-extra-columns'>Type</span></th>";
                            echo '<th class="text_align_right">Amount</th>';
                            echo '<th class="text_align_center">Notes</th>';
                            echo '<th>Account</th>';
                            if (costant::disable_payee() == False)
                                {echo "<th class='transaction-extra-columns'>Payee</th>";}
                            if (costant::disable_category() == False)
                                {echo '<th>Category</th>';}
                        echo '</tr>';
                    echo '</thead>';
                    
                    echo '<tbody>';
                    $s_date = '';
                    for ($i = 0; $i <= $recordmaxid; $i++)
                    {
                        if (isset($resultarray[$i]['ID']))
                        {
                            drawRecordRow($resultarray[$i], $s_date);
                            $s_date = $resultarray[$i]['Date'];
                        }
                    }
                    echo '</tbody>';
                echo '</table>';
                echo '</form>';
            echo '</div>';
        echo '</div>';
    }
else
    {
        echo '<div class="container">';
            echo '<br />';
            echo '<br />';
            echo '<h3 class="text_align_center">No pending transactions</h3>';
            echo '<br />';
            echo '<br />';
			echo '<a href="new_transaction.php" class="btn btn-lg btn-success btn-block">Add new</a>';
            echo '<br />';
            echo '<a href="landing.php" class="btn btn-lg btn-success btn-block">Return to menu</a>';
            echo '<br />';
            echo '<br />';
        echo '</div>'."\n";
    }

include_once '_footer.php';
