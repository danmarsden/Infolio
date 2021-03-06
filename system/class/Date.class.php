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
 * Class Date
 * 

 * 
 * This class is handling date conversion
 * 
 * @author     	Elvir Leonard <elvir.leonard@rixcentre.org>
 * @copyright  	2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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