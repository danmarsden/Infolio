<?php
/**
 * User list JSON
 * Prints a list of users in JSON format.
 * A admin user must be logged in to get this data.
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: assets.list.php 684 2009-07-08 11:00:01Z richard $
 * @link       NA
 * @since      NA
*/

include_once('../../system/initialiseBackOffice.php');
include_once('model/Image.class.php');
include_once('model/Video.class.php');
include_once('model/Group.class.php');

// Check user is logged in before letting them do stuff
$adminUser = BackOffice::RetrieveAndCheckAjaxAdminUser($_SESSION);

// Check if institution was specified
$fromInstitution = Safe::getWithDefault('inst', null);
if(isset($fromInstitution)) $fromInstitution = new Institution($fromInstitution);

// Get assets for specific user
$uid = Safe::get('user_id');
$gid = Safe::get('group_id');
$gfil = Safe::get('filter');
$gtag  = Safe::get('tag');
if(isset($uid)) {
	$assets = Asset::RetrieveUsersAssets(new User($uid));
}
// Get assets for a specific group
elseif(isset($gid)) {
	$assets = Asset::RetrieveGroupAssets(new Group($gid));
}
elseif(isset($gfil)) {
	switch($gfil) {
		case 'recent':
			$assets = Asset::RetrieveRecent($adminUser, $fromInstitution);
			break;
		case 'unassigned':
			$assets = Asset::RetrieveUnassigned($adminUser, $fromInstitution);
			break;
		case 'mine':
			$assets = Asset::RetrieveCreatedByAdmin($adminUser, $fromInstitution);
			break;
		default:
			Logger::Write("Bad filter name '{$gfil}'", Logger::TYPE_ERROR, $adminUser);
	}
}
elseif(isset($gtag)) {
	if(!isset($fromInstitution))$fromInstitution = $adminUser->getInstitution();

	$tag = Tag::RetrieveByName($gtag, $fromInstitution);
	if(isset($tag)) {
		$assets = Asset::RetrieveByTag($tag);
	}
}
// Get all assets that admin user has right to see
else {
	// Get all users
	$assets = Asset::RetrieveAll($adminUser, $fromInstitution);
}

// Print them in chosen format
$format = Safe::getWithDefault('format', null);
switch($format) {
	case 'xml':
		print Asset::createXmlString($assets, Image::SIZE_PHOTO_LOGIN);
		break;
	case 'json':
	default:
		print Asset::CreateJsonString($assets, Image::SIZE_THUMBNAIL);
		break;
}