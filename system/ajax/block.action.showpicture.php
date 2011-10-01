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
 * Moves a block
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: block.action.showpicture.php 773 2009-08-24 20:20:17Z richard $
 * @link       NA
 * @since      NA
*/

// Input
// block_id [uint]
// picture_place [uint]
// picture_id [uint]

include_once('../initialise.php');
include_once('model/PageBlock.class.php');

// Check user is logged in before letting them do stuff
$ignoreInstitutionUrl = true;
include_once('_includes/login.inc.php');
if(!isset($studentUser)) {
	die('User not logged in');
}

// Get inputv
$blockId = Safe::postWithDefault('block_id', null, PARAM_INT);
$picturePlace = Safe::postWithDefault('picture_place', null, PARAM_INT);
$pictureId = Safe::postWithDefault('picture_id', null, PARAM_INT);

if( !(isset($blockId) && isset($picturePlace) && isset($pictureId)) ) {
	die('Missing input');
}
elseif(!(is_numeric($blockId) && is_numeric($picturePlace) && is_numeric($pictureId))) {
	die('Bad input');
}

$asset = Asset::RetrieveById($pictureId, $studentUser);

$page = new SimplePage('Partial page');
$page->setHrefFromReferrer();
			
// Add edit links

//$linkParams = array_merge(array('imageedit'=>$picturePlace), $otherLinks);
$linkParams = array('imageedit'=>$picturePlace);

$editIconLink = Link::CreateImageLink($studentTheme->Icon('edit2'), $page->PathWithQueryString($linkParams));
$editIconLink->addHtmlProperty('class', "btnIEdit p{$picturePlace}");
$asset->setEditLink($editIconLink);
$asset->addClass('edit');


print $asset->Html(Image::SIZE_BOX);


