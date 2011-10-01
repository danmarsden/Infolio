<?php



/**
 * Get image
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: get.image.php 699 2009-07-13 22:07:46Z richard $
 * @link       NA
 * @since      NA
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
include_once('model/Image.class.php');
include_once('model/User.class.php');

// Querystring input: id, size
$imageId = Safe::getWithDefault('id', null, PARAM_INT);
$imageSize = Safe::getWithDefault('size', Image::SIZE_ORIGINAL);



// Check user is logged in before letting them see pics (unless asking for password picture, or picture used for where page)
if($imageSize != Image::SIZE_PHOTO_LOGIN && $imageSize != Image::SIZE_SMALL_BOX) {
	$ignoreInstitutionUrl = true;
	include_once('_includes/login.inc.php');
}
else {
	// Password pictures are a special case (ToDo: check this is the user's password picture)
	$studentUser = null;
}

// Check image id is a number
if(!is_numeric($imageId)) {
	print "Error: No image id";
	exit(0);
}

if($imageId > 0) {
	$image = Image::RetrieveById($imageId, $studentUser);
}
// Audio file code: -1
elseif($imageId == -1) {
	$image = Image::GetAudioPlaceHolder();
}
// Placeholder code: 0 (But use catchall to make general placeholder)

// Check image was made okay
if(!isset($image)) {
	$image = Image::GetPlaceHolder();
}

try {
	$imageFile = $image->getFilePath($imageSize);
}
catch (Exception $e) {
    print $e->getMessage();
	exit(0);
}

// Based on:
// http://uk.php.net/manual/en/function.header.php#74884

//$imageFile = 'C:\xampp\htdocs\data\rix-asset\image\Santorini_1000.jpg'; // test file
$mm_type="image/jpeg";

header("Cache-Control: public");
header("Content-Type: " . $mm_type);
header("Content-Length: " .(string)(filesize($imageFile)) );
header("Last-Modified: {$lastModifiedDateString}");
header('Expires: ');
header('Pragma: public');
header("Content-Transfer-Encoding: binary\n");

readfile($imageFile);