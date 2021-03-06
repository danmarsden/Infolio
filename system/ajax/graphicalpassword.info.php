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
 * Asset list JSON
 * Prints a list of a user's assets in JSON format.
 * The user must be logged in to get this data.
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: graphicalpassword.info.php 761 2009-08-13 10:13:23Z richard $
*/

include_once('../initialise.php');
include_once('model/User.class.php');
include_once('model/Image.class.php');

// Check user is logged in before letting them do stuff
$ignoreInstitutionUrl = true;
include_once('_includes/login.inc.php');

// Get assets for specific user
if(isset($studentUser)) {
	$coords = $studentUser->getFlashLoginCoords();
	$passwordPicture = $studentUser->getFlashLoginPhoto();

	$xml = '<password>';
	$passwordPicture->setPreviewSize(Image::SIZE_PHOTO_LOGIN);
	if( isset($passwordPicture) )  $xml .= $passwordPicture->toXml();
	$xml .= "<coords>\n";

	// Loop through all the items
	foreach($coords as $item) {
		$xml .= "<coord>";

		// Loop through an items properties and produce its XML
		foreach($item as $itemKey=>$itemValue) {
			$xml .= "<{$itemKey}>{$itemValue}</{$itemKey}>";
		}

		$xml .= "</coord>\n";
	}
	$xml .= '</coords></password>';
	print $xml;
}
else {
	// Get all users
	print 'User not logged in';
}