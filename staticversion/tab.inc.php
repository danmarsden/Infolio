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
 * The code behind for tab.php (static version)
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: tab.inc.php 827 2009-12-18 16:36:47Z richard $
*/

//include_once('conf.php');
include_once("system/initialise.php");
include_once('_includes/ui.inc.php');
include_once('class/Events/TabEventDispatcher.class.php');



/* ** Setup ** */

$tab = $studentUser->getTabById($tabId, 'new');

$page = new SimplePage( $tab->getName() );
$page->setName($tab->getName());

// Static version won't have commands
$showAddPage = false;

$tab->setViewer($studentUser);
$page->getJavaScriptVariables()->addVariable('phpTabPlace', $tab->getIndex());


$sortMenu = null; $editMenu = null;