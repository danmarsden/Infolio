<?php

/**
 * initialiseBackOffice.php
 * initialise all general classes for backoffice
 *

 *
 * @author     Elvir Leonard <elvir.leonard@rixcentre.org>
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: initialiseBackOffice.php 715 2009-07-26 18:57:07Z richard $
 * @link       NA
 * @since      NA
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