<?

/**
 * The code behind for index.php
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: settings.inc.php 570 2009-05-21 13:06:21Z richard $
 * @link       NA
 * @since      NA
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
$eventD = new SettingsEventDispatcher($_GET, $_POST);
$eventD->setUser($studentUser);
$eventD->setOnChangeColourHandler('onChangeColour');
$eventD->setOnColourSwapHandler('onColourSwap');
$eventD->setOnEnterPageHandler('onShowPage');
$eventD->setOnSetPasswordHandler('onSetPassword');
$eventD->setOnSizeChangeHandler('onSizeChange');
$eventD->DispatchEvents();