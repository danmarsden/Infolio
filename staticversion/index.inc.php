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
 * The code behind for print version of index.php
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: index.inc.php 825 2009-12-14 09:28:52Z richard $
*/

//include_once('conf.php');
//include_once("../system/initialise.php");
include_once('class/Events/HomePageEventDispatcher.class.php');
include_once('class/si/Theme.class.php');
include_once('class/si/SimplePage.class.php');
include_once('model/User.class.php');

/* ** Setup page ** */

$page = new SimplePage();
$page->getJavaScriptVariables()->addVariable('phpTabPlace', 0);
$page->setTitle("{$studentUser->getFirstName()}'s home page");
$page->setName('About-me');
$studentDetails = $studentUser->HtmlUserDetails($page, $studentTheme);
