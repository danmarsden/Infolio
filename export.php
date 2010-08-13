<?

/**
 * export.php - Creates a static html version of a user's infolio
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2009 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: export.php 851 2010-01-07 11:33:48Z richard $
 * @link       NA
 * @since      NA
*/

// This file can take a long time to run, so needed to expand the timeout
set_time_limit(360);

$startTime = microtime(true);
function printTimeElapsed($timeLabel)
{
	global $startTime;
	print '<p><strong>' . $timeLabel . ':</strong> ' . (microtime(true) - $startTime) . '</p>';
}


include_once("system/initialise.php");
include_once("model/User.class.php");
include_once("function/shared.php");

// Check user is logged in before letting them do stuff (except logging in)
$adminUser = null;
session_start();
if( isset($_SESSION) ) {
	$adminUser = User::RetrieveBySessionData($_SESSION);

	// Nullify user if they don't have permission
	if( isset($adminUser) &&  !$adminUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN) ) {
		$adminUser = null;
	}
}

// Stop, if user not valid
if(!isset($adminUser) ) {
	die('Admin user not logged in');
}


// Take userID as input.
// Take tabIds as input, generate for them and then get them if they exist in users tabs

// Input: user_id
$studentUser = null;
if(isset($_POST['user_id']) && is_numeric($_POST['user_id']) )
{
	$userId = $_POST['user_id'];
	$studentUser = User::RetrieveById($userId, $adminUser);
}

if(!isset($studentUser)) {
	die("No user with that id (or no user_id provided)");
}

//check for existance of export dirs:
if (!is_dir("staticversion/export")) {
    mkdir("staticversion/export");
}
// Input: tab_id[0 -> (tab_count-1)]
$tabIds = array();
for($i=0; $i<$_POST['tab_count']; $i++)
{
	$tabLabel = 'tab_id'.$i;
	if(isset($_POST[$tabLabel])) {
		if(is_numeric($_POST[$tabLabel])) {
			array_push($tabIds, $_POST[$tabLabel]);
		}
		else {
			die("You have sent a bad tab id");
		}
	}
}

//set up Globals
$studentTheme = $studentUser->getTheme(); //used as Global by scripts
// Get user's tabs and assets
$userTabs = $studentUser->getTabs($tabIds);

//printTimeElapsed("Got tabs");

$userAssetCollection = $studentUser->getAssetCollection();//Asset::RetrieveUsersAssets($studentUser);
$userAssets = $userAssetCollection->getAssets();

if (!is_dir("staticversion/export/html")) {
    mkdir("staticversion/export/html");
}
if ($_POST['format'] == 'html') {
    include_once("export/html/lib.php");
    export_portfolio($studentUser, $tabIds);
    // Done so redirect
    header('Location: ' . $adminUser->getInstitution()->getUrl() . '/' . DIR_WS_ADMIN . '?do=' . SECTION_USER . '&a=edit&tab=export&id=' . $studentUser->getId() );

} else {
    include_once("export/leap/lib.php");
    include_once("export/leap/leaplib.php");

    export_portfolio($studentUser, $tabIds);
}


