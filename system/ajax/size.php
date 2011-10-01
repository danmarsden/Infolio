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
 * The code behind for page.php
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: size.php 472 2009-03-19 19:57:21Z richard $
 * @link       NA
 * @since      NA
*/

include_once("../initialise.php");

// Make sure they're logged in
$ignoreInstitutionUrl = true;
include_once('_includes/login.inc.php');

include_once('class/si/Safe.class.php');

$size = Safe::postWithDefault('s', null);

if( isset($size) ) {
	$studentTheme = $studentUser->getTheme();
	$studentTheme->setSize($size);
	$studentTheme->Save($studentUser);
}

Debugger::debug("Ajax: size={$size}", 'size.php::main_1', Debugger::LEVEL_INFO);