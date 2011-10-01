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
 * Class JavascriptFramework
 * 

 * 
 * This class is responsible to handle all javascript framework
 * 
 * @author     	Elvir Leonard <elvir.leonard@rixcentre.org>
 * @copyright  	2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    	$Id:$
 * @link       	NA
 * @since      	NA
 * 
 */
class JavascriptFramework{
	protected $frameworks=array(
		"jquery"	=>	"jquery/jquery-1.2.6.min.js",
		"dojo"		=>	"/_scripts/dojo/dojo.js"
	);
	
	/**
	 * includes all necessary files for TableSorter
	 */
	public function jqueryTableSorter(){
		$this->includeFiles(
			array(
				$this->frameworks["jquery"],
				"jquery/tableSorter/jquery.tablesorter.min.js",
				"jquery/tableSorter/jquery.tablesorter.pager.js",		
			),
			array(
				"jquery/tableSorter/blue/style.css"
			)
		);
	}

	/**
	 * includes all necessary files for SimpleModal
	 */
	public function jquerySimpleModal(){
		$this->includeFiles(
			array(
				$this->frameworks["jquery"],
				"jquery/simplemodal/jquery.simplemodal-1.1.1.js"
			)
		);
	}

	public function includeJquery(){
		$this->includeFiles(array(
			$this->frameworks["jquery"]
		));
	}
	
	public function includeDojo(){
		$this->includeFiles(array(
			$this->frameworks["dojo"]
		));
	}
	
	private function includeFiles($jss, $csss=array()){
		foreach($jss as $js){
			?><script type="text/javascript" src="<?php echo DIR_WS_JAVASCRIPT . $js ?>"></script><?
		}
		foreach($csss as $css){
			?><link rel="stylesheet" href="<?php echo DIR_WS_JAVASCRIPT . $css ?>" type="text/css" media="screen" /><?php
		}
	}
}
?>