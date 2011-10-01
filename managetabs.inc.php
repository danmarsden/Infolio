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
 * The code behind tabs.php
 *
*/

//include_once('conf.php');
include_once("system/initialise.php");
include_once('_includes/ui.inc.php');
include_once('class/si/Link.class.php');
include_once('class/si/Menu.class.php');
include_once('class/si/Theme.class.php');
include_once('class/si/SimplePage.class.php');
include_once('model/User.class.php');
include_once('model/Tab.class.php');
include_once('class/Events/TabEventDispatcher.class.php');
include_once('class/Events/ManageTabsEventDispatcher.class.php');

// Make sure they're logged in
include('_includes/login.inc.php');

/* ** Event handlers ** */
include_once('_includes/accessibility-toolbar-button-event.functions.php');

function onEnterShowManageTabs(SimplePage $_page)
{
    global $studentTheme, $studentUser, $page;
    $page = $_page;
}

function onMoveTab(SimplePage $_page, $_direction, $tabid)
{
	global $studentTheme, $studentUser, $page;
    $page = $_page;
    $direction = $_direction;

    $tab = Tab::GetTabById($tabid);
    $tab->setWeight($direction);
    onEnterShowManageTabs($page);
}

// Set up events
$eventD = new ManageTabsEventDispatcher();
$eventD->setUser($studentUser);
$eventD->setOnColourSwapHandler('onColourSwap');
$eventD->setOnEnterShowManageTabsHandler('onEnterShowManageTabs');
$eventD->setOnMoveTabHandler('onMoveTab');
$eventD->setOnSizeChangeHandler('onSizeChange');
$eventD->DispatchEvents();
