<?php
/**
 * Page XML for switch browser
 * The user must be logged in to get this data.
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2009 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: switch_page.xml.php 777 2009-08-25 13:31:39Z richard $
 * @link       NA
 * @since      NA
*/

/*
 * * Query input *
 *   page_id
 *   User id is stored in session.
 */

include_once('../../initialise.php');
include_once('model/User.class.php');
include_once('model/Page.class.php');

// Check user is logged in before letting them do stuff
$ignoreInstitutionUrl = true;
include_once('_includes/login.inc.php');

// Get page
$pid = Safe::get('page_id', PARAM_INT);
$page = Page::GetPageById($pid, $studentUser);
$page->setViewer($studentUser);

if(!isset($page)) {
	print "msg=No page with id {$pid}";
	exit();
}

// Print XML
print $page->SwitchXml();