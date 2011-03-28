<?php
/**
 * User list JSON
 * Prints a list of users in JSON format.
 * A admin user must be logged in to get this data.
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
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
if(isset($_GET['user_id'])) {
	$assets = Asset::RetrieveUsersAssets(new User($_GET['user_id']));
}
// Get assets for a specific group
elseif(isset($_GET['group_id'])) {
	$assets = Asset::RetrieveGroupAssets(new Group($_GET['group_id']));
}
elseif(isset($_GET['filter'])) {
	switch($_GET['filter']) {
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
			Logger::Write("Bad filter name '{$_GET['filter']}'", Logger::TYPE_ERROR, $adminUser);
	}
}
elseif(isset($_GET['tag'])) {
	if(!isset($fromInstitution))$fromInstitution = $adminUser->getInstitution();

	$tag = Tag::RetrieveByName($_GET['tag'], $fromInstitution);
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