<?php
// $Id: js.conf.php 554 2009-05-16 23:12:25Z richard $
?>var path={
<?php
	include("../../system/conf.php");
	echo "\"DIR_WS_ADMIN\":'" . DIR_WS_ADMIN . "',\n";
	echo "\"DIR_WS_ADMIN_LOGIN\":'" . DIR_WS_ADMIN_LOGIN . "',\n";
	echo "\"DIR_WS_ADMIN_LOGOUT\":'" . DIR_WS_ADMIN_LOGOUT . "',\n";
	echo "\"DIR_WS_DATA\":'" . DIR_WS_DATA . "',\n";
	echo "\"DIR_WS_SYSTEM\":\"" . DIR_WS_SYSTEM . "\",\n";
	echo "\"DIR_WS_FUNCTION\":\"" . DIR_WS_FUNCTION . "\",\n";
	echo "\"DIR_WS_CLASS\":\"" . DIR_WS_CLASS . "\",\n";
	echo "\"DIR_WS_JAVASCRIPT\":\"" . DIR_WS_JAVASCRIPT . "\",\n";
	echo "\"DIR_WS_JQUERY_FRAMEWORK_ROOT\":\"" . DIR_WS_JQUERY_FRAMEWORK_ROOT . "\",\n";
	echo "\"AJAX_DISPATCHER\":\"" . AJAX_DISPATCHER . "\",\n";
	echo "\"BACKOFFICE_LOGOUT_FILENAME\":\"" . BACKOFFICE_LOGOUT_FILENAME . "\",\n";
	echo "\"BACKOFFICE_LOGIN_FILENAME\":\"" . BACKOFFICE_LOGIN_FILENAME . "\"\n";
?>
};
var redirectionInMS=2000;