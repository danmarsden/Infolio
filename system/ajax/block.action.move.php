<?php
/**
 * Moves a block
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: block.action.move.php 775 2009-08-25 12:48:40Z richard $
 * @link       NA
 * @since      NA
*/

// Input
// page_id [uint]
// block_id [uint]
// dir [up|down]

include_once('../initialise.php');
include_once('model/Page.class.php');

// Check user is logged in before letting them do stuff
$ignoreInstitutionUrl = true;
include_once('_includes/login.inc.php');
if(!isset($studentUser)) {
	die('User not logged in');
}

// Get input
$pageId = Safe::GetArrayIndexValueWithDefault($_POST, 'page_id', null);
$blockId = Safe::GetArrayIndexValueWithDefault($_POST, 'block_id', null);
$direction = Safe::GetArrayIndexValueWithDefault($_POST, 'dir', null);

if( !(isset($pageId) && isset($blockId) && isset($direction)) ) {
	die('Missing input');
}
elseif(!(is_numeric($pageId) && is_numeric($blockId) && ($direction=='up' || $direction=='down'))) {
	die('Bad input');
}

// Get page
$page = Page::GetPageById($pageId);

// MOve block up or down
switch($direction) {
	case 'up':
		$page->MoveBlockUp($blockId);
		break;
	case 'down':
		$page->MoveBlockDown($blockId);
		break;
	default:
		die('Bad direction ' + $direction);
}

// Success
print '1';