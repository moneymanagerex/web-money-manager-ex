<?php

##########################
#  Attachments function  #
##########################
class attachments
{
    public static function get_attachments_filename_array($TrID,$bIncludeZero=false)
        {
            $i=0;
            $AttachmentsArray = array();

            if ($handle = opendir(costant::attachments_folder()))
                {
                    while (false !== ($entry = readdir($handle)))
                    {
                        if
                        (
                            (strpos($entry,"Transaction_".$TrID) == 0 && strpos($entry,"Transaction_".$TrID) !== false)
                            ||
                            ($bIncludeZero && strpos($entry,"Transaction_0") == 0 && strpos($entry,"Transaction_0") !== false)
                        )
                            {
                            $AttachmentsArray[$i] = $entry;
                            $i++;
                            }
                    }
                    closedir($handle);
                }
            return $AttachmentsArray;
        }

    public static function get_number_of_attachments($TrID)
        {
            $LastAttachNum = 0;
            if ($handle = opendir(costant::attachments_folder()))
                {
                    while (false !== ($entry = readdir($handle)))
                    {
                        if (strpos($entry,"Transaction_".$TrID) == 0 && strpos($entry,"Transaction_".$TrID) !== false)
                        {
                            $AttachNumb = substr($entry,strpos($entry,"Attach")+6,strpos($entry,".")-(strpos($entry,"Attach")+6));
                            if ($AttachNumb > $LastAttachNum)
                                $LastAttachNum = $AttachNumb;
                        }
                    }
                    closedir($handle);
                }
            return $LastAttachNum;
        }

    public static function delete_zero()
        {
            if ($handle = opendir(costant::attachments_folder()))
                {
                    while (false !== ($entry = readdir($handle)))
                    {
                        if (strpos($entry,"Transaction_0") == 0 && strpos($entry,"Transaction_0") !== false)
                        {
                            unlink(costant::attachments_folder()."/".$entry);
                        }
                    }
                    closedir($handle);
                }
            return true;
        }

    public static function rename_zero($TrID)
        {
            if ($handle = opendir(costant::attachments_folder()))
                {
                    while (false !== ($entry = readdir($handle)))
                    {
                        if (strpos($entry,"Transaction_0") == 0 && strpos($entry,"Transaction_0") !== false)
                        {
                            $NewFileName = str_replace("Transaction_0","Transaction_".$TrID,$entry);
                            rename(costant::attachments_folder()."/".$entry,costant::attachments_folder()."/".$NewFileName);
                        }
                    }
                    closedir($handle);
                }
            return true;
        }

    public static function delete_group($TrID_Array)
        {
            $N = count($TrID_Array);
            if ($handle = opendir(costant::attachments_folder()))
            {
                while (false !== ($entry = readdir($handle)))
                {
                    for($i=0; $i < $N; $i++)
                    {
                        $TrID = $TrID_Array[$i];
                        if (strpos($entry,"Transaction_".$TrID) == 0 && strpos($entry,"Transaction_".$TrID) !== false)
                        {
                            unlink(costant::attachments_folder()."/".$entry);
                        }
                    }
                }
                closedir($handle);
            }
            return true;
        }
    public static function delete_attachment_by_name($FileName)
        {
            $FullPath = costant::attachments_folder()."/".$FileName;
            if (!empty($FileName) && file_exists($FullPath))
                unlink($FullPath);
        }
}
