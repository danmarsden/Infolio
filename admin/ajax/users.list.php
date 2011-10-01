<?php
/**
 * User list JSON
 * Prints a list of users in JSON format.
 * A admin user must be logged in to get this data.
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: users.list.php 431 2009-03-06 18:00:23Z richard $
 * @link       NA
 * @since      NA
*/

include_once('../../system/initialiseBackOffice.php');
include_once('model/User.class.php');

// Check user is logged in before letting them do stuff
$adminUser = BackOffice::RetrieveAndCheckAjaxAdminUser($_SESSION);

// Check if institution was specified
$fromInstitution = Safe::getWithDefault('inst', null);
if(isset($fromInstitution)) $fromInstitution = new Institution($fromInstitution);

// Get all users
$users = User::RetrieveUsers($adminUser, $fromInstitution);

// Print them in JSON format
print User::CreateJsonString($users);