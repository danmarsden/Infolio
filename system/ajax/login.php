<?php

/**
 * For logging in via ajax or flash
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: login.php 288 2008-12-06 18:11:33Z richard $
 * @link       NA
 * @since      NA
*/          

include_once('../initialise.php');
include_once('class/si/Safe.class.php');

// Constants
define('ERROR_NONE', 'ok');
define('ERROR_INVALID_USER', 'invalid user');
define('ERROR_PASSWORD_FAIL', 'password fail');
define('ERROR_ACCOUNT_LOCKOUT', 'account lockout');
define('ERROR_TYPE_LOCKOUT', 'type lockout');

define('TYPE_FULL_AUTO', 'full_auto');
define('TYPE_SUB_SYMBOL', 'sub_symbol');
define('TYPE_SUB_PHOTO', 'sub_photo');
define('TYPE_PHOTO_INFO', 'photo_info');

define('CONFUSE_PASSWORD_PICTURE_1', 2875250003);
define('CONFUSE_PASSWORD_PICTURE_2', 3300453929);

// Get post variables
$flash_user_name = Safe::postWithDefault('user_name', null);
$flash_data_type = Safe::postWithDefault('data_type', null);
$flash_sub_pass = Safe::postWithDefault('sub_pass', null);

Debugger::debug("Ajax", 'login.php::main_1', Debugger::LEVEL_INFO);

//mock userbase
switch ($flash_user_name)
{
	case 'Fred':
	    $id = 1;
	    $password_md5 = "bcbfbbcdsdf87sdfh";
	    /*$photo_password = 8625752184; // 3 clicks on Porsche Badge
	    $click_accuracy = 5;
	    $photo_url = "password_images/cars.png";
	    $photo_lockstatus = 0;*/
	    $symbol_password = 31141710363;  // red cube + footprints
	    $symbol_lockstatus = 0;
	    $account_lockout = 0;
	    $last_incorrect_timestamp = 0;
	    $incorrect_logins = 0;
	    break;
	case 'Jenny':
	    $id = 2;
	    $password_md5 = "bcbfbbcdsdf87sdfh";
	    $photo_password = 5750501915;  // click Kian's elbow tips
	    $click_accuracy = 20;
	    $photo_url = "password_images/westlife.png";
	    $photo_lockstatus = 0;
	    $symbol_password = 49986017269; // blue star + elephant
	    $symbol_lockstatus = 0;
	    $account_lockout = 0;
	    $lockout_timestamp = 0;
	    $incorrect_logins = 0;
	    break;
	    
	case 'Bill':
	    $id = 3;
	    $password_md5 = "bcbfbbcdsdf87sdfh";
	    $photo_password = 17251503275; // click all the noses
	    $click_accuracy = 10;
	    $photo_url = "password_images/party.png";
	    $photo_lockstatus = 0;
	    $symbol_password = 10620174288;  //orange prism + fairground        
	    $symbol_lockstatus = 0;
	    $account_lockout = 0;
	    $lockout_timestamp = 0;
	    $incorrect_logins = 0;
	    break;
	
	case 'Simon' :
	    $id = 4;
	    $password_md5 = "bcbfbbcdsdf87sdfh";
	    $photo_password = 17251503275; // click all the nose tips
	    $click_accuracy = 10;
	    $photo_url = "password_images/party.png";
	    $photo_lockstatus = 1;
	    $symbol_password = -1;      
	    $symbol_lockstatus = 1;
	    $account_lockout = 1;
	    $lockout_timestamp = 0;
	    $incorrect_logins = 10;
	    break;
	 
	case 'Gary':
	    $id = 5;
	    $password_md5 = "bcbfbbcdsdf87sdfh";
	    $photo_password = 17251502284; // 3 clicks on bass drum, 3 clicks onaudience member fist in air 
	    $click_accuracy = 1;
	    $photo_url = "password_images/crowd.png";
	    $photo_lockstatus = 0;
	    $symbol_password = -1;       
	    $symbol_lockstatus = 1;
	    $account_lockout = 0;
	    $lockout_timestamp = 0;
	    $incorrect_logins = 0;
	    break;   
	    
	default:
	    errorMessage("invalid user");  
	    exit;
	    break;
}


if ($account_lockout){
    login(ERROR_ACCOUNT_LOCKOUT);
    exit;
}


switch ($flash_data_type)
{
	case TYPE_PHOTO_INFO:
		if ($photo_lockstatus){
			login(ERROR_TYPE_LOCKOUT);
		}
		else {
			print "photo_url=".$photo_url;     
		}
		break;
	case TYPE_FULL_AUTO:
		if  ($flash_sub_pass==$password_md5){
			login(ERROR_NONE); 
		}
		else {
			login(ERROR_PASSWORD_FAIL); 
		}    
		break;
	case TYPE_SUB_SYMBOL:
		if ($symbol_lockstatus){
			login(ERROR_TYPE_LOCKOUT);
		}
		else if  ($flash_sub_pass==$symbol_password){
			login(ERROR_NONE);
		}
		else {
			login(ERROR_PASSWORD_FAIL);
		}      
        break;
	case TYPE_SUB_PHOTO:
		$number_of_clicks = intval($photo_password/2875250003);
		$click_accuracy*=$number_of_clicks;
    	if  (($flash_sub_pass >= $photo_password-$click_accuracy) && ($flash_sub_pass <= $photo_password+$click_accuracy)){
			login(ERROR_NONE);
		}
		else {
			login(ERROR_PASSWORD_FAIL);
        }
		break;
	default:
		errorMessage("unknown");
		break;
}

function login ($status)
{
	if($status == ERROR_NONE){
		print "login_success=true";
	}
	else {
		errorMessage($status);
	}
}


function errorMessage($error_type)
{
	global $flash_user_name, $flash_sub_pass;
	
	$errors = array(
					ERROR_NONE => "Correct Login.",
					ERROR_INVALID_USER => "Check your spelling.",
					ERROR_PASSWORD_FAIL => "Password is wrong.",
					ERROR_ACCOUNT_LOCKOUT => "You are locked out.",
					ERROR_TYPE_LOCKOUT => "You cannot login this way."
					);
	$error_msg = isset($errors[$error_type]) ? $errors[$error_type] : 'Unknown Server Issue.';

    print "error={$error_type}&error_msg={$error_msg} ({$flash_user_name} - {$flash_sub_pass})";
    exit;
}