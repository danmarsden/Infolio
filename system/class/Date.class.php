<?php
/**
 * Class Date
 * 
 * LICENSE: This is an Open Source Project
 * 
 * This class is handling date conversion
 * 
 * @author     	Elvir Leonard <elvir.leonard@rixcentre.org>
 * @copyright  	2008 Rix Centre
 * @license    	http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    	$Id: Date.class.php 849 2010-01-07 11:19:12Z richard $
 * @link       	NA
 * @since      	NA
 * 
 */

class Date
{
	// Date format constants
	# - DATE
	const FORMAT_LONG = 'd/m/Y h:i A';
	const FORMAT_SHORT = 'd/m/Y';
	const FORMAT_INTERNET = 'D, d M Y h:i A';
	//const DATE_DB_INPUT_FORMAT_SHORT = 'Y-m-d';
	const DB_FORMAT_LONG = 'Y-m-d h:i:s';
	
	// Useful constants
	const SECONDS_IN_YEAR = 31536000; // 60 * 60 * 24 * 365
	
	/**
	 * Creates a time that can be used in an SQL statement
	 * @return 
	 * @param $timestamp Object
	 */
	public static function formatForDatabase($timestamp)
	{
		return date(self::DB_FORMAT_LONG, $timestamp);
	}

	/**
	 * Creates a time that can be used in internet messages such as RSS and http headers
	 * @return
	 * @param $timestamp Object
	 */
	public static function formatForInternet($timestamp)
	{
		return date(self::FORMAT_INTERNET, $timestamp);
	}

	/**
	 * The standard format for dates (with time) on this site
	 * @return 
	 * @param $date Object
	 */
	public static function formatLongForScreen($timestamp)
	{
		return date(self::FORMAT_LONG, $timestamp);
	}
	
	/**
	 * The standard format for dates (without time) on this site
	 * @return 
	 * @param $date Object
	 */
	public static function formatShortForScreen($timestamp)
	{
		return date(self::FORMAT_LONG, $timestamp);
	}
	
	/**
	 * The current time
	 * @return 
	 */
	public static function now()
	{
		return time();
	}
	
	public static function varFromDatabase($databaseTime)
	{
		return strtotime($databaseTime);
	}
}