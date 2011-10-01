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