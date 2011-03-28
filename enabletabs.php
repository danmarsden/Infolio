<?

/**
 * enabletabs.php - enables and disables users tabs
 *
 * LICENSE: This is an Open Source Project
 *
*/

include_once("system/initialise.php");
include_once("model/User.class.php");
include_once("model/Tab.class.php");

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
$puid = Safe::post('user_id', PARAM_INT);
if(isset($puid))
{
	$userId = $puid;
	$studentUser = User::RetrieveById($userId);
}

if(!isset($studentUser)) {
	die("No user with that id (or no user_id provided)");
}
$pshare = Safe::post('share', PARAM_INT);
if (isset($pshare)) {
    $studentUser->setShare($pshare);
    $studentUser->save($adminUser);
}
$pt = Safe::post('tab_count', PARAM_INT);
// Input: tab_id[0 -> (tab_count-1)]
for($i=1; $i<$pt; $i++)
{
    $tabLabel = 'tab_id'.$i;
    $ptl = Safe::post($tabLabel);
    if(isset($ptl)) {
        $keys = explode('_', $ptl);
        if ($tab = Tab::GetTabById((int)$keys[1])) {
            $enabled = $keys[0] === 'enabled' ? true : false;
            if ($enabled) {
                $tab->Restore($adminUser);
            } else {
                $tab->Delete($adminUser);
            }
		}
		else {
			die("You have sent a bad tab id");
        }
	}
}

//set up Globals
$studentTheme = $studentUser->getTheme(); //used as Global by scripts

header('Location: ' . $adminUser->getInstitution()->getUrl() . '/' . DIR_WS_ADMIN . '?do=' . SECTION_USER . '&a=edit&tab=tabs&id=' . $studentUser->getId() );
