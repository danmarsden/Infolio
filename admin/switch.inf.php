<?php
/**
 * User list JSON
 * Prints a list of users in JSON format.
 * A admin user must be logged in to get this data.
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: switch.inf.php 784 2009-08-27 12:53:39Z richard $
 * @link       NA
 * @since      NA
*/

include_once('../system/initialiseBackOffice.php');
include_once('model/User.class.php');

// Check user is logged in before letting them do stuff
$adminUser = BackOffice::RetrieveAndCheckAjaxAdminUser($_SESSION);

// Check provided input
$studentUserId = Safe::GetArrayIndexValueWithDefault($_GET, 'id', null);
$includePasscode = isset($_GET['includepass']);
if($studentUserId == null) {
	die("Missing input.");
}

$studentUser = User::RetrieveById($studentUserId, $adminUser);
if($studentUser == null) {
	die ("Bad user");
}


// Set headers
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=switch.inf");
header("Content-Type: application/octet-stream");
header("Content-Transfer-Encoding: binary");


?><switchinfo>
	<userid><? print $studentUser->getId(); ?></userid>
	<efoliourl>http://www.clippi.com/system/ajax/flash/</efoliourl>
	<? if($includePasscode) { ?><passcode><? print $studentUser->getPermissionManager()->getSymbolLogin()->getShapePhotoNumber($studentUser->getId()); ?></passcode>
	<? } ?><scandelay>4</scandelay>
</switchinfo>