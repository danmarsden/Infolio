<?php

// This file is part of In-Folio - http://blog.in-folio.org.uk/blog/
//
// In-Folio is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// In-Folio is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with In-Folio.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The PageBlock Class
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: Uploader.class.php 730 2009-07-28 20:14:24Z richard $
*/

include_once('class/Events/EventDispatcher.class.php');

/**
 * A class to deal with uploading files to the server from the user's browser
 * @return 
 */
class Uploader
{
	// Member variables
	private $m_allowedFileTypes;
	private $m_fileSizeLimit;
	private $m_title;

	const UPLOAD_DIR = 'data/asset/';
	const NUM_MAX_FILE_DUPLICATE = 1000;
	
	public function __construct($title = 'Upload a new file', $fileSizeLimit = 1000000, $allowedFileTypes = array())
	{
		$this->m_allowedFileTypes = $allowedFileTypes;
		$this->m_fileSizeLimit = $fileSizeLimit;
		$this->m_title = $title;

		//ini_set('upload_max_filesize', $fileSizeLimit);
		//ini_set('post_max_size', $fileSizeLimit);
	}

	/**
	 * Uploads a file to the server and either creates an asset or attachment
	 * @param <type> $fileUploadId
	 * @param User $user
	 * @param Page $page If provided this file will become an attachment
	 * @return int
	 */
	public function CopyUpload($fileUploadId, User $user, Page $page=null)
	{
		Debugger::debug('Starting CopyUpload', 'Uploader::CopyUpload');

		// Get the temporary file
		if( isset($_FILES[$fileUploadId]) ) {
			$tempFile = $_FILES[$fileUploadId];
		}
		else {
			Logger::Write("User upload error 'File not uploaded to server'", Logger::TYPE_ERROR, $user);
			throw new Exception('TechDis: File not uploaded to server');
		}
		
		switch($tempFile['error']) {
			// File okay
			case UPLOAD_ERR_OK:
				$newId = $this->doCopyUpload($tempFile, $user, $page);
				Logger::Write("User uploaded file '{$_FILES[$fileUploadId]['name']}'", Logger::TYPE_INFO, $user);
				return $newId;
				break;
			// Error cases
			case UPLOAD_ERR_INI_SIZE:
				$errMessage = "Can't upload file: Too big [{$tempFile['size']}] (system limit)";
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$errMessage = "Can't upload file: Too big [{$tempFile['size']}] (form limit)";
				break;
			case UPLOAD_ERR_PARTIAL:
				$errMessage = "Didn't finish uploading file. Try again.";
				break;
			case UPLOAD_ERR_NO_FILE:
				$errMessage = "Didn't upload file. Try again.";
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$errMessage = "Can't upload file. Something is wrong.";
				Logger::Write('Upload error: No tmp directory', Logger::TYPE_ERROR, $user);
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$errMessage = "Can't upload file. Something is wrong.";
				Logger::Write('Upload error: Can not write file', Logger::TYPE_ERROR, $user);
				break;
			case UPLOAD_ERR_EXTENSION:
				$errMessage = "Can't upload this type of file";
				break;
			default:
				$errMessage = 'Error: ' . $tempFile['error'];
				break;
		}
		
		// TODo: check $this->goodFileType($tempFile);
		
		// Check for errors
		if ( isset($errMessage) ) {
			Logger::Write("User upload error '{$errMessage}'", Logger::TYPE_ERROR, $user);
			throw new Exception($errMessage);
		}
	}
	
	public function HtmlUploadForm($fileUploadId, $actionPath, Page $page = null)
    {
        if ($page) {
            $returnurl = $page->PathWithQueryString();
            $pageid = $page->getId();
        } else {
            $returnurl = 'collection.php';
            $pageid = null;
        }
		$html = '<div id="html-uploader"><form id="upload-image-frm" enctype="multipart/form-data" action="' . $actionPath . '" method="post">' .
			'<h3><label for="txtFile">' .$this->m_title. '</label></h3>' .
			//'<input type="hidden" name="MAX_FILE_SIZE" value="' . $this->m_fileSizeLimit . '" />' .
			'<input id="txtFile" type="hidden" name="a" value="' .EventDispatcher::ACTION_UPLOAD. '" />' .
			'<div class="fileinputs"><input name="' . $fileUploadId . '" type="file" /></div> ' .
			'<input type="image" id="upload-btn" value="Upload" src="/_images/si/upload-btn.gif" />' .
            '</form></div>';

        /* SWFUpload form  */
        $html .= '<div id="swf-uploader" style="visibility: hidden;"><form id="upload-image-frm" action="/swfupload.php" method="post" enctype="multipart/form-data">
                    <h3><label for="txtFile">'.$this->m_title.'</label></h3>
                    <div class="fileinputs">
                    <div class="fileUploadButton">
                        <span id="spanButtonPlaceHolder"></span>
                        <div style="visibility: hidden;"><input id="btnCancel" type="button" value="Cancel All Uploads" onclick="swfu.cancelQueue();" disabled="disabled" style="margin-left: 2px; font-size: 8pt; height: 29px;" /></div>
                    </div>					
                    <div class="fieldset flash" id="fsUploadProgress">
                    </div>
                    </div>
                    <input type="hidden" id="pageid" name="pageid" value="'.$pageid.'" />
                    <input type="hidden" id="returnurl" name="returnurl" value="'.$returnurl.'" />
                </form></div>';

		return $html;
		
		

		
		//<input type="image" name="upload-btn"/>
	}

	/**
	 * Get the file extension of a filename
	 * @param <type> $filename
	 * @return <type>
	 */
	public static function FindFileExtension($filename)
	{
		$fileBits = explode(".", $filename);
		$extension = array_pop($fileBits);
		$extension = strtolower($extension);

		return $extension;
	}
	
	function doCopyUpload($file, User $user, Page $page=null)
	{
		// Create an object with iFile interface
		$fileName = basename( $file['name']);
		if (!isset($page)) {
			// This is an asset
			$tempUploadObject = Asset::CreateNew($fileName, $user);
		}
		else {
			// This is an attachment
			$tempUploadObject = Attachment::CreateNew($fileName, $page, $user);
		}
        //check that systemfolder exists
        $sysfolder = $tempUploadObject->getSystemFolder();
        if (!is_dir($sysfolder)) {
            mkdir($sysfolder, 0777, true);
        }

		$extension = '.' . $tempUploadObject->getFileExtension();
		
		$fileBit = substr($fileName, 0, strlen($fileName) - (strlen($extension)) );
		$filePathFirstBit = $sysfolder . $fileBit;

		Debugger::debug('Uploading *.'  . $extension . '=' . $tempUploadObject->getType(), 'Uploader::doCopyUpload');
		
		// Find a unique name to save the file as
		$newFileNum = 0;
		$target_path = $filePathFirstBit . $newFileNum . $extension;;
		while( file_exists($target_path) ) {
			$newFileNum ++;
			$target_path = $filePathFirstBit . $newFileNum . $extension;
			
			if($newFileNum > self::NUM_MAX_FILE_DUPLICATE) {
				// stop endless loop
				break;
			}
		}
		
		// Save the file to file system and DB
		$success = false;
		if (isset($file['infoliopath']) && copy($file['infoliopath'], $target_path)) {
            $success = true;
		} elseif(move_uploaded_file($file['tmp_name'], $target_path)) {
		    $success = true;
		} 
		if($success) {
		    // Set HREF
			$tempUploadObject->setHref($fileBit . $newFileNum . $extension);

			// Convert bitmaps to jpegs
			if($tempUploadObject->getFileExtension() == 'bmp') {
				$tempUploadObject->convertTo('jpg');
			}

			$tempUploadObject->Save($user);

			// Only assign to collection if able
			if(method_exists($tempUploadObject, 'assignToCollectionInDb')) {
				$tempUploadObject->assignToCollectionInDb($user);
			}
		}
		
		Debugger::debug("Saved: {$tempUploadObject->getHref()}, id: {$tempUploadObject->getId()}", "Uploader::doCopyUpload({$file})_1", Debugger::LEVEL_INFO);
		return $tempUploadObject->getId();
	}
}
