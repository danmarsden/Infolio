<?php
/**
 * Class JavascriptFramework
 * 
 * LICENSE: This is an Open Source Project
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