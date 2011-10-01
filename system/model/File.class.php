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
 * File Class
 * 
 * This class is used to handle temporary file during upload for
 * asset module in backoffice
 *
 * @author     Elvir LEonard	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: Image.class.php 78 2008-07-31 15:47:15Z Elvir $
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
