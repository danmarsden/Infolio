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
 * Get export zip
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: get.exportzip.php 850 2010-01-07 11:25:30Z richard $
 * @link       NA
 * @since      NA
*/

include_once('initialise.php');
include_once('class/Date.class.php');

$profileExportFile = DIR_FS_ROOT . 'staticversion/user-' . Safe::getWithDefault('user_id', '') . '.zip';

if(file_exists($profileExportFile))
{
	$lastModifiedDate = filemtime($profileExportFile);
	$lastModifiedDateString = Date::formatForInternet($lastModifiedDate);
}
else {
	header("HTTP/1.0 404 Not Found");
	header('Cache-Control: public');
	header("Pragma: public");
	header('Expires: ');
	header('Content-Type: ');
	print('Not found');
	exit();
}

// Start off headers
header("Last-Modified: {$lastModifiedDateString}");

// If browser is asking, check if they can use cached version
$requestHeaders = apache_request_headers();
if(isset($requestHeaders['If-Modified-Since'])) {
	$clientModifiedSinceDate = strtotime($requestHeaders['If-Modified-Since']);

	//print("<p>{$lastModifiedDate} <= {$clientModifiedSinceDate}</p>");
	if($lastModifiedDate <= $clientModifiedSinceDate)
	{
		echo("Sending 304.");
		header("HTTP/1.0 304 Not Modified");
		header('Cache-Control: public');
		header("Pragma: public");
		header('Expires: ');
		header('Content-Type: ');
		exit();
	}
}

include_once('model/User.class.php');


// Check user has rights
$ignoreInstitutionUrl = true;
include_once('_includes/login.inc.php');

// Check this user has ADMIN rights
if(! $studentUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN))
{
	// TODO: Check for more specific rights. Does this user have rights over this infolio export
	die("You can't download this");
}


// Based on:
// http://uk.php.net/manual/en/function.header.php#74884

$mm_type = 'application/zip';

header("Cache-Control: public");
header("Content-Type: " . $mm_type);
header("Content-Length: " .(string)(filesize($profileExportFile)) );
header("Last-Modified: {$lastModifiedDateString}");
header('Expires: ');
header('Pragma: public');
header('Content-Disposition: attachment; filename="export.zip"');
header("Content-Transfer-Encoding: binary\n");

readfile($profileExportFile);