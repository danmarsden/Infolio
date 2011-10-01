<?php
/**
 * Class Breadcrumb
 * 

 * 
 * This class holds module on for the left menu in backoffice
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

class Module{
	
	protected $moduleId;
	protected $name;
	protected $image;
	protected $fileName;
	protected $entityName;
	
	/* ** Constructors ** */
	function __construct($moduleId=null, $name=null){
		$this->moduleId = $moduleId;
		$this->name = $name;
		$this->image = "";
		$this->fileName = "";		
	}
	
	/* ** Accessor ** */
	public function setId($value){ $this->moduleId=$value; }
	public function getId(){ return $this->moduleId; }

	public function setName($value){ $this->name=$value; }
	public function getName(){ return $this->name; }

	public function setImage($value){ $this->image=$value; }
	public function getImage(){ return $this->image; }		

	public function setFilename($value){ $this->fileName=$value; }
	public function getFilename(){ return $this->fileName; }			

	public function setEntityName($value){ $this->entityName=$value; }
	public function getEntityName(){ return $this->entityName; }			
	
}

?>