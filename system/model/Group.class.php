<?php
/**
 * Group Class
 *
 * This class is used to hold group data 
 * 
 * LICENSE: This is an Open Source Project
 *
 * @author     Elvir LEonard	
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: Group.class.php 708 2009-07-20 14:35:23Z richard $
 * @link       NA
 * @since      NA
 */

include_once('DatabaseObject.class.php');

class Group extends DatabaseObject
{	
	/* ** Member variables ** */
	private $m_title;
	private $m_description;
	private $m_institution;
	
	private $m_members;
	private $m_membersToDelete;
	private $m_membersToSave;

	// From other table
	private $m_assetCollection;
	
	/* ** Accessors ** */

	/**
	 * Add an asset to this user's collection
	 * @param int $assetId
	 */
	public function addAsset($newAsset, $authUser=null)
	{

		// Check asset isn't already in collection
		$assets = $this->getAssetCollection();
		$duplicateFound = false;
		foreach($assets as $asset) {
			// TODO: Finish this bit
			if($newAsset->isSameAs($asset)) {
				$duplicateFound = true;
				break;
			}
		}

		if($duplicateFound) {
			// Don't add it again
			return false;
		}
		else {
			$newAsset->assignToCollectionInDb(null, $this, $authUser);
		}
	}

	/**
	 * Remove asset from this group's collection
	 * @param int $assetId
	 */
	public function removeAsset($assetId)
	{
		// Check asset is in collection
		$assets = $this->getAssetCollection();
		$assets = $assets->getAssets();

		$assetExists = false;
		foreach($assets as $asset) {
			// TODO: Finish this bit
			if($asset->getId() == $assetId) {
				$assetExists = true;
				break;
			}
		}

		if($assetExists) {
			// Would like to check it's been used (but shared nature of picture could cause problems)
			$db = DATABASE::getInstance();
			/*$sql = "SELECT picture0, picture1 FROM block WHERE page_id IN (SELECT id FROM page WHERE tab_id IN (SELECT id from tab WHERE user_id={$this->getId()}))";
			$result = $db->query($sql);

			$pictureUsed = false;
			while($row = $db->fetchArray($result)) {
				if($row['picture0'] == $assetId || $row['picture1'] == $assetId) {
					$pictureUsed = true;
					break;
				}
			}

			if($pictureUsed) {
				return 'Picture in use by this user';
			}
			else { */

			// Delete asset from collection
			$sql = "DELETE FROM collection WHERE asset_id={$assetId} AND group_id={$this->getId()}";
			$db->query($sql);
			return 'Asset removed';
		}
		else {
			// Can't remove it, as not there
			return 'Picture doesn\'t exist in this user/group\'s collection';
		}
	}

	/**
	 * Returns a simple object of this object. Is used for JSON making.
	 */
	protected function getAsObject()
	{
		return (object) array(
			'id' => $this->getId(),
			'name' => $this->getTitle()
		);
	}

	public function getAssetCollection()
	{
		if(!isset($this->m_assetCollection)) {
			$this->m_assetCollection = AssetCollection::CreateGroupAssetCollection($this);
		}
		return $this->m_assetCollection;
	}

	public function addMember(User $user)
	{
		$this->checkFilled();
		if(!isset($this->m_members)) {
			$this->m_members = User::RetrieveUsersInGroup($this, $this->m_createdBy);
		}
		$this->m_members[] = $user;
		
		// newMembers keeps track of users that needs to be saved
		if(!isset($this->m_membersToSave)) {
			$this->m_membersToSave = array();
		}
		$this->m_membersToSave[] = $user;
	}
	
	public function removeMember(User $user)
	{
		$this->checkFilled();
		if(!isset($this->m_members)) {
			$this->m_members = User::RetrieveUsersInGroup($this, $this->m_createdBy);
		}
		// Find member and remove it from array
		foreach($this->m_members as $key=>$member) {
			if($user->getId() == $member->getId()) {
				unset($this->m_members[$key]);
				break;
			}
			$this->m_members = array_values($this->m_members);
		}
		
		// newMembers keeps track of users that needs to be deleted
		if(!isset($this->m_membersToSave)) {
			$this->m_membersToSave = array();
		}
		$this->m_membersToDelete[] = $user;
	}

	public function setDescription($value)
	{
		$this->checkFilled();
		$this->m_description = $value;
	}
	public function getDescription()
	{
		$this->checkFilled();
		return $this->m_description;
	}
	
	public function setInstitution($value)
	{
		$this->checkFilled();
		$this->m_institution = $value;
	}
	
	/**
	 * The number of members in the group
	 * @return 
	 */
	public function getSize()
	{
		$this->checkFilled();
		if(!isset($this->m_members)) {
			$this->m_members = User::RetrieveUsersInGroup($this, $this->m_createdBy);
		}
		return count($this->m_members);
	}
	
	public function setTitle($title)
	{
		$this->checkFilled();
		$this->m_title = $title;
	}
	
	public function getTitle()
	{
		$this->checkFilled();
		return $this->m_title;
	}
	
	/* ** Factoy methods ** */
	
	/**
	 * 
	 * @return 
	 */
	public static function RetrieveGroupById($id)
	{
		$db = Database::getInstance();
		$sql = "SELECT * FROM groups WHERE id={$id}";
		$result = $db->query($sql);
		if ($row = $db->fetchArray($result)) {
			$group = self::createFromHashArray($row);
			return $group;
		}
	}

    //get group using title and institution id
	public static function RetrieveGroupByTitle($title, $institution)
	{
		$db = Database::getInstance();
		$sql = "SELECT * FROM groups WHERE title='{$title}' AND institution_id={$institution}";
        echo $sql;
		$result = $db->query($sql);
		if ($row = $db->fetchArray($result)) {
			$group = self::createFromHashArray($row);
			return $group;
		}
	}

	/**
	 * 
	 * @return 
	 */
	public static function RetrieveGroups(User $authUser, Institution $fromInstitution = null)
	{
		// Check what groups the authUser has right to viwe
		if($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN)) {
			if(isset($fromInstitution)) {
				$sqlWhere = " WHERE institution_id={$fromInstitution->getId()}";
			}
			else {
				$sqlWhere = '';
			}
		}
		elseif($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN)) {
			$sqlWhere = " WHERE institution_id={$authUser->getInstitution()->getId()}";
		}
		else {
			// No rights
			return null;
		}
		
		$db = Database::getInstance();
		$sql = 'SELECT * FROM groups' . $sqlWhere . ' ORDER BY title';
		$result = $db->query($sql);
		$groups = array();
		while ($row = $db->fetchArray($result)) {
			$groups[] = self::createFromHashArray($row);
		}
		return $groups;
	}

public static function RetrieveWhoCanSeeTemplate(Template $template, User $authUser)
	{
		// Check what users the authUser has right to view
		/*if($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN)) {
			$sqlWhere = '';
		}
		else*/if($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN)) {
			$sqlWhere = " AND institution_id={$authUser->getInstitution()->getId()}";
		}
		else {
			// No rights
			return null;
		}

		$db = Database::getInstance();
		$sql = "SELECT * FROM groups WHERE id IN (SELECT group_id FROM template_viewers WHERE template_id={$template->getId()})" . $sqlWhere . ' ORDER BY title';
		Debugger::debug($sql, 'Group::RetrieveWhoCanSeeTemplate', Debugger::LEVEL_SQL);
		$result = $db->query($sql);

		$groups = array();
		while($row = $db->fetchArray($result)) {
			$groups[] = self::createFromHashArray($row);
		}

		return $groups;
	}

	public static function RetrieveWhoCantSeeTemplate(Template $template, User $authUser)
	{
		// Check what users the authUser has right to view
		/*if($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN)) {
			$sqlWhere = '';
		}
		else*/if($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN)) {
			$sqlWhere = " AND institution_id={$authUser->getInstitution()->getId()}";
		}
		else {
			// No rights
			return null;
		}

		$db = Database::getInstance();
		$sql = "SELECT * FROM groups WHERE id NOT IN (SELECT group_id FROM template_viewers WHERE NOT group_id IS NULL AND template_id={$template->getId()})" . $sqlWhere . ' ORDER BY title';
		Debugger::debug($sql, 'Group::RetrieveWhoCantSeeTemplate', Debugger::LEVEL_SQL);
		$result = $db->query($sql);

		$groups = array();
		while($row = $db->fetchArray($result)) {
			$groups[] = self::createFromHashArray($row);
		}

		return $groups;
	}
	
	private static function createFromHashArray($hashArray)
	{
		$group = new Group($hashArray['id']);
		$group->m_title = $hashArray['title'];
		$group->m_description = $hashArray['description'];
		$group->m_institution = new Institution($hashArray['institution_id']);
		$group->m_createdTime = $hashArray['created_time'];
		$group->m_updatedTime = $hashArray['updated_time'];
		$group->m_updatedBy = new User($hashArray['updated_by']);
		$group->m_createdBy = new User($hashArray['created_by']);
		$group->m_filled = true;
		return $group;
	}
	
	/* ** DB Methods ** */
	
	/**
	 * Updates the database with this Group's details
	 * Will create a new entry if group doesn't exist in DB
	 */
	public function Save($authUser)
	{	
		parent::Save($authUser);
		
		// Add new members
		if(isset($this->m_membersToSave)) {
			$sqlValues = array();
			foreach($this->m_membersToSave as $member) {
				$sqlValues[] = "({$this->m_id}, {$member->getId()})";
			}
			$db = Database::getInstance();
			$sql = 'INSERT INTO group_members (group_id, user_id) VALUES' . join(',', $sqlValues);
			$db->query($sql);
		}
		
		// Add new members
		if(isset($this->m_membersToDelete)) {
			$sqlValues = array();
			foreach($this->m_membersToDelete as $member) {
				$sqlValues[] = "(group_id={$this->m_id} AND user_id={$member->getId()})";
			}
			$db = Database::getInstance();
			$sql = 'DELETE FROM group_members WHERE ' . join(' OR ', $sqlValues);
			echo $sql;
			$db->query($sql);
		}
	}
	
	/**
	 * 
	 * @return 
	 */
	protected function dbCreate()
	{
		$this->m_createdBy = $this->m_updatedBy;
		$this->m_createdTime = $this->m_updatedTime;
		
		$db = Database::getInstance();
		
		$data=array(
			'title' => $this->m_title,
			'description' => $this->m_description,
			'institution_id' => $this->m_institution->getId(),
			//"template_id" => $this->getTemplateId(),			
			'created_by' => $this->m_createdBy->getId(),
			'updated_by' => $this->m_updatedBy->getId(),
			'created_time' => Date::formatForDatabase($this->m_createdTime),
			'updated_time' => Date::formatForDatabase($this->m_updatedTime)
		);
		$db->perform('groups', $data);
		$this->m_id = $db->insertId();
	}
	
	protected function dbUpdate()
	{
		$db = Database::getInstance();
	
		$data=array(
			'title' => $this->getTitle(),
			//'template_id' => $this->getTemplateId(),			
			'institution_id' => $this->m_institution->getId(),
			'description' => $this->getDescription(),
			'updated_by' => $this->m_updatedBy->getId(),
			'updated_time' => Date::formatForDatabase($this->m_updatedTime)			
		);
		$db->perform("groups", $data, "update", "id=" . $this->getId());
	}
	
		/**
	 * Gets the data for this institution using the ID.
	 * Lazy loading. In most casse we only need the institution ID
	 * @return 
	 * @param $id Object
	 */
	protected function populateFromDB(){
		$db = Database::getInstance();
		$sql = "SELECT * FROM groups WHERE id={$this->m_id}";
		$result = $db->query($sql);
		
		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$group->m_title = $row['title'];
			$group->m_description = $row['description'];
			$group->m_institution = new Institution($row['institution_id']);
			$group->m_createdTime = $row['created_time'];
			$group->m_updatedTime = $row['updated_time'];
			$group->m_updatedBy = new User($row['updated_by']);
			$group->m_createdBy = new User($row['created_by']);
			$group->m_filled = true;
		}
	}

	public function delete(){
		$db = Database::getInstance();
		$sql="DELETE FROM user_group WHERE group_id=" . $this->getId();
		$db->query($sql);
		$sql="DELETE FROM groups WHERE id=" . $this->getId();
		$db->query($sql);		
		echo $this->getId();
	}
}