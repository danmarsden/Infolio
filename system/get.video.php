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
 * Get image
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: get.video.php 821 2009-11-10 21:28:57Z richard $
*/
$lastModifiedDateString = 'Tue, 28 Apr 2009 10:13:11 GMT';


// Start off headers
header("Last-Modified: {$lastModifiedDateString}");

// If browser is asking, let it keep cached version
$requestHeaders = apache_request_headers();
if(isset($requestHeaders['If-Modified-Since'])) {
	header("HTTP/1.0 304 Not Modified");
	header('Cache-Control: public');
	header("Pragma: public");
	header('Expires: ');
	header('Content-Type: ');
	exit();
}

include_once('initialise.php');
include_once('model/Video.class.php');
include_once('model/User.class.php');

// Querystring input: id, size
$videoId = Safe::getWithDefault('id', null, PARAM_INT);
$videoSize = Safe::getWithDefault('size', Image::SIZE_ORIGINAL);


// Check user is logged in before letting them see pics (unless asking for password picture, or picture used for where page)
// ToDo: Double check these sizes can be applied to videos
if($videoSize != Image::SIZE_PHOTO_LOGIN && $videoSize != Image::SIZE_SMALL_BOX) {
	$ignoreInstitutionUrl = true;
	include_once('_includes/login.inc.php');
}
else {
	// Password pictures are a special case (ToDo: check this is the user's password picture)
	$studentUser = null;
}

// Check image id is a number
if(!is_numeric($videoId)) {
	print "Error: No video id";
	exit(0);
}

if($videoId > 0) {
	$video = Video::RetrieveById($videoId, $studentUser);
}


// Check image was made okay
if(!isset($video)) {
	$video = Image::GetPlaceHolder();
}

try {
	$videoFile = $video->getFilePath($videoSize);
}
catch (Exception $e) {
    print $e->getMessage();
	exit(0);
}

// Based on:
// http://uk.php.net/manual/en/function.header.php#74884

$mm_type = ($videoSize==Image::SIZE_TAB_ICON || $videoSize==Image::SIZE_THUMBNAIL) ? 'image/gif' : 'video/x-flv';

header("Cache-Control: public");
header("Content-Type: " . $mm_type);
header("Content-Length: " .(string)(filesize($videoFile)) );
header("Last-Modified: {$lastModifiedDateString}");
header('Expires: ');
header('Pragma: public');
header("Content-Transfer-Encoding: binary\n");

readfile($videoFile);