<?php
/**
 * Asset list JSON
 * Prints a list of a user's assets in JSON/XL format.
 * The user must be logged in to get this data.
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: assets.list.php 761 2009-08-13 10:13:23Z richard $
 * @link       NA
 * @since      NA
*/

include_once('../initialise.php');
include_once('model/User.class.php');
include_once('model/Image.class.php');
include_once('model/Video.class.php');
include_once('model/Group.class.php');

// Check user is logged in before letting them do stuff
$ignoreInstitutionUrl = true;
include_once('_includes/login.inc.php');

// Get assets for specific user
if(isset($studentUser)) {
	$assets = Asset::RetrieveUsersAssets($studentUser);
}
else {
	// Get all users
	print 'User not logged in';
}

// Print them in chosen format
$format = Safe::getWithDefault('format', null);
switch($format) {
	case 'xml':
		print Asset::createXmlString($assets, Image::SIZE_BOX);
		break;
	case 'json':
	default:
		print Asset::CreateJsonString($assets, Image::SIZE_THUMBNAIL);
		break;
}