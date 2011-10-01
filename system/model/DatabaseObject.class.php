<?php

/**
 * DatabaseObject Class
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: DatabaseObject.class.php 799 2009-09-01 21:19:41Z richard $
 * @link       NA
 * @since      NA
*/

include_once('framework/pear/JSON.php');
include_once('class/Date.class.php');

/**
 * This class contains data and functions that a persisted DB model class will need.
 * All data model classes should inherit from this class.
 */
abstract class DatabaseObject
{
	protected $m_id;
	
	// Audit variables
	protected $m_createdBy;
	protected $m_createdTime;
	protected $m_updatedBy;
	protected $m_updatedTime;

	protected $m_filled;
	
	/**
	 * Constructor that allows lazy loading
	 * @return 
	 * @param $id Object[optional]
	 */
	public function __construct($id = null)
	{
		$this->m_id = $id;
		$this->m_filled = false;
	}
	
	/* ** Accessors ** */
	
	public function getId(){ return $this->m_id; }

	public function isSameAs(DatabaseObject $obj)
	{
		return ($this->m_id == $obj->m_id);
	}

	/* ** Audit Accessors ** */

	public function getCreatedBy()
	{
		$this->checkFilled();
		return $this->m_createdBy;
	}
	
	public function getCreatedTime()
	{
		$this->checkFilled();
		return $this->m_createdTime;
	}
	
	public function getUpdatedBy()
	{
		$this->checkFilled();
		return $this->m_updatedBy;
	}
	
	public function getUpdatedTime()
	{
		$this->checkFilled();
		return $this->m_updatedTime;
	}

	/**
	 * Sets the audit fields of this object from a hasharray
	 * @param <type> $hashArray
	 */
	protected function setAuditFieldsFromHashArray($hashArray)
	{
		$this->m_createdTime = Date::varFromDatabase($hashArray['created_time']);
		$this->m_updatedTime = Date::varFromDatabase($hashArray['updated_time']);
		$this->m_updatedBy = new User($hashArray['updated_by']);
		$this->m_createdBy = new User($hashArray['created_by']);
	}

	/* ** JSON methods ** */

	/**
	 *
	 * @param <type> $dbObjectArray
	 * @param <type> $option Not used but may be used by descendants
	 * @return <type>
	 */
	public static function CreateJsonString($dbObjectArray, $fakeId = false)
	{
		$collectionObj = (object) array(
			'label' => 'id',
			'identifier' => 'id',
			'items' => self::createJsonItems($dbObjectArray, $fakeId)
		);

		if($fakeId) $collectionObj->identifier = 'fid';
		
		$oJSON = new Services_JSON();
		return $oJSON->encode($collectionObj);
	}

	/**
	 * Creates JSON items for JSON string
	 * @param <type> $dbObjectArray
	 * @param <type> $fakeId Create serialised id numbers for assets without ids
	 * @return <type>
	 */
	protected static function createJsonItems($dbObjectArray, $fakeId = false)
	{
		$jsonItems = array();
		$counter = 0;
		foreach($dbObjectArray as $item) {
			if( isset($item) ) {
				$itemObject = $item->getAsObject();
				if($fakeId) $itemObject->fid = $counter;
				$jsonItems[] = $itemObject;
			}
			$counter++;
		}

		return $jsonItems;
	}

	public static function createXmlString($dbObjectArray)
	{
		$xml = "<DbObjects>\n";

		// Loop through all the items
		foreach($dbObjectArray as $item) {
			$xml .= $item->toXml();
		}
		$xml .= '</DbObjects>';

		return $xml;
	}

	/* ** Database methods ** */
	
	public function Save($authUser)
	{	
		$this->m_updatedBy = $authUser;
		$this->m_updatedTime = Date::Now();
		if(isset($this->m_id)){
			// Only update filled objects
			if($this->m_filled) {
				$this->dbUpdate();
			}
		}
		else {
			$this->dbCreate();
		}
	}
	
	/* ** Methods ** */
	
	/**
	 * Checks this object is filled and fills it if it exists in the DB
	 * @return 
	 */
	protected function checkFilled()
	{
		if ( !$this->m_filled && isset($this->m_id) ) {
			$this->populateFromDB();
		}
	}

	/**
	 * Gets the id for this object if it has just been created.
	 * Should be called at the end of dbCreate
	 */
	protected function populateInsertedId($tableName)
	{
		// Get user's new DB ID
		$sql = "SELECT id from {$tableName} WHERE updated_by='{$this->m_updatedBy->getId()}' ORDER BY ID DESC LIMIT 1";

		$db = Database::getInstance();
		$result = $db->query($sql);
		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$this->m_id = $row['id'];
		}
	}

	public function toXml()
	{

		$typeName = get_class($this);
		$xml = "<{$typeName}>";

		$itemObject = $this->getAsObject();
		// Loop through an items properties and produce its XML
		foreach($itemObject as $itemKey=>$itemValue) {
			$xml .= "<{$itemKey}>{$itemValue}</{$itemKey}>";
		}

		$xml .= "</{$typeName}>\n";

		return $xml;
	}

	/* ** Abstract methods ** */
	
	abstract protected function dbCreate();
	abstract protected function dbUpdate();

	/**
	 * Returns a simple object of this object.
	 * Used for JSON making in createJsonString and createJsonItems.
	 */
	abstract protected function getAsObject();
	abstract protected function populateFromDB();
	
}