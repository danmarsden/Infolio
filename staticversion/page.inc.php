<?php

/**
 * The code behind for page.php (static version))
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: page.inc.php 834 2009-12-23 10:24:45Z richard $
 * @link       NA
 * @since      NA
*/

//include_once('conf.php');
include_once("system/initialise.php");
include_once('_includes/ui.inc.php');
include_once('model/User.class.php');
include_once('model/Video.class.php');
include_once('class/Events/PageEventDispatcher.class.php');

$page = Page::GetPageById($pageId);
$page->setViewer($studentUser);

if( isset($page) ) {
	$tab = $studentUser->getTabByPage($page);
	$page->getJavaScriptVariables()->addVariable('phpTabPlace', $tab->getIndex());
	$pagingMenu = $tab->PagePagingmenu($page);
	$pagingMenu->setClass('inline-list');
}