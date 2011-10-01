<?php

/**
 * The code behind for page.php
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: page.inc.php 813 2009-11-04 15:04:11Z richard $
 * @link       NA
 * @since      NA
*/

define('UPLOAD_LIMIT', 32000000);

//include_once('conf.php');
include_once("system/initialise.php");
include_once('_includes/ui.inc.php');
include_once('model/User.class.php');
include_once('model/Video.class.php');
include_once('class/Events/PageEventDispatcher.class.php');
include_once('class/si/Uploader.class.php');

define('ADD_COMMAND', 'add');

// Make sure they're logged in
include_once('_includes/login.inc.php');

// Menu placeholders
$editMenu = null; $blockLayoutsMenu = null; $pagingMenu = null;

/* ** Event handlers ** */
include_once('_includes/accessibility-toolbar-button-event.functions.php');


function onAttachmentDelete($page, $attachmentId)
{
	global $studentUser;

	Debugger::debug("Event caught", 'page.inc.php::onAttachmentDelete');
	$attachment = new Attachment($attachmentId);
	$attachment->Delete($studentUser);

	header("Location: {$page->PathWithQueryString()}");
}

function onAttachmentUpload($page)
{
	Debugger::debug("Event caught", 'page.inc.php::onAttachmentUpload');

	global $studentUser, $pageMessage;
	//global $studentTheme, $studentUser, $collection, $assetId, $filterMenu, $filterMenu2, $assetFilter, $uploader;

	$uploadProblems = false;
	$uploader = new Uploader(UPLOAD_LIMIT);

	// Try to upload the file and show an error message if there is a problem.
	try {
		$uploadId = $uploader->copyUpload('fAttach', $studentUser, $page);
	}
	catch(Exception $e) {
		$uploadProblems = true;
		$page->addWarningMessage( $e->getMessage() );

		// Call normal show page function
		onEnterShowPage($page);
	}

	// redirect (stops refresh causing duplicate block deletes)
	header("Location: {$page->PathWithQueryString()}");
}

/**
 * A block's content has been updated
 * @return 
 * @param $pageId Object
 * @param $wordBlocks Object
 * @param $blockId Object[optional]
 * @param $templateId Object[optional]
 * @param $blockWeight Object[optional]
 */
function onBlockSave($page, $title, $wordBlocks, $blockId = null, $templateId = null, $blockWeight = null)
{
	Debugger::debug("Event caught", 'page.inc.php::onBlockSave_1', Debugger::LEVEL_INFO);
	global $studentUser;

	if( isset($blockId) ) {
		// Existing block
		$block = $page->getBlock($blockId);

		// Make changes
		$block->setTitle($title);
		$block->setWordBlocks($wordBlocks);

		// remove edit box once content is updated
		unset($_GET['blockedit']);

		$block->Save($studentUser);
	}
	else {
		Logger::Write("Failure to save block in page.inc.php", Logger::TYPE_ERROR, $studentUser);
	}

	// Redirect (stops refresh causing duplicate saves)
	header("Location: {$page->PathWithQueryString( array('mode'=>Page::MODE_EDIT), false, 'b'.$blockId)}");
}

function onDeletePage($page)
{
	global $studentUser;
	
	$page->Delete($studentUser);
	header('Location: ' . $page->getTab()->getLink()->getHref());
}

function onNewPage($ownerTabId)
{
	Debugger::debug("Event caught", 'page.inc.php::onNewPage_1', Debugger::LEVEL_INFO);
	global $studentUser;
	
	$page = new Page();
	$page->setUser($studentUser);
	$page->setTab(new Tab($ownerTabId));
	$page->setEnabled(true);
	$page->Save($studentUser);

	// redirect (stops refresh causing duplicate new pages)
	header("Location: {$page->PathWithQueryString( array('mode'=>Page::MODE_EDIT), false)}");
}

function onSavePage($page, $title)
{
	Debugger::debug("Event caught", 'page.inc.php::onSavePage_1', Debugger::LEVEL_INFO);
	global $studentUser;

	$page->setTitle($title);
	$page->Save($studentUser);

	// redirect (stops refresh causing duplicate new pages)
	header("Location: {$page->PathWithQueryString(array('mode'=>Page::MODE_SHOW), false)}");
}

function onSetImage($blockId, $imagePlace, $imageId, $page)
{
	global $studentUser;

	Debugger::debug("Event caught", 'page.inc.php::onSetImage_1', Debugger::LEVEL_INFO);
	
	Debugger::debug("Block id: $blockId, Image place: $imagePlace, Image id: $imageId", 'page.inc.php::onSetImage_2', Debugger::LEVEL_INFO);
	
	$block = PageBlock::RetrieveById($blockId);
	$block->setPicture($imagePlace, $imageId);
	$block->Save($studentUser);
	
	// redirect (stops refresh causing duplicate new pages)
	header("Location: {$page->PathWithQueryString( array('mode'=>Page::MODE_EDIT, 'blockedit'=>$blockId), false, 'b'.$blockId)}");
}

function onEnterShowPage($_page)
{
	Debugger::debug("Event caught", 'page.inc.php::onEnterShowPage_1', Debugger::LEVEL_INFO);
	global $page, $editMenu, $studentTheme, $studentUser, $uploader;
	$page = $_page;
	$page->setViewer($studentUser);
	
	createTab($page);

	$editMenu = new Menu( array(Link::CreateIconLink('Edit',
										$page->PathWithQueryString(array('mode'=>Page::MODE_EDIT), false),
										$studentTheme->Icon('edit-page'), array('title' => "Edit {$page->getTitle()}"))
								));
	$editMenu->setClass('inline-list');

	$uploader = new Uploader('New attachment', UPLOAD_LIMIT);
}

function onEnterEditPage($_page, $editPictureId = null, $assetFilter=AssetCollection::FILTER_ALL, $editBlockId = null)
{
	Debugger::debug("Event caught", 'page.inc.php::onEnterEditPage_1', Debugger::LEVEL_INFO);
	global $page, $editMenu, $blockLayoutsMenu, $pictureChooseHtml, $studentTheme, $studentUser, $uploader;
	$page = $_page;
	$page->setViewer($studentUser);
	
	createTab($page);

	// check for 'add' command
	if( !isset($editBlockId) ) $editBlockId = checkForNewBlock(Safe::get(ADD_COMMAND), $page);

	// Show an edit block
	if( isset($editBlockId) ) {
		$page->setEditBlock($editBlockId);
	}

	// Create menus
	$editMenu = new Menu( array( Link::CreateIconLink('Finish', $page->PathWithQueryString( array('mode'=>Page::MODE_SHOW)), $studentTheme->Icon('stop-abort'), array('title'=>"Finish editing {$page->getTitle()}"))));
	$editMenu->setClass('inline-list');
	
	// Create menu for changing picture
	if( isset($editPictureId) ) {
		$linkCommands = array('blockedit'=>$editBlockId, 'a'=>PageEventDispatcher::ACTION_SET_IMAGE, 'imageedit'=>$editPictureId);
		$filterCommands = array('blockedit'=>$editBlockId, 'imageedit'=>$editPictureId);
		$collection = $studentUser->getAssetCollection();
		$pictureChooseHtml = $collection->HtmlThumbnails($page, $studentUser, $assetFilter, $linkCommands, $filterCommands);
	}
	else {
		// Only show block edit when not changing picture
		$blockLayoutsMenu = PageBlock::GetLayoutsMenu($page);
	}

	$uploader = new Uploader('New attachment', UPLOAD_LIMIT);
}

function onBlockUp($blockId, $page)
{
	Debugger::debug("Event caught", 'page.inc.php::onBlockUp_1', Debugger::LEVEL_INFO);
	
	$page->MoveBlockUp($blockId);
	
	// redirect (stops refresh causing duplicate block ups)
	header("Location: {$page->PathWithQueryString(array('mode'=>Page::MODE_EDIT), false, 'b'.$blockId)}");
}

function onBlockDown($blockId, $page)
{
	Debugger::debug("Event caught", 'page.inc.php::onBlockDown_1', Debugger::LEVEL_INFO);
	
	$page->MoveBlockDown($blockId);
	
	// redirect (stops refresh causing duplicate block downs)
	header("Location: {$page->PathWithQueryString(array('mode'=>Page::MODE_EDIT), false, 'b'.$blockId)}");
}

function onBlockDelete($page, $blockId)
{
	Debugger::debug("Event caught {$blockId}", 'page.inc.php::onBlockDelete_1', Debugger::LEVEL_INFO);
	$page->DeleteBlock($blockId);
	
	// redirect (stops refresh causing duplicate block deletes)
	header("Location: {$page->PathWithQueryString(array('mode'=>Page::MODE_EDIT), null, false)}");
}

// Set up events
$eventD = new PageEventDispatcher();
$eventD->setUser($studentUser);
$eventD->setOnAttachmentDeleteHandler('onAttachmentDelete');
$eventD->setOnBlockDeleteHandler('onBlockDelete');
$eventD->setOnBlockDownHandler('onBlockDown');
$eventD->setOnBlockSaveHandler('onBlockSave');
$eventD->setOnBlockUpHandler('onBlockUp');
$eventD->setOnColourSwapHandler('onColourSwap');
$eventD->setOnDeletePageHandler('onDeletePage');
$eventD->setOnEnterEditPageHandler('onEnterEditPage');
$eventD->setOnEnterShowPageHandler('onEnterShowPage');
$eventD->setOnNewPageHandler('onNewPage');
$eventD->setOnSavePageHandler('onSavePage');
$eventD->setOnSetImageHandler('onSetImage');
$eventD->setOnSizeChangeHandler('onSizeChange');
$eventD->setOnUploadAttachmentHandler('onAttachmentUpload');
$eventD->DispatchEvents();



/**
 * Checks to see if the user has asked for a new block to be added and creates an HTML form if they have
 * @return String HTML form
 * @param $input Array
 */
function checkForNewBlock($commandInput, $page)
{
	//TODO: Make this work so it saves the block before displaying it
	global $studentUser;
	// New 
	if ( isset($commandInput) ) {
		$templateId = $commandInput;
		$newBlock = $page->CreateNewBlock($templateId, $studentUser);
		$newBlock->Save($studentUser);

		// Add this block to the page
		$page->AddBlock($newBlock);
		
		//$tempBlock->setClass("bl{$templateId}");
		return $newBlock->getId();
	}
	else {
		return null;
	}
}

/**
 * Creates the $tab, $studentUser and $page
 * Global objects that get used by the front end page
 * @return 
 * @param $tabName Object
 * @param $sortMethod Object
 */
function createTab($page)
{
	global $tab, $pagingMenu;

	if( isset($page) ) {
		$tab = Tab::GetPagesTab($page);
		$pagingMenu = $tab->PagePagingmenu($page);
		$pagingMenu->setClass('inline-list');
	}
	else {
		// Page not found
		// ToDo: Create page not found page
	}
}
