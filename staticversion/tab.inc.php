<?php

/**
 * The code behind for tab.php (static version)
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: tab.inc.php 827 2009-12-18 16:36:47Z richard $
 * @link       NA
 * @since      NA
*/

//include_once('conf.php');
include_once("system/initialise.php");
include_once('_includes/ui.inc.php');
include_once('class/Events/TabEventDispatcher.class.php');



/* ** Setup ** */

$tab = $studentUser->getTabById($tabId, 'new');

$page = new SimplePage( $tab->getName() );
$page->setName($tab->getName());

// Static version won't have commands
$showAddPage = false;

$tab->setViewer($studentUser);
$page->getJavaScriptVariables()->addVariable('phpTabPlace', $tab->getIndex());


$sortMenu = null; $editMenu = null;