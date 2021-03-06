<?php
/*
 * Assigns tags to an asset.
 * A admin user must be logged in to get this data.
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $$
*/

include_once('../../system/initialiseBackOffice.php');
include_once('model/Group.class.php');
include_once('model/Image.class.php');
include_once('model/Video.class.php');
include_once('class/si/Safe.class.php');

// Check user is logged in before letting them do stuff
$adminUser = BackOffice::RetrieveAndCheckAjaxAdminUser($_SESSION);

// Get the info passed by AJAX request
$assetId = Safe::postWithDefault('aId', null, PARAM_INT);
$newTags = Safe::postWithDefault('tags', null);

$asset = Asset::RetrieveById($assetId, $adminUser);

if(isset($asset)) {
	$newTag = Tag::CreateOrRetrieveByName($newTags, $adminUser->getInstitution(), $adminUser);
	print $asset->addTag($newTag);
}
else {
	print 'Error: bad asset id';
}