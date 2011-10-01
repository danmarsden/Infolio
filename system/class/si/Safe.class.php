<?php

/**
 * The Safe Class
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: Safe.class.php 675 2009-07-06 14:34:00Z richard $
 * @link       NA
 * @since      NA
*/

/**
 * PARAM_ALPHA - contains only english ascii letters a-zA-Z.
 */
define('PARAM_ALPHA',    'alpha');

/**
 * PARAM_ALPHAEXT the same contents as PARAM_ALPHA plus the chars in quotes: "_-" allowed
 * NOTE: originally this allowed "/" too, please use PARAM_SAFEPATH if "/" needed
 */
define('PARAM_ALPHAEXT', 'alphaext');

/**
 * PARAM_ALPHANUM - expected numbers and letters only.
 */
define('PARAM_ALPHANUM', 'alphanum');

/**
 * PARAM_ALPHANUMEXT - expected numbers, letters only and _-.
 */
define('PARAM_ALPHANUMEXT', 'alphanumext');
/**
 * PARAM_NORMAL - only does htmlspecialchars
 */
define('PARAM_NORMAL',    'normal');

/**
 * PARAM_INT - integers only, use when expecting only numbers.
 */
define('PARAM_INT',      'int');

define('PARAM_FILE', 'file');

include_once(DIR_FS_SYSTEM . "kses.php");
/**
 * A class to store functions that help make input safe and secure.
 */
class Safe
{
	/**
	 * Checks a user's input is safe and takes out anything nasty
	 * @return String
	 * @param $userInputText String
	 */
	public static function Input($userInputText, $type=PARAM_NORMAL)
	{
        switch($type) {
            case PARAM_ALPHA:        // Remove everything not a-z
                return preg_replace('/[^a-zA-Z]/i', '', $userInputText);
            case PARAM_ALPHAEXT:     // Remove everything not a-zA-Z_- (originally allowed "/" too)
                return preg_replace('/[^a-zA-Z_-]/i', '', $userInputText);
            case PARAM_ALPHANUM:     // Remove everything not a-zA-Z0-9
                return preg_replace('/[^A-Za-z0-9]/i', '', $userInputText);
            case PARAM_ALPHANUMEXT:     // Remove everything not a-zA-Z0-9_-
                return preg_replace('/[^A-Za-z0-9_-]/i', '', $userInputText);
            case PARAM_INT:
                if (is_numeric($userInputText)) {
                    return (int)$userInputText;
                } else {
                    return null;
                }
                break;
            case PARAM_FILE:
                $userInputText = preg_replace('~[[:cntrl:]]|[&<>"`\|\':\\\\/]~u', '', $userInputText);
                $userInputText = preg_replace('~\.\.+~', '', $userInputText);
                return $userInputText;
                break;
            case PARAM_NORMAL:
            default:
                global $ALLOWED_TAGS;
                if (is_numeric($userInputText)) {
                    return $userInputText;
                }
                /// Fix non standard entity notations
                $userInputText = fix_non_standard_entities($userInputText);

                /// Remove tags that are not allowed
                $userInputText = strip_tags($userInputText, $ALLOWED_TAGS);

                /// Clean up embedded scripts and , using kses
                $userInputText = cleanAttributes($userInputText);

                /// Again remove tags that are not allowed
                $userInputText = strip_tags($userInputText, $ALLOWED_TAGS);

                // Remove potential script events - some extra protection for undiscovered bugs in our code
                $userInputText = preg_replace("~([^a-z])language([[:space:]]*)=~i", "$1Xlanguage=", $userInputText);
                $userInputText = preg_replace("~([^a-z])on([a-z]+)([[:space:]]*)=~i", "$1Xon$2=", $userInputText);
                //ToDo: Filter the text better
                return htmlspecialchars($userInputText, ENT_QUOTES);
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
            return Safe::Input($_GET[$var], $type);
        }
        return null;
    }
    public static function post($var, $type=PARAM_NORMAL) {
        if (isset($_POST[$var])) {
            return Safe::Input($_POST[$var], $type);
        }
        return null;
    }
    public static function request($var, $type=PARAM_NORMAL) {
        if (isset($_REQUEST[$var])) {
            return Safe::Input($_REQUEST[$var], $type);
        }
        return null;
    }
    public static function getWithDefault($var, $default, $type=PARAM_NORMAL) {
       if( isset($_GET[$var]) ) {
			$value = Safe::Input($_GET[$var], $type);
		} else {
			$value = $default;
		}
        return $value;
    }
    public static function postWithDefault($var, $default, $type=PARAM_NORMAL) {
        if( isset($_POST[$var]) ) {
             $value = Safe::Input($_POST[$var], $type);
         } else {
             $value = $default;
         }
         return $value;
    }
}

/**
 * Replaces non-standard HTML entities
 *
 * @param string $string
 * @return string
 */
function fix_non_standard_entities($string) {
    $text = preg_replace('/&#0*([0-9]+);?/', '&#$1;', $string);
    $text = preg_replace('/&#x0*([0-9a-fA-F]+);?/', '&#x$1;', $text);
    $text = preg_replace('[\x00-\x08\x0b-\x0c\x0e-\x1f]', '', $text);
    return $text;
}

/**
 * This function takes a string and examines it for HTML tags.
 *
 * If tags are detected it passes the string to a helper function {@link cleanAttributes2()}
 * which checks for attributes and filters them for malicious content
 *
 * @param string $str The string to be examined for html tags
 * @return string
 */
function cleanAttributes($str){
    $result = preg_replace_callback(
            '%(<[^>]*(>|$)|>)%m', #search for html tags
            "cleanAttributes2",
            $str
            );
    return  $result;
}

/**
 * This function takes a string with an html tag and strips out any unallowed
 * protocols e.g. javascript:
 *
 * It calls ancillary functions in kses which are prefixed by kses
 *
 * @global object
 * @global string
 * @param array $htmlArray An array from {@link cleanAttributes()}, containing in its 1st
 *              element the html to be cleared
 * @return string
 */
function cleanAttributes2($htmlArray){

    global $CFG, $ALLOWED_PROTOCOLS;
    require_once($CFG->libdir .'/kses.php');

    $htmlTag = $htmlArray[1];
    if (substr($htmlTag, 0, 1) != '<') {
        return '&gt;';  //a single character ">" detected
    }
    if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9]+)([^>]*)>?$%', $htmlTag, $matches)) {
        return ''; // It's seriously malformed
    }
    $slash = trim($matches[1]); //trailing xhtml slash
    $elem = $matches[2];    //the element name
    $attrlist = $matches[3]; // the list of attributes as a string

    $attrArray = kses_hair($attrlist, $ALLOWED_PROTOCOLS);

    $attStr = '';
    foreach ($attrArray as $arreach) {
        $arreach['name'] = strtolower($arreach['name']);
        if ($arreach['name'] == 'style') {
            $value = $arreach['value'];
            while (true) {
                $prevvalue = $value;
                $value = kses_no_null($value);
                $value = preg_replace("/\/\*.*\*\//Us", '', $value);
                $value = kses_decode_entities($value);
                $value = preg_replace('/(&#[0-9]+)(;?)/', "\\1;", $value);
                $value = preg_replace('/(&#x[0-9a-fA-F]+)(;?)/', "\\1;", $value);
                if ($value === $prevvalue) {
                    $arreach['value'] = $value;
                    break;
                }
            }
            $arreach['value'] = preg_replace("/j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t/i", "Xjavascript", $arreach['value']);
            $arreach['value'] = preg_replace("/v\s*b\s*s\s*c\s*r\s*i\s*p\s*t/i", "Xvbscript", $arreach['value']);
            $arreach['value'] = preg_replace("/e\s*x\s*p\s*r\s*e\s*s\s*s\s*i\s*o\s*n/i", "Xexpression", $arreach['value']);
            $arreach['value'] = preg_replace("/b\s*i\s*n\s*d\s*i\s*n\s*g/i", "Xbinding", $arreach['value']);
        } else if ($arreach['name'] == 'href') {
            //Adobe Acrobat Reader XSS protection
            $arreach['value'] = preg_replace('/(\.(pdf|fdf|xfdf|xdp|xfd)[^#]*)#.*$/i', '$1', $arreach['value']);
        }
        $attStr .=  ' '.$arreach['name'].'="'.$arreach['value'].'"';
    }

    $xhtml_slash = '';
    if (preg_match('%/\s*$%', $attrlist)) {
        $xhtml_slash = ' /';
    }
    return '<'. $slash . $elem . $attStr . $xhtml_slash .'>';
}