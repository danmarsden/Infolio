<?php

/**
 * The code behind for collection.php
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: collection.inc.php 830 2009-12-21 16:27:33Z richard $
 * @link       NA
 * @since      NA
*/

include_once("system/initialise.php");
include_once('class/si/Link.class.php');
include_once('class/si/Menu.class.php');
include_once('class/si/Theme.class.php');
include_once('class/si/SimplePage.class.php');
include_once('model/User.class.php');
include_once('model/Video.class.php');



// Make sure they're logged in
//include('_includes/login.inc.php');

/* ** Event handlers ** */
//include_once('_includes/accessibility-toolbar-button-event.functions.php');


$page = new SimplePage($studentUser->getFirstName() . "'s collection");
$page->setName('Collection');
$page->getJavaScriptVariables()->addVariable('phpTabPlace', 1);
	
$assetFilter = AssetCollection::FILTER_ALL;
$collection = $studentUser->getAssetCollection();
//$collection->setSelectedAsset(null, $assetFilter);