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
 * initialiseBackOffice.php
 * initialise all general classes for backoffice
 *
 * @author     Elvir Leonard <elvir.leonard@rixcentre.org>
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: initialiseBackOffice.php 715 2009-07-26 18:57:07Z richard $
*/

include_once("initialise.php"); //system configuration

include_once(DIR_FS_CLASS . "BackOffice.php");
include_once(DIR_FS_CLASS . "Module.php");
include_once(DIR_FS_CLASS . "Validation.php");
include_once(DIR_FS_CLASS . "Breadcrumb.php");
include_once(DIR_FS_CLASS . "JavascriptFramework.php");

//$link=0;
$backoffice		=	BackOffice::getInstance(); 
$breadcrumb		=	new Breadcrumb; 
$validation		=	new Validation;
$jsFramework	=	new JavascriptFramework;