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
 * init.php
 * initialise all general classes for both front and backend
 */
include_once('conf.php'); //system configuration
include_once(DIR_FS_CLASS . "Database.php");
include_once(DIR_FS_CLASS . "si/Safe.class.php");
include_once(DIR_FS_FUNCTION ."core.php");

if (get_magic_quotes_gpc()) {
    error("Site Error: magic_quotes_gpc(PHP setting) must be disabled.");
}

$db = Database::getInstance();
if( substr(PHP_OS,0,3) == 'WIN' ){
	ini_set('include_path', '.;' . DIR_FS_ROOT . ';' . DIR_FS_SYSTEM);
}else{
	ini_set('include_path', '.:' . DIR_FS_ROOT . ':' . DIR_FS_SYSTEM);
}

// Set up exceptions for old style PHP errors
function errorHandler($errno, $errstr, $errfile, $errline) {
	if(strstr($_SERVER["REQUEST_URI"], "admin")===false && strstr($_SERVER["REQUEST_URI"], "ajax")===false) {
		throw new Exception($errstr, $errno);
	}
	else {
		// Backend dies with exceptions turned on ( need better error handling strategy)
		// Log message as debug error
		Debugger::debug("[Error $errno] $errfile at line $errline: $errstr", 'Error $errno', Debugger::LEVEL_ERROR);
	}
}

// SET UP help system constants
define("HELP_LOGIN", 0);
define("HELP_ABOUT_ME", 1);
define("HELP__TABS", 2);
define("HELP_PAGES", 3);
define("HELP_COLLECTION", 4);
define("HELP_SETTINGS", 5);

// Set up Admin constants
define('SECTION_TEMPLATE', 1);
define('SECTION_USER', 6);
define('SECTION_GROUP', 4);
define('SECTION_LOG_VIEWER', 5);
define('SECTION_INSTITUTION', 7);
define('SECTION_ASSET', 8);
define('SECTION_UPLOADED_ASSET', 9);
define('SECTION_UPLOAD_MANAGER', 10);
define('SECTION_SITEEXPORT', 11);
define('SECTION_LEAPIMPORT', 12);

$allowedExt = 'avi,mp4,mpeg,mpg,flv,mov,wmv,mp3,wav.bmp,jpg,png,gif,jpeg,tif,tiff,pps,pdf,iso,flv';
$allowedExt .= 'xls,doc,docx,zip,txt,lit,rt,odm,msg,rmvb,mkv,nds,nes,pdp,iwd,p3t,rar,arc,boo,car,alz';
$allowedExt .= 'odt,ods,odf,docm,dotx,dotm,xlsx,xlsm,xltx,xltm,xlsb,xlam,pptx,pptm,potx,potm,ppam,ppsx,ppsm,sldx,sldm,thmx';

define('EXT_WHITELIST', $allowedExt);

global $ALLOWED_TAGS;
$ALLOWED_TAGS =
'<p><br><b><i><u><font><table><tbody><thead><tfoot><span><div><tr><td><th><ol><ul><dl><li><dt><dd><h1><h2><h3><h4><h5><h6><hr><img><a><strong><emphasis><em><sup><sub><address><cite><blockquote><pre><strike><param><acronym><nolink><lang><tex><algebra><math><mi><mn><mo><mtext><mspace><ms><mrow><mfrac><msqrt><mroot><mstyle><merror><mpadded><mphantom><mfenced><msub><msup><msubsup><munder><mover><munderover><mmultiscripts><mtable><mtr><mtd><maligngroup><malignmark><maction><cn><ci><apply><reln><fn><interval><inverse><sep><condition><declare><lambda><compose><ident><quotient><exp><factorial><divide><max><min><minus><plus><power><rem><times><root><gcd><and><or><xor><not><implies><forall><exists><abs><conjugate><eq><neq><gt><lt><geq><leq><ln><log><int><diff><partialdiff><lowlimit><uplimit><bvar><degree><set><list><union><intersect><in><notin><subset><prsubset><notsubset><notprsubset><setdiff><sum><product><limit><tendsto><mean><sdev><variance><median><mode><moment><vector><matrix><matrixrow><determinant><transpose><selector><annotation><semantics><annotation-xml><tt><code>';
