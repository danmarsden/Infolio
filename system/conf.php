<?
//phpinfo();
/**
 * Conf
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Elvir Leonard	
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: conf.php 821 2009-11-10 21:28:57Z richard $
 * @link       NA
 * @since      NA
 * GENERAL SETUP
 * Define the webserver and path parameters
 * Conventions for:
 * - Date format for output and input
 * - Max file size for upload
 * - Character Set
 * - Email address
 * - Super Admin
 * - Language
 * - Log
 * - Meta tags
 * - Etc (todo)
 * 
 * SERVER PATH
 * - DIR_FS_* = Filesystem directories (local/physical)
 * - DIR_WS_* = Webserver directories (virtual/URL)
 * - DOMAIN_NAME = eg, http://localhost - should not be empty for productive servers
 * - HTTP_SERVER = 
 * - DIR_FS_ROOT = where the pages are located on the server
 * - DIR_WS_ADMIN = 
 * - DIR_FS_ADMIN = absolute path required
 * - DIR_WS_CATALOG = 
 * 
 * MYSQL SETTING
 * - DB_SERVER = 
 * - DB_SERVER_USERNAME = 
 * - DB_SERVER_PASSWORD = 
 * - DB_DATABASE = 
 * - USE_PCONNECT = // use persisstent connections?
 * - STORE_SESSIONS = leave empty "" for default handler or set to "mysql"
 * 
 */

		include('class/Debugger.class.php');


		/*
		// Debug settings
		$debugg = array();
		$debug['DEBUG_LEVEL'] = Debugger::LEVEL_INFO;

		*/

 
		//SERVER PATH
		define('DIR_FS_ROOT', '/home/iamorg/public_html/');

		//DATABASE CONF
		define('DB_SERVER', 'localhost');
		define('DB_SERVER_USERNAME', 'iamorg_infolio');
		define('DB_SERVER_PASSWORD', 'test99');
		define('DB_DATABASE', 'iamorg_infolio');
		
		// Image Magick -- Linux chars
		define('IM_NO_BOUNDS', '^');
		define('IM_OPEN_BRACKET', '\(');
		define('IM_CLOSE_BRACKET', '\)');


define("DIR_WS_ADMIN", 				"admin/");
define("DIR_WS_ADMIN_LOGIN",		"admin/login/");
define("DIR_WS_ADMIN_LOGOUT",		"admin/logout/");

// DATA  FOLDER
define('DIR_WS_DATA', '/data/');
define('DIR_FS_DATA', DIR_FS_ROOT . 'data/');
define('DIR_FS_DATA_UPLOAD', '/upload/');
define('DIR_WS_DATA_UPLOAD', '/upload/');

# - DONT CHANGE THIS
define("DIR_WS_IMAGES", 		"/_images/bo/");
define("DIR_WS_SYSTEM", 		"/system/");
define("DIR_WS_FUNCTION", 		DIR_WS_SYSTEM . "function/");
define("DIR_WS_CLASS",	 		DIR_WS_SYSTEM . "class/");
define("DIR_WS_MODULE", 		DIR_WS_SYSTEM . "module/");
define("DIR_WS_TMP", 			DIR_WS_SYSTEM . "tmp/");
define("DIR_WS_TEMPLATE", 		DIR_WS_SYSTEM . "template/");
define("DIR_WS_FRAMEWORK", 		DIR_WS_SYSTEM . "framework/");	
define("DIR_WS_PEAR", 			DIR_WS_FRAMEWORK . "pear/");
define("DIR_WS_JAVASCRIPT", 	DIR_WS_SYSTEM . "js/");
define("DIR_WS_CACHE", 			"/cache/");
define("DIR_WS_ICON", 			DIR_WS_IMAGES . "icon/");	

define("DIR_FS_CACHE", 			DIR_FS_ROOT . "cache/");
define("DIR_FS_IMAGES", 		DIR_FS_ROOT . "_images/bo/");
define("DIR_FS_ROOT_IMAGES", 	DIR_FS_ROOT . "_images/bo");
define("DIR_FS_SYSTEM", 		DIR_FS_ROOT . "system/");
define("DIR_FS_FUNCTION", 		DIR_FS_SYSTEM . "function/");
define("DIR_FS_CLASS",	 		DIR_FS_SYSTEM . "class/");
define("DIR_FS_MODEL",	 		DIR_FS_SYSTEM . "model/");
define("DIR_FS_MODULE", 		DIR_FS_SYSTEM . "module/");
define("DIR_FS_TMP", 			DIR_FS_SYSTEM . "tmp/");
define("DIR_FS_FRAMEWORK", 		DIR_FS_SYSTEM . "framework/");	
define("DIR_FS_TEMPLATE", 		DIR_FS_SYSTEM . "template/");
define("DIR_FS_PEAR", 			DIR_FS_FRAMEWORK . "pear/");
define("DIR_FS_JAVASCRIPT", 	DIR_FS_SYSTEM . "js/");
define("DIR_FS_ICON", 			DIR_FS_IMAGES . "icon/");	
	
define("DIR_WS_JAVASCRIPT_DEPENDENCIES", 	DIR_WS_JAVASCRIPT . "dependencies/");
define("DIR_WS_JQUERY_FRAMEWORK_ROOT", 		DIR_WS_JAVASCRIPT . "jquery/");

define("AJAX_DISPATCHER", 		"/admin/ajax.dispatcher.php");
	

# - UPLOAD SIZE
define("MAX_FILE_SIZE",			10000000);

# - EMAIL
define("ORIGIN_EMAIL_NAME", 	"The RixCentre");
define("ORIGIN_EMAIL_ADDRESS",	"info@rixcentre.org");

# - ###########################################################################################
# - ####################################### BACKOFFICE ########################################
# - ###########################################################################################	
# - FILENAMES
define("BACKOFFICE_LOGIN_FILENAME", 	"admin-login.html");	//use for aliasing (refer to .htaccess)
define("BACKOFFICE_LOGOUT_FILENAME", 	"admin-logout.html");  	

# - REDIRECTION
define("REDIRECTION_CAPTION",			"You will be redirected to the new location in 2 seconds. If this fails, please click");
define("REDIRECTION_TIME", 				"1000");	//1000=1 sec

# - SPLIT PAGE
define("DISPLAY_PER_PAGE",				"10,20,30,40,50,75,100");
define("MINIMUM_DISPLAY_PER_PAGE",		"10"); 	//must be the same with the first number in DISPLAY_PER_PAGE constant
define("MAXIMUM_DISPLAY_PER_PAGE",		"100");

# - OTHER
define('VIDEO_TYPE','mpg,mpeg,avi,mov,flv');
define('IMAGE_TYPE','png,bmp,jpg,jpeg,gif');
define('FLASH_AUDIO_PLAYER', DIR_WS_IMAGES . 'listen_button.swf');	
define('FLASH_VIDEO_PLAYER', DIR_WS_IMAGES . 'flvplayer.swf');	
define('ENCRYPTION_KEY', 'rixcentreROCKS');	
define('BREADCRUMB_SEPARATOR',' &raquo; ');	
define('TEXT_FIELD_REQUIRED', 	'<span class="requiredContainer">*</span>');

# - ###########################################################################################
# - ####################################### FRONT END #########################################
# - ###########################################################################################	
# - META
define('DEFAULT_META_TITLE', 'In-Folio');
define('DEFAULT_META_KEYWORD', 'In-Folio');
define('DEFAULT_META_DESCRIPTION', 'In-Folio');
define('DEFAULT_META_AUTHOR', 'In-Folio');
define('DEFAULT_META_CONTENT_TYPE', 'text/html; charset=utf-8');	
define('URL_FRIENDLY_REPLACEMENT', '-');



/**
 * all settings goes in here
 */
date_default_timezone_set('Europe/London');
?>
