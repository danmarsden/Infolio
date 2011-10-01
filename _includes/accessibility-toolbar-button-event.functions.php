<?php

/**
 * font-event.inc.php
 * The PHP event code to handle font-size events
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: accessibility-toolbar-button-event.functions.php 332 2009-01-08 14:03:18Z richard $
 * @link       NA
 * @since      NA
*/

function onColourSwap($page)
{
	Debugger::debug("Event caught", 'accessibility-toolbar-button-event.functions.php::onSwapColours_1', Debugger::LEVEL_INFO);
	global $studentUser;
	
	$studentTheme = $studentUser->getTheme();
	$studentTheme->InvertColours();
	$studentTheme->Save($studentUser);
	
	// redirect (stops refresh causing duplicate block ups)
	header("Location: {$page->PathWithQueryString(null, false)}");
}

function onSizeChange($page, $fontSize)
{
	Debugger::debug("Event caught", 'accessibility-toolbar-button-event.functions.php::onSizeChange_1', Debugger::LEVEL_INFO);
	global $studentUser;
	
	$studentTheme = $studentUser->getTheme();
	$studentTheme->setSize($fontSize);
	$studentTheme->Save($studentUser);
	
	// redirect (stops refresh causing duplicate block ups)
	header("Location: {$page->PathWithQueryString(null, false)}");
}