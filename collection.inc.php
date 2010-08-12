<?

/**
 * The code behind for collection.php
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: collection.inc.php 808 2009-11-02 15:51:14Z richard $
 * @link       NA
 * @since      NA
*/

define('UPLOAD_LIMIT', 32000000);

//include_once('conf.php');
include_once("system/initialise.php");
include_once('class/si/Link.class.php');
include_once('class/si/Menu.class.php');
include_once('class/si/Theme.class.php');
include_once('class/si/SimplePage.class.php');
include_once('class/si/Uploader.class.php');
include_once('model/User.class.php');
include_once('model/Video.class.php');
include_once('class/Events/CollectionEventDispatcher.class.php');


// Make sure they're logged in
include('_includes/login.inc.php');

/* ** Event handlers ** */
include_once('_includes/accessibility-toolbar-button-event.functions.php');

function onShowCollection($_page, $selectedAssetId, $filter)
{
	Debugger::debug("Event caught ({$selectedAssetId})", 'collection.inc.php::onShowCollection_1', Debugger::LEVEL_INFO);
	
	global $studentTheme, $studentUser, $page, $collection, $assetId, $filterMenu, $filterMenu2, $assetFilter, $uploader, $tags;
	$page = $_page;
	
	$assetFilter = $filter;
	$collection = $studentUser->getAssetCollection();
	$collection->setSelectedAsset($selectedAssetId, $assetFilter);
	
	$uploader = new Uploader('Add a new picture or video', UPLOAD_LIMIT);
}

function onTagAdd(SimplePage $_page, $assetId, $tagName)
{
	global $studentUser;

	$asset = Asset::RetrieveById($assetId);
	$newTag = Tag::CreateOrRetrieveByName($tagName, $studentUser->getInstitution(), $studentUser);
	$asset->addTag($newTag, $studentUser);

	header("Location: {$_page->PathWithQueryString(array('c'=>$assetId, 'tag'=>$tagName), false) }");
}

function onTagRemove(SimplePage $_page, $assetId, $tagName)
{
	global $studentUser;

	$asset = Asset::RetrieveById($assetId);
	$removeTag = Tag::RetrieveByName($tagName, $studentUser->getInstitution());
	if(isset($removeTag) && isset($asset)) {
		$asset->RemoveTag($removeTag, $studentUser);
	}

	header("Location: {$_page->PathWithQueryString(array('c'=>$assetId), false) }");
}

function onAssetDelete(SimplePage $_page, $assetId)
{
	global $studentUser;

	$asset = Asset::RetrieveById($assetId);
	$asset->DeleteForUser($studentUser, $studentUser);

	header("Location: {$_page->PathWithQueryString()}");
}

function onAssetEdit($page, $selectedAssetId, $filter)
{
	global $studentUser;

	$collection = $studentUser->getAssetCollection();
	$collection->setMode(AssetCollection::MODE_EDIT);

	onShowCollection($page, $selectedAssetId, $filter);
}

function onAssetSave($page, $assetId, $title)
{
	global $studentUser;

	$asset = Asset::RetrieveById($assetId, $studentUser);
	$asset->setTitle($title);
	$asset->Save($studentUser);

	header("Location: {$page->PathWithQueryString(array('c'=>$assetId), false)}");
}

function onFavouriteSet(SimplePage $_page, $assetId, $isFavourite)
{
	Debugger::debug("Event caught", 'collection.inc.php::onFavouriteSet', Debugger::LEVEL_INFO);
	global $studentUser;
	$asset = Asset::RetrieveById($assetId);

	$asset->setFavourite($isFavourite, $studentUser);
	header("Location: {$_page->PathWithQueryString(array('c'=>$assetId), false)}");
}

function onUpload($_page)
{
	Debugger::debug("Event caught", 'collection.inc.php::onUpload_1', Debugger::LEVEL_INFO);
	
	global $studentUser, $pageMessage;
	//global $studentTheme, $studentUser, $collection, $assetId, $filterMenu, $filterMenu2, $assetFilter, $uploader;

	$uploadProblems = false;
	$uploader = new Uploader(UPLOAD_LIMIT);

	// Try to upload the file and show an error message if there is a problem.
	try {
		$assetId = $uploader->copyUpload('uFile', $studentUser);
	}
	catch(Exception $e) {
		$uploadProblems = true;
		$pageMessage = $e->getMessage();

		// Call normal show page function
		onShowCollection($_page, null, AssetCollection::FILTER_ALL);
	}
	
	// Redirect (stops refresh causing duplicate uploads)
	if(!$uploadProblems) header("Location: {$_page->PathWithQueryString(array('c'=>$assetId), false)}");
}

// Set up events
$eventD = new CollectionEventDispatcher($_GET, $_POST);
$eventD->setUser($studentUser);
$eventD->setOnColourSwapHandler('onColourSwap');
$eventD->setOnDeleteAssetHandler('onAssetDelete');
$eventD->setOnEditAssetHandler('onAssetEdit');
$eventD->setOnEnterShowCollectionHandler('onShowCollection');
$eventD->setOnFavouriteSetHandler('onFavouriteSet');
$eventD->setOnSaveAssetHandler('onAssetSave');
$eventD->setOnSizeChangeHandler('onSizeChange');
$eventD->setOnTagAddHandler('onTagAdd');
$eventD->setOnTagRemoveHandler('onTagRemove');
$eventD->setOnUploadHandler('onUpload');
$eventD->DispatchEvents();