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

//include_once('conf.php');
include_once("system/initialise.php");
include_once('class/Events/LoginEventDispatcher.class.php');
include_once('class/si/Theme.class.php');
include_once('class/si/Safe.class.php');
include_once('model/User.class.php');

session_start();
$studentTheme = new Theme();
$message = null;

function onLogin($_page, $username, $password, $institutionName)
{
	Debugger::debug("Event caught", 'login.inc.php::onLogin_1', Debugger::LEVEL_INFO);
	
	global $message, $page, $institution;
	
	$message = login($username, $password, $institutionName);
	if( isset($message) ) $message = "<p class=\"error\">{$message}</p>";

	$page = $_page;
	$institution = Institution::RetrieveByName($institutionName);
}

function onEnterPage($_page, $institutionName)
{
	Debugger::debug("Event caught", 'login.inc.php::onEnterPage_1', Debugger::LEVEL_INFO);
	global $page, $institution;
	$page = $_page;
	$institution = Institution::RetrieveByName($institutionName);
	
	// No institution set means they spelt it wrong
	if( !isset($institution)) {
		header("Location: /where.php?msg=Problem finding institution. Please choose again.");
	}

    $log = Safe::get('log');
    if(!empty($log)) {
		logout();
	}
}

function login($userName, $password, $institutionName)
{	
	if( User::Login($userName, $password, $institutionName) ) {
		// Redirect to homepage (Session data has been set)

		header("Location: .");
	}
	else {
		// Tell them they got something wrong
		$message = 'Wrong user name or password';
	}
	
	return $message;
}

function logout()
{
	PermissionManager::Logout();
	$message = 'You have logged out.';
}

$eventD = new LoginEventDispatcher();
$eventD->setOnEnterPageHandler('onEnterPage');
$eventD->setOnLoginHandler('onLogin');
$eventD->DispatchEvents();