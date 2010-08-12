<?php

/**
 * The code behind for page.php
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: size.php 472 2009-03-19 19:57:21Z richard $
 * @link       NA
 * @since      NA
*/

include_once("../initialise.php");

// Make sure they're logged in
$ignoreInstitutionUrl = true;
include_once('_includes/login.inc.php');

include_once('class/si/Safe.class.php');

$size = Safe::GetArrayIndexValueWithDefault($_POST, 's', null);

if( isset($size) ) {
	$studentTheme = $studentUser->getTheme();
	$studentTheme->setSize($size);
	$studentTheme->Save($studentUser);
}

Debugger::debug("Ajax: size={$size}", 'size.php::main_1', Debugger::LEVEL_INFO);