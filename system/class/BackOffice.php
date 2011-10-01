<?php
/**
 * Class Application
 * 
 * LICENSE: This is an Open Source Project
 * 
 * 
 * @author     	Elvir Leonard
 * @copyright  	2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    	$Id: BackOffice.php 681 2009-07-07 14:36:14Z richard $
 * @link       	NA
 * @since      	NA
 * 
 */

include_once('model/User.class.php');

/**
 * This class will check all required files,
 * apply server (php) configuration and include any
 * shared functions required by the backoffice (admin)
 */
class BackOffice{
	
	static $_instance;
	static $modules;
	
	
	/**
	 * __construct
	 *
	 * calling start() function and finishes with finish() function
	 */
	function __construct()
	{
		$this->applySettings();
		$this->includeFiles();
		$this->setInstalledModule();
	}
	
	/**
	 * Factory method
	 * @return 
	 */
	public static function getInstance(){
		if( ! (self::$_instance instanceof self) ){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Function that is applying all settings required to run backoffice
	 * @todo conditional configuration is needed
	 */
	private function applySettings()
	{
		define('PAGE_PARSE_START_TIME', microtime());
		date_default_timezone_set("Europe/London");
		session_start();
		error_reporting(E_ALL);
	}

	/**
	 * includeFiles()
	 * Inlude all required files (in this case db and shared.php on DIR_FS_FUNCTION folder (refer to conf.php)
	 */
	private function includeFiles()
	{
		include_once(DIR_FS_FUNCTION . "shared.php");
	}
	
	// ToDo: Check this plugin model is used.
	/**
	 * Set all installed module
	 * Used in: class/Breadcrumbs.php
	 * @return 
	 */
	public function setInstalledModule()
	{
		$allModules = array();
		
		$studentManager = new Module("6", "User manager");
		$studentManager->setFilename("user");
		$studentManager->setEntityName("student");
		$studentManager->setImage("student.png");
		array_push($allModules, $studentManager);
		
		$studentGroup = new Module("4", "Group manager");
		$studentGroup->setFilename("user");
		$studentGroup->setEntityName("group");
		$studentGroup->setImage("group.png");
		array_push($allModules, $studentGroup);
		
		$assetManager = new Module("8", "Asset manager");
		$assetManager->setFilename("asset");
		$assetManager->setEntityName("asset");
		$assetManager->setImage("asset.png");
		array_push($allModules, $assetManager);
		
		$templateManager = new Module("1", "Template manager");
		$templateManager->setFilename("user");
		$templateManager->setEntityName("template");
		$templateManager->setImage("template.png");
		array_push($allModules, $templateManager);
		
		$schoolManager = new Module("7", "Institution manager");
		$schoolManager->setFilename("user");
		$schoolManager->setEntityName("school");
		$schoolManager->setImage("school.png");
		array_push($allModules, $schoolManager);
		
		$systemLog = new Module("5", "System log");
		$systemLog->setFilename("user");
		$systemLog->setEntityName("system log");
		$systemLog->setImage("group.png");
		array_push($allModules, $systemLog);		
		
		self::$modules=$allModules;
	}
	
	/**
	 * return an array of all installed module
	 * @return 
	 */
	public function getInstalledModule(){ return self::$modules; }
	
	/**
	 * Get the current module that user is currently working on
	 * @return 
	 */
	public function getCurrentModule(){
		$currentModule = $this->checkModuleNameById($this->getDo());
		if( $currentModule ){
			return $currentModule;
		}
	}
	
	/**
	 * Get a GET[do] folder; 'do' determine the module that is being used
	 * @return GET['do'] or POST['do']
	 */
	private function getDo()
	{
        $gdo = Safe::get('do');
        $pdo = Safe::post('do');
		if(isset($gdo)) return $gdo;
		else if(isset($pdo)) return $pdo;
	}
	
	/**
	 * Return module by querying using its ID
	 * @return object module
	 * @param $id Object
	 */
	private function checkModuleNameById($id)
	{
		$modules = $this->getInstalledModule();
		if(isset($modules)){
			foreach($modules as $module){
				if($module->getId()==$this->getDo()) return $module;
			}
		}
		return false;
	}
	
	/**
	 * This to get icon for current module
	 * @return full image path
	 */
	public function getIconForCurrentModule()
	{
		$currentModule=$this->getCurrentModule();
		if($currentModule){
			if($currentModule->getImage()!="") return $currentModule->getImage();
		}
		return "";
	}


	public static function RetrieveAndCheckAjaxAdminUser($sessionData)
	{
		if( isset($_SESSION) ) {
			$adminUser = User::RetrieveBySessionData($_SESSION);

			// Nullify user if they don't have permission
			if( isset($adminUser) &&  !$adminUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN) ) {
				$adminUser = null;
			}
		}

		// Stop, if user not valid
		if(!isset($adminUser)) {
			die('User not logged in');
		}

		return $adminUser;
	}
}