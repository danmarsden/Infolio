<?php

include_once('initialise.php');
include_once('model/PageBlock.class.php');
include_once('model/Page.class.php');
include_once('model/Tab.class.php');
include_once('model/User.class.php');

// Check user is logged in before letting them do stuff
$ignoreInstitutionUrl = true;
include_once('_includes/login.inc.php');

// Querystring input: blockid
$blockId = Safe::GetArrayIndexValueWithDefault($_GET, 'blockid', null);

if(is_numeric($blockId)) {
	$block = PageBlock::RetrieveById($blockId);
	$soundFile = $block->TextToSpeech();
}
elseif($blockId == 'home') {
	$soundFile = $studentUser->SpeechUserDetails();
}
else {
	print "Error: No block id";
	exit(0);
}

// Based on:
// http://uk.php.net/manual/en/function.header.php#74884

//$soundFile = "C:/xampp/htdocs/data/_voicedtext/39-1240883190.wav"; // test file
$mm_type="audio/mpeg";

header("Cache-Control: public, must-revalidate");
header("Content-Type: " . $mm_type);
header("Content-Length: " .(string)(filesize($soundFile)) );
header("Content-Transfer-Encoding: binary\n");

readfile($soundFile);