<?php
/**
 * Login switch browser
 * Logs the user in so they can access other data
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2009 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: switch_login.php 717 2009-07-27 08:52:44Z richard $
 * @link       NA
 * @since      NA
*/

/*
 * * Query input *
 *   user_id : integer
 *   user_passcode : integer
 */

include_once('../../initialiseBackOffice.php');
include_once('model/User.class.php');

// Get query data
$userId = Safe::request('user_id');
$switchLoginNumber = Safe::request('user_passcode');

$loginCode = User::LoginSwitch($userId, $switchLoginNumber);
if($loginCode == PermissionManager::SWITCH_LOGIN_ERROR_NO_ERROR) {
	// Login success (session data will now exist)
	print 'login_success=1';
}
else {
	// Login failure
	print "error_code={$loginCode}";
}