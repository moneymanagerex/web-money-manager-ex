<?php
require_once "functions.php";
session_start();
security::redirect_if_not_loggedin();

if (isset($_GET["DeleteAttach"]))
    {
        attachments::delete_attachment_by_name($_GET["DeleteAttach"]);
    }

if (isset($_FILES['UploadedAttachments']))
    {
        $TrNumber = 0;
        $FileName = $_FILES['UploadedAttachments']['name'];
        $FileExtension = substr($FileName,strpos($FileName,".")+1,strlen($FileName));
        $NewFileName = "Transaction_".$TrNumber."_Attach".(attachments::get_number_of_attachments($TrNumber)+1).".".$FileExtension;
        move_uploaded_file ($_FILES['UploadedAttachments']['tmp_name'], "attachments/".$NewFileName);
        echo $NewFileName;
    }
    
if (isset($_GET["AttachmentsTable"]))
    {
        $Attachments = attachments::get_attachments_filename_array($_GET["AttachmentsTable"]);
        echo "<table class = 'table'>";
            echo "<tbody>";
                for ($i = 0; $i < sizeof($Attachments); $i++)
                {
                    echo "<tr>";
                        $File = $Attachments[$i];
                        design::table_cell(substr($File,strpos($File,"Attach"),strlen($File)),"");
                        design::table_cell("<a href='#' onclick='attachment_delete(\"${File}\");return false;'><span class='glyphicon glyphicon-download-alt'> </span> Open</a>","text_align_right");
                        design::table_cell("<a href='#' onclick='attachment_delete(\"${File}\");return false;'><span class='glyphicon glyphicon-remove'> </span> Delete</a>","text_align_right");
                    echo "</tr>";
                }
            echo "</tbody>";
        echo "</table>";
    }
?>