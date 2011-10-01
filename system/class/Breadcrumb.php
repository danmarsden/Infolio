<?php
/**
 * Class Breadcrumb
 * 
 * LICENSE: This is an Open Source Project
 * 
 * This class is responsible for breadcrumb creation
 * 
 * @staticvar 	mixed dbConn
 * @author     	Elvir Leonard <elvir.leonard@rixcentre.org>
 * @copyright  	2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    	$Id:$
 * @link       	NA
 * @since      	NA
 * 
 */

class Breadcrumb{
	
	/* ** Constructor ** */
	function __construct(){
		
	}
	
	/**
	 * Spits out html code based on where you are
	 * @return 
	 */
	function create(){
		$breadCrumbData = array();

		$currentModule = $this->checkModuleNameById($this->getDo());
		$breadCrumbData[] = '<a href=".">Home</a>';
		
		if( $currentModule ){
			$breadCrumbData[] = '<a href="?do=' . $currentModule->getId() . '">' . $currentModule->getName() . '</a>';
		}

		if($this->getAction()!=""){
			$breadCrumbData[] = ucfirst($this->getAction()) . " " . $currentModule->getEntityName();		
		}
		
		echo implode(BREADCRUMB_SEPARATOR, $breadCrumbData);
	}
	
	
	private function checkModuleNameById($id){
		$backoffice = BackOffice::getInstance();
		$modules = $backoffice->getInstalledModule();
		if(isset($modules)){
			foreach($modules as $module){
				if($module->getId()==$this->getDo()) return $module;
			}
		}
		return false;
	}

	/**
	 * Get a GET[do] folder; 'do' determine the module that is being used
	 * @return GET['do'] or POST['do']
	 */	
	private function getDo(){
        $gdo = Safe::get('do');
		if(isset($gdo)){
			return ($gdo!="") ? $gdo : Safe::Post('do');
		}else{
			return false;
		}
	}
	
	/**
	 * Translate GET[action] to a more linguistic word, to be used in breadcrumb
	 * @return GET['action'] or POST['action']
	 */	
	private function getAction(){
		$a="";
		$action="";
        $a = Safe::post('a');
        if (!isset($a)) {
            $a = Safe::get('a');
        }

		if($a=="edit") $action="edit";
		else if($a=="insert" || $a=="add") $action="add a new";
		else if($a=="delete") $action="delete";

		return $action;
	}
}
?>