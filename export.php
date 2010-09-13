<?

/**
 * export.php - provides an export of Infolio
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2009 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
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
include_once("function/core.php");

// Check user is logged in before letting them do stuff (except logging in)
$adminUser = require_admin();

// Take userID as input.
// Take tabIds as input, generate for them and then get them if they exist in users tabs

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
$users = array();
if (!empty($_POST['siteexport'])) {
    //get list of all users.
    $sql = "SELECT id FROM user";
    $db = Database::getInstance();
    $result = $db->query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $users[] = User::RetrieveById($row['id'], $adminUser);
    }
    
} elseif (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
    $users[0] = User::RetrieveById($_POST['user_id'], $adminUser);
} else {
    die("No user with that id (or no user_id provided)");
}
if (!is_dir("staticversion/export/html")) {
    mkdir("staticversion/export/html");
}

$files = array();
foreach ($users as $user) {
    //set up Globals
    $studentTheme = $user->getTheme(); //used as Global by scripts
    // Get user's tabs and assets
    $userTabs = $user->getTabs($tabIds);

    $userAssetCollection = $user->getAssetCollection();//Asset::RetrieveUsersAssets($studentUser);
    $userAssets = $userAssetCollection->getAssets();

    if ($_POST['format'] == 'html') {
        $studentUser = $user; //used as a strange global.
        $collection = $userAssetCollection; //used as a strange global.
        include_once("export/html/lib.php");
        if (empty($_POST['siteexport'])) {
            export_portfolio($user, $tabIds);
            header('Location: ' . $adminUser->getInstitution()->getUrl() . '/' . DIR_WS_ADMIN . '?do=' . SECTION_USER . '&a=edit&tab=export&id=' . $user->getId() );
        } else {
            $files[] = export_portfolio($user, $tabIds, true);
        }
    } else {
        include_once("export/leap/lib.php");
        include_once("export/leap/leaplib.php");
        if (empty($_POST['siteexport'])) {
            export_portfolio($user, $tabIds);
        } else {
            $files[] = export_portfolio($user, $tabIds, true);
        }
    }
}
if (!empty($files)) {
    $zipfilename = 'data/export/site-export'. '-' . time() . '.zip';
    $zip = new ZipArchive();

    if ($zip->open($zipfilename, ZIPARCHIVE::CREATE)!==TRUE) {
        exit("cannot open <$zipfilename>\n");
    }
    foreach ($files as $file) {
        //hacky way to rename zip files in this new zip
        $newname = str_replace('data/export/', '', $file);
        $newname = str_replace('staticversion/', '', $newname);
        $zip->addFile($file, $newname);
    }
    $zip->close();
    //tidy up old files
    foreach ($files as $file) {
       unlink($file);
    }
    send_temp_file($zipfilename, 'infolio-site-export'. '-' . time() . '.zip');

}
