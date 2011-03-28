<?php
/*
 * Creates assets from uploaded files
 * A admin user must be logged in to get this data.
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Billingham
 * @copyright  2009 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $$
 * @link       NA
 * @since      NA
*/

include_once('../../system/initialiseBackOffice.php');
include_once('model/Group.class.php');
include_once('model/Image.class.php');
include_once('model/Video.class.php');
include_once('model/Audio.class.php');
include_once('class/si/Safe.class.php');

// Check user is logged in before letting them do stuff
$adminUser = BackOffice::RetrieveAndCheckAjaxAdminUser($_SESSION);
$fname = Safe::post('filename', PARAM_FILE);
$ptitle = Safe::post('title');
$pdesc = Safe::post('description');

if(isset($fname)) $filename = basename($fname);

if(file_exists($adminUser->getInstitution()->getFullPath().DIR_FS_DATA_UPLOAD.$filename) && is_file($adminUser->getInstitution()->getFullPath().DIR_FS_DATA_UPLOAD.$filename)){

	// Create an object with iFile interface
	$tempUploadObject = Asset::CreateNew($filename, $adminUser);
	$extension = '.' . $tempUploadObject->getFileExtension();

	$fileBit = substr($filename, 0, strlen($filename) - (strlen($extension)) );
	$filePathFirstBit = $tempUploadObject->getSystemFolder() . $fileBit;

	// Find a unique name to save the file as
	$newFileNum = 0;
	$target_path = $filePathFirstBit . $newFileNum . $extension;
	while( file_exists($target_path) ) {
		$newFileNum ++;
		$target_path = $filePathFirstBit . $newFileNum . $extension;
		
		if($newFileNum > Uploader::NUM_MAX_FILE_DUPLICATE) {
			// stop endless loop
			break;
		}
	}

	// Save the file to file system and DB
	if(rename($adminUser->getInstitution()->getFullPath().DIR_FS_DATA_UPLOAD.$filename, $target_path)) {
		// Add it to asset table
		$tempUploadObject->setHref($fileBit . $newFileNum . $extension);
		if(isset($ptitle)) $tempUploadObject->setTitle($ptitle);
		if(isset($pdesc)) $tempUploadObject->setDescription($pdesc);
		$tempUploadObject->Save($adminUser);
		// add any chosen tags
		doTagging($tempUploadObject);
		// remove any cached thumbnail from the upload directory
		switch($tempUploadObject->getType()){
			case 'video':
				// videos are made into GIF thumbnails
				$thumbnail = $adminUser->getInstitution()->getFullPath().DIR_FS_DATA_UPLOAD.Image::SIZE_THUMBNAIL.DIRECTORY_SEPARATOR.$fileBit.'.gif';
				break;
			default:
				// images remain in their original format
				$thumbnail = $adminUser->getInstitution()->getFullPath().DIR_FS_DATA_UPLOAD.Image::SIZE_THUMBNAIL.DIRECTORY_SEPARATOR.$filename;
		}
		if(file_exists($thumbnail)) unlink($thumbnail);
		echo htmlspecialchars($filename).'####'.htmlspecialchars($tempUploadObject->getTitle());
	} else {
		echo "oops! rename/move failed: ".$adminUser->getInstitution()->getFullPath().DIR_FS_DATA_UPLOAD.$filename." => ".$target_path;
	}
	die();
}
//if(!file_exists(DIR_FS_DATA_UPLOAD.$filename)) echo "file does not exist: ".DIR_FS_DATA_UPLOAD.$filename;
//if(!is_file(DIR_FS_DATA_UPLOAD.$filename)) echo DIR_FS_DATA_UPLOAD.$filename." is not a regular file";

function doTagging($asset){
	global $adminUser;
    $ptags = Safe::post('tags');
	if(isset($ptags)){
		$tags = split(',', Safe::Input($ptags));
		foreach($tags as $tagname){
			$tag = Tag::CreateOrRetrieveByName(trim($tagname), $adminUser->getInstitution(), $adminUser);
			if(!is_null($tag)){
				$asset->addTag($tag);
			}
			else{
				echo "tag is null";
			}
		}
	}
}