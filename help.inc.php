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
 * The code behind for help.php
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: help.inc.php 809 2009-11-03 08:45:09Z richard $
*/

//include_once('conf.php');
include_once("system/initialise.php");
include_once('_includes/ui.inc.php');
include_once('class/Events/HelpEventDispatcher.class.php');
include_once('class/SimpleBlock.class.php');
include_once('model/User.class.php');

// Get theme details if they're logged in
$notSecured = true;
include_once('_includes/login.inc.php');

/* ** Event handlers ** */
include_once('_includes/accessibility-toolbar-button-event.functions.php');

function onEnterPage($_page, $helpItem)
{
	global $helpLinks, $page;
	$page = $_page;

	$helpLinks[HELP_LOGIN] = new SimpleBlock('Login',
			'<p>Get <a href="http://in-folio.org.uk/help/login.html">logging in help</a>.</p>');
	$helpLinks[HELP_ABOUT_ME] = new SimpleBlock('About me',
			'<p>Get <a href="http://in-folio.org.uk/help/aboutme.html">about me page help</a>.</p>');
	$helpLinks[HELP__TABS] = new SimpleBlock('Tabs',
			'<p>Get <a href="http://in-folio.org.uk/help/tabs.html">tabs help</a>.</p>');
	$helpLinks[HELP_PAGES] = new SimpleBlock('Pages',
			'<p>Get <a href="http://in-folio.org.uk/help/pages.html">pages help</a>.</p>');
	$helpLinks[HELP_COLLECTION] = new SimpleBlock('Collection',
			'<p>Get <a href="http://in-folio.org.uk/help/collection.html">collection help</a>.</p>');
	$helpLinks[HELP_SETTINGS] = new SimpleBlock('Settings',
			'<p>Get <a href="http://in-folio.org.uk/help/settings.html">settings help</a>.</p>');

	// Work out help item to put on top
	$topContextHelpItemArray = array_splice($helpLinks, $helpItem, 1);
	array_unshift($helpLinks, $topContextHelpItemArray[0]);
}

// Set up events
$eventD = new HelpEventDispatcher();
if(isset($studentUser))
{
	$eventD->setUser($studentUser);
	$eventD->setOnColourSwapHandler('onColourSwap');
	$eventD->setOnSizeChangeHandler('onSizeChange');
}
$eventD->setOnEnterPageHandler('onEnterPage');
$eventD->DispatchEvents();