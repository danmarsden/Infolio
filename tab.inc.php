<?php

/**
 * The code behind for tab.php
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: tab.inc.php 813 2009-11-04 15:04:11Z richard $
 * @link       NA
 * @since      NA
*/

//include_once('conf.php');
include_once("system/initialise.php");
include_once('_includes/ui.inc.php');
include_once('class/Events/TabEventDispatcher.class.php');

// Make sure they're logged in
include('_includes/login.inc.php');

/* ** Event handlers ** */
include_once('_includes/accessibility-toolbar-button-event.functions.php');


function onDeleteTab(Tab $_tab)
{
	global $studentUser;
	
	$_tab->Delete($studentUser);

	// Redirect (stops refresh causing attempt to delete tab twice)
	header("Location: .");
}

function onEnterEditIcon(Tab $tab, SimplePage $page, $sortMethod, $pictureFilter)
{
	global $studentUser, $pictureChooseHtml;

	// Set up tab icon choosing HTML
	$linkCommands = array('a'=>TabEventDispatcher::ACTION_SAVE_ICON);
	$filterCommands = array('a'=>TabEventDispatcher::ACTION_EDIT_ICON);
	$collection = $studentUser->getAssetCollection();
	$collection->setAssetType(Asset::IMAGE);

	$pictureChooseHtml = $collection->HtmlThumbnails($page, $studentUser, $pictureFilter, $linkCommands, $filterCommands);

	// Display page in edit mode
	$page->setMode(SimplePage::MODE_EDIT);
	onEnterEditTab($tab, $page, $sortMethod);
}

/**
 * Event handler - When the tab is first loaded in EDIT mode
 * @return 
 * @param $tabName String
 * @param $sortMethod Object
 */
function onEnterEditTab(Tab $_tab, $_page, $sortMethod)
{
	global $page, $tab, $editMenu, $studentTheme, $studentUser;
	$tab = $_tab;
	$page = $_page;

	$tab->setViewer($studentUser);

	// Set up menus
	$editMenu = new Menu( array(
								Link::CreateIconLink('Finish', $page->PathWithQueryString(array('mode'=>Page::MODE_SHOW)), $studentTheme->Icon('stop-abort'), array('title'=>"Finish editing {$tab->getName()} tab",)),
								Link::CreateIconLink('Add Page', "page-0?tab={$tab->getId()}", $studentTheme->Icon('add-page'), array('title'=>'Add new page'))
								),
							'menu_tabs');
	$editMenu->setClass('inline-list');
}

/**
 * Event handler - When the tab is first loaded in SHOW mode
 * @return 
 * @param $tabName String
 * @param $sortMethod Object
 */
function onEnterShowTab(Tab $_tab, SimplePage $_page, $sortMethod)
{
	global $page, $tab, $sortMenu, $editMenu, $studentTheme, $studentUser;
	$tab = $_tab;
	$page = $_page;

	// Is this part of a template and is it a locked template
	$showAddPage = true;
	$tabTemplate = $tab->getTemplate();
	if(isset($tabTemplate)) {
		$showAddPage = !$tabTemplate->isLocked();
	}

	if($tab->getId() != null) {
		$tab->setViewer($studentUser);

		// Set up menus
		$sortMenu = $tab->SortMenuIfSortable($page, $studentTheme);
		if($showAddPage) {
			$editMenu = new Menu( array(
								Link::CreateIconLink('Add Page', "page-0?tab={$tab->getId()}", $studentTheme->Icon('add-page'), array('title'=>'Add new page'))
							));
			$editMenu->setClass('inline-list');
		}

		// Template pages can not be editted
		if($tab->getTemplate() == null) {
			$editMenu->addLink( Link::CreateIconLink('Edit Tab', $page->PathWithQueryString(array('mode'=>Page::MODE_EDIT)), $studentTheme->Icon('edit-page'), array('title'=>"Edit {$tab->getName()} tab")) );
		}
	}
}

function onNewTab()
{
	Debugger::debug('Event caught', 'tab.inc.php::onNewTab', Debugger::LEVEL_INFO);
	global $studentUser;

        $tab = Tab::CreateNewTab('', $studentUser);
        $tab->setWeight();
	$tab->Save($studentUser);

        $redirect = $tab->getLink()->getHref() . '?mode=edit'; 

	// Redirect (stops refresh causing duplicate new tabs)
        header("Location: $redirect");
}

function onSaveIcon(Tab $tab, $newIconId)
{
	global $studentUser;

	// Save new tab icon asset
	$tab->setIconById($newIconId);
	$tab->Save($studentUser);

	// Redirect (stops refresh causing multiple saves)
	header("Location: {$tab->getLink()->getHref()}");
}

function onSaveTab($tab, $newTabName)
{
	global $studentUser;
	
	// Make changes
	$tab->setName($newTabName);
	$tab->Save($studentUser);

	// Redirect (stops refresh causing duplicate saves)
	header("Location: {$tab->getLink()->getHref()}");
}

$sortMenu = null; $editMenu = null;

// Set up events
$eventD = new TabEventDispatcher();
$eventD->setUser($studentUser);
$eventD->setOnColourSwapHandler('onColourSwap');
$eventD->setOnDeleteTabHandler('onDeleteTab');
$eventD->setOnEditIconHandler('onEnterEditIcon');
$eventD->setOnEnterEditTabHandler('onEnterEditTab');
$eventD->setOnEnterShowTabHandler('onEnterShowTab');
$eventD->setOnNewTabHandler('onNewTab');
$eventD->setOnSaveIconHandler('onSaveIcon');
$eventD->setOnSaveTabHandler('onSaveTab');
$eventD->setOnSizeChangeHandler('onSizeChange');
$eventD->DispatchEvents();
