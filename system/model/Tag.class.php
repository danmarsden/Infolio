<?php

/**
 * The Institution Class
 *

 *
 * @author     Richard Garside
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: Tag.class.php 803 2009-10-29 14:41:47Z richard $
 * @link       NA
 * @since      NA
 */

include_once('DatabaseObject.class.php');

class Tag extends DatabaseObject
{
	private $m_name;
	private $m_institution;
	
	private $m_isPublicAssetTag = true;
	
	/* ** Accessors ** */

	/**
	 * Returns a simple object of this object. Is used for JSON making.
	 */
	protected function getAsObject()
	{
		return (object) array(
			'tag_id'		=>	$this->getId(),
			'name'		=>	$this->getName()
		);
	}

	/**
	 * The institution that owns this tag
	 * @return <type>
	 */
	public function getInstitution()
	{
		if(!$this->m_filled) {
			$this->populateFromDB();
		}
		return $this->m_institution;
	}
	public function setInstitution($value){$this->m_institution = $value;}

	/**
	 * Gets this tag's name
	 * @return String
	 */
	public function getName()
	{
		if(!$this->m_filled) {
			$this->populateFromDB();
		}
		return $this->m_name;
	}
	public function setName($value){$this->m_name = $value;}


	/**
	 * Creates a filter link to this tag
	 * @param <type> $page
	 */
	public function FilterLink(SimplePage $page, $filterQueryParams = array())
	{
		return Link::CreateLink($this->getName(), $page->PathWithQueryString(array_merge(array('tag'=>$this->getName()), $filterQueryParams)));
	}
	
	public function FilterLinkRemove(SimplePage $page, $assetId)
	{
		if(!$this->m_isPublicAssetTag) {
			$link = Link::CreateLink('x', $page->PathWithQueryString(array('c'=>$assetId ,'deltag'=>$this->getName())));
			$link->setTitle('Delete ' . $this->getName() . ' tag');
			return $link;
		}
	}


	/* ** Factory methods ** */

	public static function CreateOrRetrieveByName($tagName, Institution $institution, User $authUser)
	{
		$tag = self::RetrieveByName($tagName, $institution);
		if(!isset($tag)) {
			$tag = new Tag();
			$tag->setInstitution($institution);
			$tag->setName($tagName);
			$tag->Save($authUser);
		}

		return $tag;
	}

	/**
	 * Retrieves a set of tags from the Database for an asset
	 * @param Institution $institution
	 * @return Array of tags
	 */
	public static function RetrieveByAsset(Asset $asset, User $user=null)
	{
		if(isset($user)) {
			// Limit to this users tags and global ones
			$sqlUserWhere = "(user_id={$user->getId()} OR user_id IS NULL)";
		}
		else {
			// Limit to public tags
			$sqlUserWhere = "user_id IS NULL";
		}

		$db = Database::GetInstance();
		//$sql = "SELECT * FROM tags WHERE id IN (SELECT tag_id FROM tags_assets WHERE asset_id={$asset->getId()} AND {$sqlUserWhere}) ORDER BY name ASC";
		$sql = "SELECT tags.*, tags_assets.user_id FROM tags INNER JOIN tags_assets ON tags.id=tags_assets.tag_id WHERE asset_id={$asset->getId()} AND {$sqlUserWhere} ORDER BY tags.name ASC";
		//echo ("Debug: {$sql}<br />");
		$result = $db->query($sql);

		$tags = array();
		while($row = $db->fetchArray($result)) {
			$tag = self::createFromHashArray($row);
			if(isset($row['user_id'])) {
				$tag->m_isPublicAssetTag = false;
			}
			$tags[$row['id']] = $tag;
		}

		return $tags;
	}

	/**
	 * Retrieves a set of tags from the Database that are owned by the institution
	 * @param Institution $institution
	 * @return Array of tags
	 */
	public static function RetrieveByInstitution(Institution $institution)
	{
		$db = Database::GetInstance();
		$sql = "SELECT * FROM tags WHERE institution_id={$institution->getId()} ORDER BY name ASC";
		$result = $db->query($sql);
		echo ("<p>Debug: {$sql}</p>");
		
		$institutions = array();
		while($row = $db->fetchArray($result)) {
			$institutions[$row['id']] = self::createFromHashArray($row);
		}
		
		return $institutions;
	}

	/**
	 * Retrieves a tag from the Database that has the provided name and is from the specified institution
	 * @param String $tagName
	 * @param Institution $institution
	 * @return Array of tags
	 */
	public static function RetrieveByName($tagName, Institution $institution)
	{
		$db = Database::GetInstance();
		$sql = "SELECT * FROM tags WHERE name='{$tagName}' AND institution_id={$institution->getId()} ORDER BY name ASC";
		$result = $db->query($sql);

		if($row = $db->fetchArray($result)) {
			$tag = self::createFromHashArray($row);
			return $tag;
		}
	}

	/**
	 * Retrieves a set of tags from the Database that have been used by this user
	 * @param User $user
	 * @return Array of tags
	 */
	public static function RetrieveByUser(User $user)
	{
		$db = Database::GetInstance();
		$sql = "SELECT * FROM tags WHERE id IN (SELECT tag_id FROM tags_assets WHERE asset_id " .
				"IN (SELECT assets.id FROM assets INNER JOIN collection ON assets.id = collection.asset_id " .
				"WHERE enabled=1 AND assets.id IN (SELECT asset_id FROM collection WHERE user_id ={$user->getId()} ".
				"OR group_id IN (SELECT group_id FROM group_members WHERE user_id ={$user->getId()})))) ORDER BY name ASC";

		Debugger::debug($sql, 'Tag::RetrieveByUser', Debugger::LEVEL_SQL);
		$result = $db->query($sql);

		$tags = array();
		while($row = $db->fetchArray($result)) {
			$tags[] = self::createFromHashArray($row);
			
		}
		return $tags;
	}

	public static function UserKey(User $user = null)
	{
		return isset($forUser) ? $forUser->getId() : 0;
	}
	
	/* ** DB Methods ** */
	
	/**
	 * 
	 * @return 
	 */
	protected function dbCreate()
	{
		$this->m_createdBy = $this->m_updatedBy;
		$this->m_createdTime = $this->m_updatedTime;
		
		$data = array(
			'name' => $this->m_name,
			'institution_id' => $this->m_institution->getId(),
			'created_by' => $this->m_createdBy->getId(),
			'updated_by' => $this->m_updatedBy->getId(),
			'created_time' => Date::formatForDatabase($this->m_createdTime),
			'updated_time' => Date::formatForDatabase($this->m_updatedTime)
		);

		// Write to DB
		$db = Database::getInstance();
		$db->perform('tags', $data, Database::INSERT);
		
		// Get tag's new DB ID
		$sql = "SELECT id from tags WHERE name='{$this->m_name}' ORDER BY id DESC LIMIT 1";
		$result = $db->query($sql);
		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$this->m_id = $row['id'];
		}
		else {
			print 'poo';
		}
	}
	
	/**
	 * Database operation
	 * @return 
	 */
	public function Delete()
	{
		$sql = "DELETE FROM tags WHERE id={$this->m_id}";
		$db = Database::getInstance();
		$db->query($sql);
		$this->m_id = null;
	}
	
	/**
	 * Gets the data for this institution using the ID.
	 * Lazy loading. In most casse we only need the institution ID
	 * @return 
	 * @param $id Object
	 */
	protected function populateFromDB()
	{
		$db = Database::getInstance();
		$sql = "SELECT * FROM tags WHERE id={$this->m_id}";
		$result = $db->query($sql);
		
		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$this->createFromHashArray($row, $this);
		}
	}
	
	protected function dbUpdate()
	{
		$data = array(
			'name' => $this->m_name,
			'url' => $this->m_url
		);
		$db = Database::getInstance();
		$db->perform('institution', $data, Database::UPDATE, "id={$this->m_id}");
	}
	
	private static function createFromHashArray($hashArray, Tag &$tag=null)
	{
		if(!isset($tag)) {
			$tag = new Tag($hashArray['id']);
		}

		$tag->m_name = $hashArray['name'];
		$tag->m_institution = new Institution($hashArray['institution_id']);
		$tag->setAuditFieldsFromHashArray($hashArray);
		$tag->m_filled = true;
		return $tag;
	}
}