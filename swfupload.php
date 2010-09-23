<?php
/**
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Stacey Walker, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
*/

include_once("system/initialise.php");
include_once("model/User.class.php");
include_once("function/shared.php");
include_once("function/core.php");

// Check user is logged in before letting them do stuff (except logging in)
$adminUser = require_admin();

$uploaddir = DIR_FS_ROOT.'/data/';
$uploadfile = $uploaddir . basename($_FILES['Filedata']['name']);

if (move_uploaded_file($_FILES['Filedata']['tmp_name'], $uploadfile)) {
    //don't need to do anything
    exit;
} else {
    //return error to SWFUpload.
    header("HTTP/1.1 500 Internal Server Error");
}
