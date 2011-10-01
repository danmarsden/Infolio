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
 * Login code (needed for each protected page)
 *
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $id$
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

