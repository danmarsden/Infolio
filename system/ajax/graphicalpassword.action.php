<?php
/**
 * Asset list JSON
 * Prints a list of a user's assets in JSON format.
 * The user must be logged in to get this data.
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: graphicalpassword.action.php 472 2009-03-19 19:57:21Z richard $
 * @link       NA
 * @since      NA
*/

include_once('../initialise.php');
include_once('model/User.class.php');
include_once('model/Image.class.php');

// Check user is logged in before letting them do stuff
$ignoreInstitutionUrl = true;
include_once('_includes/login.inc.php');

// Get assets for specific user
if(isset($studentUser)) {
	$passwordPictureId = Safe::postWithDefault('pictureId', null);
	$passwordSpotString = Safe::postWithDefault('spots', null);
	$passwordSpots = split(':', $passwordSpotString);

	// Put coords in array
	$coords = array();
	foreach($passwordSpots as $spot) {
		$spotCoords = split(',', $spot);
		$coords[] = array('x'=>$spotCoords[0], 'y'=>$spotCoords[1]);
	}
	
	$studentUser->setGraphicalLoginDetails($passwordPictureId, $coords);
}
else {
	// Get all users
	print 'User not logged in';
}