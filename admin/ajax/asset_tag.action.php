<?php
/*
 * Assigns tags to an asset.
 * A admin user must be logged in to get this data.
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $$
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
$assetId = Safe::GetArrayIndexValueWithDefault($_POST, 'aId', null);
$newTags = Safe::GetArrayIndexValueWithDefault($_POST, 'tags', null);

$asset = Asset::RetrieveById($assetId, $adminUser);

if(isset($asset)) {
	$newTag = Tag::CreateOrRetrieveByName($newTags, $adminUser->getInstitution(), $adminUser);
	print $asset->addTag($newTag);
}
else {
	print 'Error: bad asset id';
}