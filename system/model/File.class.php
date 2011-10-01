<?php
/**
 * File Class
 * 
 * This class is used to handle temporary file during upload for
 * asset module in backoffice
 *

 *
 * @author     Elvir LEonard	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: Image.class.php 78 2008-07-31 15:47:15Z Elvir $
 * @link       NA
 * @since      NA
 */
include_once('Asset.class.php');

class File
{	

	
	/**
	 * print upload form
	 * @return 
	 * @param $scriptHandler Object
	 */
	private function getUploadForm($scriptHandler){
		if(isset($scriptHandler)){
			return '
				<form action="' . $scriptHandler . '" method="post" id="form" name="form" enctype="multipart/form-data" >
					<input type="file" name="uploadFile" id="uploadFile" size="10" /><br />
					<input type="submit" name="submit" id="submitBtn" value="Upload" />					
				</form>
			';	
		}
	}
	
	/**
	 * Handle preview uploaded file for asset in backoffice
	 * @return 
	 */
	private function previewUploadedFile(){
		$this->setFolder(DIR_WS_CACHE);
		if(($this->getHref(DIR_WS_CACHE))){
			echo $this->Html("preview");
			echo '<br />[ <a href="' . HTTP_SERVER . "upload-asset-form.html" . '" onclick"parent.setTempUploadedFile("");">Change</a> | <a href="' . HTTP_SERVER . "upload-asset-form.html" . '" onclick"parent.setTempUploadedFile("");">Cancel</a> ]';
			echo '<script type="text/javascript">parent.setTempUploadedFile("' . basename($this->getHref()) . '");</script>';			
		}
	}
}
?>
