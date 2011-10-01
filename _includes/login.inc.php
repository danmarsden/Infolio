<?php

/**
 * Login code (needed for each protected page)
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $id$
 * @link       NA
 * @since      NA
*/

include_once('model/User.class.php');

// Get user details and redirect them to login page if they're not logged in
session_start();
if( isset($_SESSION) ) {
	$studentUser = User::RetrieveBySessionData($_SESSION);
	Debugger::debug('Session count: ' . count($_SESSION), 'Session', Debugger::LEVEL_INFO);
}

// None secured
if(!isset($notSecured))
{
	if( !isset($studentUser)) {
		if(!isset($ignoreInstitutionUrl)) {
			// Redirect user to login
			header("Location: login.php");
		}
		else {
			// Ajax login should just exit and print error msg
			print "msg=User not logged in";
			exit();
		}
	}
	else {
		// Check the url includes the institution
        $ins = Safe::get('institution',PARAM_ALPHANUMEXT);
		if(!isset($ins) && !isset($ignoreInstitutionUrl)) {
			header('Location: /' . $studentUser->getInstitution()->getUrl() . '/');
		}
	}

	$studentTheme = $studentUser->getTheme();
}
else
{
	// Set up a blank user with no privileges or data
	// Only needed by help page that must be themed, but doesn't need you to be logged in for.
	$studentTheme = new Theme();
}

