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
 * @copyright  	2008 Rix Centre
 * @license    	http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
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
	 * Get a $_GET[do] folder; 'do' determine the module that is being used
	 * @return $_GET['do'] or $_POST['do']
	 */	
	private function getDo(){
		if(isset($_GET["do"])){
			return ($_GET["do"]!="") ? $_GET["do"] : $_POST["do"];
		}else{
			return false;
		}
	}
	
	/**
	 * Translate $_GET[action] to a more linguistic word, to be used in breadcrumb
	 * @return $_GET['action'] or $_POST['action']
	 */	
	private function getAction(){
		$a="";
		$action="";
		if( isset( $_GET["a"] ) ) $a=$_GET["a"];
		if( isset( $_POST["a"] ) ) $a=$_POST["a"];		
		
		if($a=="edit") $action="edit";
		else if($a=="insert" || $a=="add") $action="add a new";
		else if($a=="delete") $action="delete";

		return $action;
	}
}
?>