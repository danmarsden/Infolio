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
 * The code behind for index.php
 *
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: index.inc.php 825 2009-12-14 09:28:52Z richard $
*/

//include_once('conf.php');
include_once("system/initialise.php");
include_once('class/Events/HomePageEventDispatcher.class.php');
include_once('class/si/Theme.class.php');
include_once('class/si/SimplePage.class.php');
include_once('model/User.class.php');

// Make sure they're logged in
include('_includes/login.inc.php');

/* ** Event handlers ** */
include_once('_includes/accessibility-toolbar-button-event.functions.php');


function onChangeUserDescription($_page)
{
	global $page, $studentDetails, $studentTheme, $studentUser;
	$page = $_page;

	$studentDetails = $studentUser->HtmlUserDetails($page, $studentTheme, true);
}

function onChangeUserPicture($_page, $pictureFilter = AssetCollection::FILTER_ALL)
{
	global $page, $pictureChooseHtml, $studentDetails, $studentTheme, $studentUser;
	$page = $_page;
	
	$studentDetails = $studentUser->HtmlUserDetails($page, $studentTheme);

	$collection = $studentUser->getAssetCollection($pictureFilter);
	$collection->setAssetType(Asset::IMAGE);
	$filterCommands = array('a'=>HomePageEventDispatcher::ACTION_CHANGE_USER_IMAGE);
	$pictureChooseHtml = $studentTheme->SolidBox( $collection->HtmlThumbnails($page, $studentUser, $pictureFilter, null, $filterCommands) );
}

function onSetUserDescription($page, $description)
{
	global $studentUser;
	
	$studentUser->setDescription($description);
	$studentUser->Save($studentUser);

	// Redirect page
	header("Location: {$page->PathWithQueryString(null, false)}");
}

/**
 * User has chosen new picture. Save it and redirect to show page.
 * @global User $studentUser
 * @param Page $page
 * @param int $newPictureId
 */
function onSetUserPicture($page, $newPictureId)
{
	Debugger::debug("Event caught (pic id: {$newPictureId})", 'index.inc.php::onSetUserPicture_1', Debugger::LEVEL_INFO);
	
	global $studentUser;
	
	$studentUser->setProfilePictureId($newPictureId);
	$studentUser->Save($studentUser);
	
	// Redirect page
	header("Location: {$page->PathWithQueryString(null, false)}");
}

function onShowPage($_page)
{
	global $page, $studentDetails, $studentTheme, $studentUser;
	$page = $_page;
	
	$studentDetails = $studentUser->HtmlUserDetails($page, $studentTheme);
}

// Set up events
$eventD = new HomePageEventDispatcher();
$eventD->setUser($studentUser);
$eventD->setOnChangeUserDescriptionHandler('onChangeUserDescription');
$eventD->setOnChangeUserPictureHandler('onChangeUserPicture');
$eventD->setOnColourSwapHandler('onColourSwap');
$eventD->setOnEnterPageHandler('onShowPage');
$eventD->setOnSetUserDescriptionHandler('onSetUserDescription');
$eventD->setOnSetUserPictureHandler('onSetUserPicture');
$eventD->setOnSizeChangeHandler('onSizeChange');
$eventD->DispatchEvents();