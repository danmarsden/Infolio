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
 * @version    $Id: settings.inc.php 570 2009-05-21 13:06:21Z richard $
*/

//include_once('conf.php');
include_once("system/initialise.php");
include_once('class/Events/SettingsEventDispatcher.class.php');
include_once('class/si/Theme.class.php');
include_once('class/si/SimplePage.class.php');
include_once('model/User.class.php');

// Make sure they're logged in
include('_includes/login.inc.php');

//$page = new SimplePage($studentUser->getFirstName() . "'s home page");

/* ** Event handlers ** */
include_once('_includes/accessibility-toolbar-button-event.functions.php');

function onChangeColour($page, $colour)
{
	global $studentTheme, $studentUser;
	
	$studentTheme->setColour($colour);
	$studentTheme->Save($studentUser);
	
	// Redirect page
	header("Location: {$page->PathWithQueryString(null, false)}");
}

function onSetPassword($page, $newPassword)
{
	global $studentUser;
	$studentUser->getPermissionManager()->setPassword($newPassword);
	$studentUser->Save($studentUser);

	// Redirect page
	header("Location: {$page->PathWithQueryString(array('show'=>true, 'changed'=>true), false)}");
}

function onShowPage($_page, $_passwordChanged = false)
{
	global $page, $passwordChanged;

	$page = $_page;
	$passwordChanged = $_passwordChanged;
}

// Set up events
$eventD = new SettingsEventDispatcher();
$eventD->setUser($studentUser);
$eventD->setOnChangeColourHandler('onChangeColour');
$eventD->setOnColourSwapHandler('onColourSwap');
$eventD->setOnEnterPageHandler('onShowPage');
$eventD->setOnSetPasswordHandler('onSetPassword');
$eventD->setOnSizeChangeHandler('onSizeChange');
$eventD->DispatchEvents();