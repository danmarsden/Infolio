<?php

/**
 * Asset Scroller
 *
 * HTML for asset scroller requested by AJAX
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: assetscroller.php 770 2009-08-14 11:20:37Z richard $
 * @link       NA
 * @since      NA
*/

include_once('../../initialise.php');
include_once('class/Events/PageEventDispatcher.class.php');


// Check user is logged in before letting them do stuff
$ignoreInstitutionUrl = true;
include_once('_includes/login.inc.php');

// Get assets for specific user
if(!isset($studentUser)) {
	die('User is not logged in');
}

$editBlockId = Safe::postWithDefault('blockId', 0);
$editPictureId = Safe::postWithDefault('pictureId', 0);

//$page = new Page();
$page = new SimplePage('Partial page');
$page->setHrefFromReferrer();

$linkCommands = array('blockedit'=>$editBlockId, 'a'=>PageEventDispatcher::ACTION_SET_IMAGE, 'imageedit'=>$editPictureId);
$filterCommands = array('blockedit'=>$editBlockId, 'imageedit'=>$editPictureId, 'mode'=>Page::MODE_EDIT);
$collection = $studentUser->getAssetCollection();

// Print the HTML for the asset scroller
print $studentTheme->SolidBox(
			$collection->HtmlThumbnails($page, $studentUser, AssetCollection::FILTER_ALL, $linkCommands, $filterCommands)
		);