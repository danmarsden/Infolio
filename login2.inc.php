<?php
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
	Debugger::debug("Event caught", 'login2.inc.php::onLogin_1', Debugger::LEVEL_INFO);
	
	global $message;
	$message = login($username, $password, $institutionName);
	if( isset($message) ) $message = "<p class=\"error\">{$message}</p>";

	global $page;
	$page = $_page;
}

function onEnterPage($_page, $institutionName)
{
	Debugger::debug("Event caught", 'login2.inc.php::onEnterPage_1', Debugger::LEVEL_INFO);
	global $page, $institution;
	$page = $_page;
	$institution = Institution::RetrieveByName($institutionName);
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
		//$message = "Success";
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