<?php
/**
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Stacey Walker, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
*/

// This file can take a long time to run, so needed to expand the timeout
set_time_limit(360);

include_once("system/initialise.php");
include_once("system/class/si/Uploader.class.php");
include_once("model/User.class.php");
include_once("function/shared.php");
include_once("function/core.php");

$uploaddir = DIR_FS_ROOT.'data/import';
$uploader = new Uploader();

// Check user is logged in before letting them do stuff (except logging in)
if (isset($_POST['userid'])) {
$user = new User($_POST['userid']);
} else {
    error('invalid user id passed.');
}

if (isset($_FILES['Filedata'])) {
    $file = $_FILES['Filedata'];
}

if ($assetId = $uploader->doCopyUpload($file, $user)) {
    //don't need to do anything
    exit;
} else {
    //return error to SWFUpload.
    header("HTTP/1.1 500 Internal Server Error");
}
