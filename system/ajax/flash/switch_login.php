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
 * Login switch browser
 * Logs the user in so they can access other data
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: switch_login.php 717 2009-07-27 08:52:44Z richard $
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