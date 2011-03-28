<?php

/**
* Copy a file, or recursively copy a folder and its contents
*
* @author      Aidan Lister <aidan@php.net>
* @version     1.0.1
* @link        http://aidanlister.com/repos/v/function.copyr.php
* @param       string   $source    Source path
* @param       string   $dest      Destination path
* @return      bool     Returns TRUE on success, FALSE on failure
*/
function copyr($source, $dest)
{
	// Check for symlinks
	if (is_link($source)) {
		return symlink(readlink($source), $dest);
	}

	// Simple copy for a file
	if (is_file($source)) {
		return copy($source, $dest);
	}

	// Make destination directory
	if (!is_dir($dest)) {
		mkdir($dest);
	}

	// Loop through the folder
	$dir = dir($source);
	while (false !== $entry = $dir->read()) {
		// Skip pointers
		if ($entry == '.' || $entry == '..') {
			continue;
		}

		// Deep copy directories
		copyr("$source/$entry", "$dest/$entry");
	}

	// Clean up
	$dir->close();
	return true;
}


/**
 * Function for sending an email
 * @dependency: 	system/class/PHPMailer.php
 * @return: true/false
 */
function sendEmail($to_name, $to_email_address, $email_subject, $email_text_html, $email_text_plain, $from_email_name, $from_email_address, $attachments_array='')
{
	global $mail;  
	if( !isset( $mail ) ){
		$mail=new PHPMailer;
	}
	$mail->From     = $from_email_address;
	$mail->FromName = $from_email_name;
	//$mail->isSMTP();
	//$mail->Host     = "localhost";
	//$mail->SMTPKeepAlive=true;
	//$mail->Mailer   = "smtp";
	
	$mail->Subject 	= $email_subject;
	$mail->Body    	= ($email_text_html!="") ? stripslashes($email_text_html): stripslashes($email_text_plain);
	$mail->AltBody 	= stripslashes($email_text_plain);
	$mail->IsHTML(true);
	$mail->AddAddress($to_email_address, $to_name);
	if($attachments_array!=""){
	foreach ($attachments_array as $attachment)
		$mail->AddAttachment($attachment, basename($attachment));
	}
	if(!$mail->Send())
		echo "There has been a mail error sending to " . $to_name . "<br>";
	
	// Clear all addresses and attachments for next loop
	$mail->ClearAddresses();
	$mail->ClearAttachments();
	//$mail->SmtpClose(); 	
}


function formatSize($size, $round = 0)
{
    $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $total = count($sizes);
    for ($i=0; $size > 1024 && $i < $total; $i++) $size /= 1024;
    return round($size,$round).$sizes[$i];
}

function getRelevantFolder($filename, $type="WS")
{
	$type=strtoupper($type);
	$fileType=getAssetType($filename);
	if($fileType=="video"){
		if($type=="WS"){
			return DIR_WS_DATA_VIDEO;
		}else{
			return DIR_FS_DATA_VIDEO;
		}
	}else if($fileType=="audio"){
		if($type=="WS"){
			return DIR_WS_DATA_AUDIO;
		}else{
			return DIR_FS_DATA_AUDIO;
		}
	}else if($fileType=="image"){
		if($type=="WS"){
			return DIR_WS_DATA_IMAGE;
		}else{
			return DIR_FS_DATA_IMAGE;
		}
	}
}
function formatFileName($filename)
{
	return preg_replace("{[^\w\d]}", "", $filename);
}
?>