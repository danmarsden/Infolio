<?php

// This file is part of In-Folio - http://blog.in-folio.org.uk/blog/
//
// In-Folio is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// In-Folio is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with In-Folio.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 *
 * @author     Stacey Walker, Catalyst IT Ltd
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

// This file can take a long time to run, so needed to expand the timeout
set_time_limit(360);

include_once("system/initialise.php");
include_once("system/class/si/Uploader.class.php");
include_once("system/model/User.class.php");
include_once("system/model/Page.class.php");
include_once("function/shared.php");
include_once("function/core.php");

$uploaddir = DIR_FS_ROOT.'data/import';
$uploader = new Uploader();

$puid = Safe::post('userid');
$ppid = Safe::post('pageid');

// Check user is logged in before letting them do stuff (except logging in)
if (isset($puid)) {
$user = new User($puid);
} else {
    error('invalid user id passed.');
}
if (isset($_FILES['Filedata'])) {
    $file = $_FILES['Filedata'];
}

if (isset($ppid)) {
    $page = Page::GetPageById($ppid);
} else {
    $page = null;
}

if ($assetId = $uploader->doCopyUpload($file, $user, $page)) {
    //don't need to do anything
    exit;
} else {
    //return error to SWFUpload.
    header("HTTP/1.1 500 Internal Server Error");
}
