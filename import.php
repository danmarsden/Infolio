<?

/**
 * import.php - import of Infolio Leap file.
 *
 * @author     Dan Marsden, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
*/

// This file can take a long time to run, so needed to expand the timeout
set_time_limit(360);

include_once("system/initialise.php");
include_once("model/User.class.php");
include_once("function/shared.php");
include_once("function/core.php");

$uploaddir = 'data/import';

// Check user is logged in before letting them do stuff (except logging in)
$adminUser = require_admin();

if (empty($_FILES['leapimport'])) {
    error("please select a LEAP export file to submit");
}
$file = $_FILES['leapimport'];
//now check if the user has submitted a zip file.
if (substr(strrchr($file['name'], '.'), 1) !== 'zip') {
    error("Invalid LEAP export provided");
}
if (!is_dir($uploaddir)) {
    mkdir($uploaddir);
}

//now extract files.
$zip = new ZipArchive();
if (!$zip->open($file['tmp_name'])) {
    error("couldn't open zip");
}
$uploaddir .= '/'.time();
if (!is_dir($uploaddir)) {
    mkdir($uploaddir);
}
$zip->extractTo($uploaddir);

//if this is a site export, unzip each item into it's own dir and run import
if ($_POST['type'] =='site') {
    $objects = scandir($uploaddir);
    foreach ($objects as $object)
        if ($object != "." && $object != "..") {
            //TODO: handle any site level files here - like site groups or site data.
            if (filetype($uploaddir."/".$object) == "file" && substr(strrchr($object, '.'), 1) == 'zip') {
                $zip = new ZipArchive();
                if (!$zip->open($uploaddir."/".$object)) {
                    error("couldn't open zip");
                }
                $newdir = $uploaddir.'/'.substr($object, 0, -4);
                if (!is_dir($newdir)) {
                    mkdir($newdir);
                }
                $zip->extractTo($newdir);
                //delete original zip
                unlink($uploaddir."/".$object);
                //now trigger import of this folder
                leap_restore_user($newdir);
                
                //Delete directory as no longer needed.
                delete_dir_recursive($newdir);
            }
        } 
} else {
    
} 


//now delete old directory.
delete_dir_recursive($uploaddir);


//function to restore an individual user leap export
function leap_restore_user($dir, $user = '') {
    //check if valid Leap export dir
    if (!file_exists($dir.'/leap2a.xml')) {
        notify('Invalid export: '.$dir. ' doesn\'t contain a valid leap export');
        return false;
    }
    //parse xml file
    $xml = simplexml_load_file($dir.'/leap2a.xml');
    print_object($xml);
}