<?php

/**
 * The code behind tabs.php
 *
 * LICENSE: This is an Open Source Project
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
$eventD = new ManageTabsEventDispatcher($_GET, $_POST);
$eventD->setUser($studentUser);
$eventD->setOnColourSwapHandler('onColourSwap');
$eventD->setOnEnterShowManageTabsHandler('onEnterShowManageTabs');
$eventD->setOnMoveTabHandler('onMoveTab');
$eventD->setOnSizeChangeHandler('onSizeChange');
$eventD->DispatchEvents();
