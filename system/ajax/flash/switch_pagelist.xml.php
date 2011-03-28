<?php
/**
 * Page list XML for switch browser
 * The user must be logged in to get this data.
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2009 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: switch_pagelist.xml.php 644 2009-06-24 15:03:46Z richard $
 * @link       NA
 * @since      NA
*/

/*
 * * Query input *
 *   tab_id
 *   User id is stored in session.
 */

include_once('../../initialise.php');
include_once('model/User.class.php');
include_once('model/Page.class.php');
include_once('model/Tab.class.php');

// Check user is logged in before letting them do stuff
$ignoreInstitutionUrl = true;
include_once('_includes/login.inc.php');

// Get tab
$tid = Safe::get('tab_id', PARAM_INT);
$tab = Tab::GetTabById($tid);
if(!isset($tab)) {
	print "msg=No tab with id {$tid}";
	exit();
}


$pages = Page::RetrieveByTab($tab, $studentUser);

// Print XML
print "<pagelist>\n";
foreach($pages as $page) {
	$page->setViewer($studentUser);
	print $page->SwitchSummaryXml();
}
print "</pagelist>";
