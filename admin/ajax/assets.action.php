<?php
/*
 * Moves assets to a user/group's collection.
 * A admin user must be logged in to get this data.
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: assets.action.php 701 2009-07-16 12:55:16Z richard $
 * @link       NA
 * @since      NA
*/

include_once('../../system/initialiseBackOffice.php');
include_once('model/Group.class.php');
include_once('model/Image.class.php');
include_once('model/Video.class.php');
include_once('class/si/Safe.class.php');

// Check user is logged in before letting them do stuff
$adminUser = BackOffice::RetrieveAndCheckAjaxAdminUser($_SESSION);

// Get the info passed by AJAX request
$assetId = Safe::postWithDefault('id', null, PARAM_INT);
$assetIds = Safe::postWithDefault('items', null);
$isPublic = Safe::postWithDefault('view_public', null);
$newOwnerId = Safe::postWithDefault('newOwner', null);
$ownerId = Safe::postWithDefault('owner', null);
$removeAsset = Safe::postWithDefault('removeAsset', null);
$type = Safe::postWithDefault('type', null);

// Move an asset to a user or group.
if(isset($newOwnerId) && isset($assetIds) && isset($type)) {
	$assetIds = explode(',', $assetIds);

	// Work out what object to assign assets to.
	// $assetHolder must have an addAsset method
	switch($type) {
		case 'user':
			$assetHolder = new User($newOwnerId);
			break;
		case 'group':
			$assetHolder = new Group($newOwnerId);
			break;
		default:
			print 'Error: Can\'t put an asset in {$type}';
			exit(0);
	}

	// Add all the assets to $assetHolder(user/group). (loop through them)
	foreach($assetIds as $assetId) {
		$assetHolder->addAsset(Asset::RetrieveById($assetId), $adminUser);
	}

	// Success
	print '1';
	exit();
}
// Remove asset from a user of group
elseif (isset($removeAsset) && isset($ownerId) && isset($type)) {
	switch($type) {
		case 'user':
			$owner = new User($ownerId);
			break;
		case 'group':
			$owner = new Group($ownerId);
			break;
		default:
			print 'Error: Can\'t remove asset';
			exit(0);
	}
	print $owner->removeAsset($removeAsset);
}
// Change asset public status
elseif(isset($isPublic) && isset($assetId)) {
	$isPublic = ($isPublic == 'true');
	$asset = Asset::RetrieveById($assetId);
	$asset->setPublic($isPublic);
	$asset->Save($adminUser);
	
	// Success
	print 1;
	exit();
}
else {
	print 'Error: Please try again.';
}