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
 * The code behind for page.php (static version))
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: page.inc.php 834 2009-12-23 10:24:45Z richard $
*/

//include_once('conf.php');
include_once("system/initialise.php");
include_once('_includes/ui.inc.php');
include_once('model/User.class.php');
include_once('model/Video.class.php');
include_once('class/Events/PageEventDispatcher.class.php');

$page = Page::GetPageById($pageId);
$page->setViewer($studentUser);

if( isset($page) ) {
	$tab = $studentUser->getTabByPage($page);
	$page->getJavaScriptVariables()->addVariable('phpTabPlace', $tab->getIndex());
	$pagingMenu = $tab->PagePagingmenu($page);
	$pagingMenu->setClass('inline-list');
}