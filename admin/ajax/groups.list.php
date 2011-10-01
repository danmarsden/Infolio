<?php
/**
 * Group list JSON
 * Prints a list of groups in JSON format.
 * A admin user must be logged in to get this data.
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: groups.list.php 431 2009-03-06 18:00:23Z richard $
 * @link       NA
 * @since      NA
*/

include_once('../../system/initialiseBackOffice.php');
include_once('model/Group.class.php');

// Check user is logged in before letting them do stuff
$adminUser = BackOffice::RetrieveAndCheckAjaxAdminUser($_SESSION);

// Check if institution was specified
$fromInstitution = Safe::getWithDefault('inst', null);
if(isset($fromInstitution)) $fromInstitution = new Institution($fromInstitution);

// Get all users
$groups = Group::RetrieveGroups($adminUser, $fromInstitution);

// Print them in JSON format
print Group::CreateJsonString($groups);