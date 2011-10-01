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
 * The code behind for collection.php
 *
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: collection.inc.php 830 2009-12-21 16:27:33Z richard $
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