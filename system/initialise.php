<?
/**
 * init.php
 * initialise all general classes for both front and backend
 */
include_once('conf.php'); //system configuration
include_once(DIR_FS_CLASS . "Database.php");
include_once(DIR_FS_CLASS . "si/Safe.class.php");

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
