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
 * font-event.inc.php
 * The PHP event code to handle font-size events
 *
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: accessibility-toolbar-button-event.functions.php 332 2009-01-08 14:03:18Z richard $
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