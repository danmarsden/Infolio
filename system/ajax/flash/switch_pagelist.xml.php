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
 * Page list XML for switch browser
 * The user must be logged in to get this data.
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: switch_pagelist.xml.php 644 2009-06-24 15:03:46Z richard $
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
