<?php

/**
 * Ajax for Flash login
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: login.php 684 2009-07-08 11:00:01Z richard $
 * @link       NA
 * @since      NA
*/

// Make sure the session is started
if(!isset($_SESSION)) {
	session_start();
}

include_once('../../initialise.php');
include_once('model/User.class.php');
include_once('class/Logger.class.php');

$flashUserName = $_REQUEST['user_name'];
$flash_data_type = $_REQUEST['data_type'];



/* Todo: check for account logout
 * if ($account_lockout){
    errorMessage("account lockout");  
    exit;   
}*/


switch ($flash_data_type) {
	case 'sub_photo':
		$flash_photo_password = $_REQUEST['sub_pass'];
		$institutionId = Safe::Input($_REQUEST['institution_id']);
		$institution = new Institution($institutionId);
		if ( User::CheckFlashPhotoCoordVal($flash_photo_password, $flashUserName, $institution) ) {
			print "login_success=true";
		}
		else {
			errorMessage("password fail"); 
		}
		break;

	case 'photo_info':
	default:
		//TODO: Remove default action by modifying flash
		if(isset($_REQUEST['institution_id'])) {
			$institutionId = Safe::Input($_REQUEST['institution_id']);
			$institution = new Institution($institutionId);
			print User::RetrieveFlashLoginStuffForUser($flashUserName, $institution);
		}
		else {
			// No institute means they must choose where they are from.
			print("No institution name provided");
		}
		
		
		break;
	/*case "full_auto":
		if ($flash_sub_pass==$password_md5) {
			print "login_success=true"; 
		}
		else {
			errorMessage("password fail"); 
		}    
		break;

	case "sub_symbol":
		if ($symbol_lockstatus){
			errorMessage("type lockout");  
		}
		else if  ($flash_sub_pass==$symbol_password){
			print "login_success=true";
		}
		else {
			errorMessage("password fail"); 
		}    
        break;

	

	default:
		errorMessage("unknown type");
		break;  */                  
}  


/* ** ** functions ** ** */

function errorMessage($error_type)
{
    switch ($error_type)
    {
    case "debug" :
        $error_msg = "Correct Login.";
        break;
    case "invalid user" :
        $error_msg = "Check your spelling.";
        break;
    case "password fail" :
        $error_msg = "Password is wrong.";
        break;
    case "account lockout" :
        $error_msg = "You are locked out.";
        break;
    case "type lockout" :
         $error_msg = "You cannot login this way.";
    break;
    default:
        $error_msg = "Unknown Server Issue.";
        break;
    }
    print "error=$error_type&error_msg=$error_msg";
    exit;
}

 

// Cut out and scale JPEG/GIF/PNG to absolute size - outputs PNG
function imageCutToSize($name, $newname, $width=700, $height=400){
global $gd2;

$system =explode(".",$name);
if (preg_match("/jpg|jpeg|jpe/", strtolower($system[1])))         {$src_img  =imagecreatefromjpeg($name);}
else if (preg_match("/png/", strtolower($system[1])))             {$src_img  =imagecreatefrompng($name);}
else if (preg_match("/gif/", strtolower($system[1])))             {$src_img  =imagecreatefromgif($name);}
else {return;}

  $up_x=imageSX($src_img);
  $up_y=imageSY($src_img);
  $up_ratio=$up_y/$up_x;
 
                $targetratio=$height/$width;
                $interx= $width*4;
                $intery=$height*4;
                      if ($up_ratio>= $targetratio) { //Too Tall
                      $yscale=($interx/$up_x);
                      $temp_h=intval($up_y*$yscale);
                      $temp_img=ImageCreateTrueColor($interx,$temp_h);
                      imagecopyresampled($temp_img, $src_img, 0, 0, 0, 0, $interx, $temp_h, $up_x, $up_y);
                      $cookiecuttop=intval(($temp_h- $intery)/4);
                      $cutout=ImageCreateTrueColor($interx, $intery);
                      imagecopy ($cutout, $temp_img, 0, 0, 0, $cookiecuttop, $interx,  $intery );
                      imagedestroy($temp_img);
                      $src_img=$cutout;                  }
                              else                                   { //Too Wide or OK
                              $xscale=($intery/$up_y);
                              $temp_w=intval($up_x*$xscale);
                              $temp_img=ImageCreateTrueColor($temp_w, $intery);
                              imagecopyresampled($temp_img, $src_img, 0, 0, 0, 0, $temp_w,  $intery, $up_x, $up_y);
                              $cookiecutleft=intval(($temp_w-$interx)/2);
                              $cutout=ImageCreateTrueColor($interx, $intery);
                              imagecopy ($cutout, $temp_img, 0, 0, $cookiecutleft, 0 , $interx,  $intery );
                              imagedestroy($temp_img);
                              $src_img=$cutout;    
                                          }
                $old_x=imageSX($src_img); //Scale to requested width
                $old_y=imageSY($src_img);
                $dst_img=ImageCreateTrueColor($width,$height);
                imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0,$width,$height, $old_x, $old_y);
            

            imagepng($dst_img, $newname.".png"); 
            imagedestroy($dst_img);
}