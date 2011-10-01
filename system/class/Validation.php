<?php
/**
 * Class Validation
 * 
 * LICENSE: This is an Open Source Project
 * 
 * Class Notification
 * stackable error notification where can be stored in flat text file or db (see /system/Logger.php)
 * 
 * @author     	Elvir Leonard <elvir.leonard@rixcentre.org>
 * @copyright  	2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    	$Id:$
 * @link       	NA
 * @since      	NA
 * @staticvar $listMessage array stackable error notification
 * 
 */

class Validation{
	
	// self explanatory
	function isInt($value){
		if(is_int($value))	return true;
		else false;
	}

	// self explanatory
	function isDouble($value){
		if(is_double($value))	return true;
		else false;
	}

	// self explanatory
	function isEmpty($value){
		if(empty($value))	return true;
		else false;
	}
}
?>