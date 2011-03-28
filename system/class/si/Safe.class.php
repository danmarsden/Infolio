<?php

/**
 * The Safe Class
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: Safe.class.php 675 2009-07-06 14:34:00Z richard $
 * @link       NA
 * @since      NA
*/

/**
 * PARAM_NORMAL - only does htmlspecialchars
 */
define('PARAM_NORMAL',    'normal');

/**
 * PARAM_INT - integers only, use when expecting only numbers.
 */
define('PARAM_INT',      'int');

define('PARAM_FILE', 'file');

/**
 * A class to store functions that help make input safe and secure.
 */
class Safe
{
	/**
	 * Returns value off $array at $index if it's set, otherwise returns $defaultValue
	 * @return Mixed
	 * @param $array Array
	 * @param $index Mixed
	 * @param $defaultValue Mixed
	 */
	public static function GetArrayIndexValueWithDefault($array, $index, $defaultValue)
	{
		if( isset($array[$index]) ) {
			$value = $array[$index];
		}
		else {
			$value = $defaultValue;
		}

		// Strip unwanted slashes (if value is set)
		if(isset($value)) {
			$value = self::StripUnwantedSlashes($value);
		}

		return $value;
	}

	/**
	 * Checks a user's input is safe and takes out anything nasty
	 * @return String
	 * @param $userInputText String
	 */
	public static function Input($userInputText)
	{
		//ToDo: Filter the text better
		return htmlspecialchars($userInputText);
	}

	/**
	 * Removes slashes if they've been added by magic quotes,
	 * but they're not needed because we aren't sending the text to a database
	 * @param <type> $string
	 * @return <type>
	 */
	public static function StripUnwantedSlashes($string)
	{
		if(!get_magic_quotes_gpc()) {
			return $string;
		}
		else {
			return stripslashes($string);
		}
	}
	
	/**
	 * Checks a user's input is safe and takes out anything nasty
	 * @return String 
	 * @param $sessionInputText String
	 */
	public static function SessionInput($sessionInputText)
	{
		//ToDo: Filter the text;
		return $sessionInputText;
	}

	public static function StringForDatabase($dbField)
	{
		if(!get_magic_quotes_gpc()) {
			return addslashes($dbField);
		}
		else {
			return $dbField;
		}
	}

	public static function UrlQueryVarOutput($urlQueryVar)
	{
		//ToDo: Remove any unsafe characters
		
		// Replace spaces with hyphens
		$urlQueryVar = str_replace(' ', '-', $urlQueryVar);
		
		// Get rid of any other characters
		$urlQueryVar = preg_replace('/[^\w\-\_\']/' ,''  , $urlQueryVar);
		
		return $urlQueryVar;
	}
    public static function get($var, $type=PARAM_NORMAL) {
        if (isset($_GET[$var])) {
            //ToDo: Filter the text better
            return htmlspecialchars($_GET[$var]);
        }
        return null;
    }
    public static function post($var, $type=PARAM_NORMAL) {
        if (isset($_POST[$var])) {
            return htmlspecialchars($_POST[$var]);
        }
        return null;
    }
    public static function request($var, $type=PARAM_NORMAL) {
        if (isset($_REQUEST[$var])) {
            return htmlspecialchars($_REQUEST[$var]);
        }
        return null;
    }
    public static function getWithDefault($var, $default, $type=PARAM_NORMAL) {
       if( isset($_GET[$var]) ) {
			$value = $_GET[$var];
		} else {
			$value = $default;
		}
        return $value;
    }
    public static function postWithDefault($var, $default, $type=PARAM_NORMAL) {
        if( isset($_POST[$var]) ) {
             $value = $_GET[$var];
         } else {
             $value = $default;
         }
         return $value;
    }
}