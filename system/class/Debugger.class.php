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
 * Debugger.class.php - A class that helps print debugging info
 * 
 * This is based on a class from the book 'Professional PHP5' by Wrox Press.
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: Debugger.class.php 333 2009-01-09 12:02:46Z richard $
 * @link       NA
 * @since      NA
*/


class Debugger {

	const LEVEL_INFO = 100;
	const LEVEL_SQL = 75;
	const LEVEL_WARNING = 50;
	const LEVEL_ERROR = 25;
	const LEVEL_CRITICAL = 10;
	const LEVEL_NONE = 0;

	public static function debug($data, $key = null, $debugLevel = Debugger::LEVEL_INFO)
	{
		global $debug;

		if(! isset($_SESSION['debugData'])) {
			$_SESSION['debugData'] = array();
		}
		
		if($debugLevel <= $debug['DEBUG_LEVEL']) {
			$_SESSION['debugData'][$key] = $data;
		}
	}

	public static function debugPrint()
	{
		global $debug;
		
		if($debug['DEBUG_LEVEL'] > self::LEVEL_NONE) {
			$arDebugData = $_SESSION['debugData'];
			print Debugger::printArray($arDebugData);
			    
			$_SESSION['debugData'] = array();
		}
	}

	private static function printArray($var, $title = true)
	{
		$html = '<h2 class="debug">Debug info</h2><table class="debug">';
		if ($title) {
			$html .= "<tr><th scope=\"col\">Key</th><th scope=\"col\">Value</th></tr>\n";
		}
		
		if (is_array($var)) {
			foreach($var as $key => $value) {
			          
				$html .= "<tr>\n" ;
				$html .= "<td><b>$key</b></td><td>";
				
				if (is_array($value)) {
					$html .= Debugger::printArray($value, false);
				} elseif(gettype($value) == 'object') {
					$html .= "Object of class " . get_class($value);
				} else {
					$html .= "$value" ;
				}
				
				$html .= "</td></tr>\n";
			}
		}
		
		$html .= "</table>\n";
		return $html;
	}

}