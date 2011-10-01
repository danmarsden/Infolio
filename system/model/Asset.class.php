<?php

/**
 * The Asset Class
 *
 * An abstract class that must be extended by any class that represents a type of asset that 
 * is held in a user's collection. Like an image or video.
 * 
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: Asset.class.php 825 2009-12-14 09:28:52Z richard $
 * @link       NA
 * @since      NA
*/

include_once('DatabaseObject.class.php');
include_once('iFile.int.php');
include_once('class/si/Uploader.class.php');
include_once('model/Tag.class.php');

abstract class Asset extends DatabaseObject implements iFile
{
	protected $m_classes;
	protected $m_editLink;
	protected $m_href; // hold filename (without absolute path)
	protected $m_title;
	protected $m_description;
	protected $m_enabled;
	protected $m_tags;
	protected $m_type; // hold object type: image|video|audio
	protected $m_public = false;
	protected $m_folder;
	protected $m_system_folder;
	protected $m_currentSize = Image::SIZE_ORIGINAL;

	// calculated fields
	protected $m_timesUsed;

	// Foreign table fields
	protected $m_favourite;
	
	// Asset type constants
	const IMAGE = 'image';
	const VIDEO = 'video';
	const AUDIO = 'audio';
	
	/*public function __construct($id = null)
	{
		parent::__construct($id);
	}*/

	/**
	 * Produces an HTML full version of the asset.
	 * Abstract function that must be extended by base classes.
	 * @return 
	 */
	abstract public function Html();
	
	public function HtmlDescription()
	{
		return "<p>{$this->m_description}</p>";
	}
	
	public function HtmlTags()
	{
		$html = '';
		if( isset($this->m_tags) ) {
			foreach($this->m_tags as $tag) {
				$html .= "<li>{$tag}</li>";
			}
		}
		return '<ul>' . $html . '</ul>';
	}
	
	public function SwitchXml()
	{
		$assetType = get_class($this);

		//TODO: width and height need to be implemented as abstract method in Asset
		$xml = "<asset atype=\"{$assetType}\" id=\"{$this->getId()}\" alt=\"{$this->getTitle()}\" width=\"300\" height=\"300\" src=\"{$this->getFullHref(Image::SIZE_BOX, true)}\">" .
				"</asset>\n";
		return $xml;
	}
	
	/** Accessors **/
	
	/**
	 * Returns a list of names of users and groups who can see this asset.
	 */
	public static function getThoseWhoCanSeeMe($assetId)
	{
		$sql = "SELECT CONCAT(lastName, ', ', firstName) as name FROM user WHERE id IN (SELECT user_id FROM collection WHERE asset_id={$assetId}) UNION ALL SELECT title as name FROM groups WHERE id IN (SELECT group_id FROM collection WHERE asset_id={$assetId});";
		$db = Database::getInstance();
		$result = $db->query($sql);

		// Create an HTML list of users and groups
		$thoseWhoCanString = '<ul>';
		while($row = $db->fetchArray($result)) {
			$thoseWhoCanString .= '<li>' . $row['name'] . '</li>';
		}
		$thoseWhoCanString .= '</ul>';
		
		return $thoseWhoCanString;
	}

	/**
	 * Returns a simple object of this object. Is used for JSON making.
	 */
	protected function getAsObject()
	{
		// Not all images have owners
		$owner = $this->getCreatedBy();
		$ownerFullName = (isset($owner)) ?
							$owner->getFullName() :
							null;
		
		$data = (object) array(
			'id' => $this->getId(),
			'owner' => $ownerFullName,
			'name' => $this->getTitle(),
			'description' => $this->getDescription(),
			'href' => $this->getFullHref($this->m_currentSize),
			'date' => $this->getUpdatedTime(),
			'use_count' => $this->m_timesUsed,
			'view_public' => $this->m_public,
			'favourite' => $this->m_favourite
		);

		// Tag list
		if(isset($this->m_id)) {
			$tags = $this->getTagList();
			if(isset($tags)) {
				$data->tags = $tags;
			}
		}

		return $data;
	}

	public function setTitle($value){ $this->m_title=$value;}
	public function getTitle()
	{
		$this->checkFilled();
		return $this->m_title;
	}

	public function setDescription($value){ $this->m_description=$value;}
	public function getDescription()
	{
		$this->checkFilled();
		return $this->m_description;
	}

	/**
	 * Is this asset a favourite.
	 * Only works if this asset has been retrieved as part of a user collection.
	 * (Otherwise it doesn't know which user it is/isn't a favourite of)
	 * @return Boolean
	 */
	public function isFavourite()
	{
		return $this->m_favourite==true;
	}

	public function setFavourite($value, User $user) {
		$this->m_favourite = $value;

		//TODO: Commit this to database

		// Work out if this is already a favourite
		$db = DataBase::getInstance();
		$sql = "SELECT id FROM favourite_assets WHERE asset_id={$this->getId()} AND user_id={$user->getId()}";
		$result = $db->query($sql);

		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$favouriteId = $row['id'];
		}

		// If record exists and is no longer a favourite
		if(isset($favouriteId) && !$this->m_favourite) {
			// Delete record
			$sql = "DELETE FROM favourite_assets WHERE id={$favouriteId}";
			$db->query($sql);
		}
		// Record doesn't exist and is now a favourite
		elseif ( !isset($favouriteId) && $this->m_favourite  ) {
			// Create record
			$sql = "INSERT INTO favourite_assets(user_id, asset_id) VALUES({$user->getId()}, {$this->getId()})";
			$db->query($sql);
		}
	}

	public function isPublic()
	{
		return $this->m_public;
	}

	public function setPublic($value)
	{
		$this->checkFilled();
		$this->m_public = ($value == true);
	}

	public function addClass($className)
	{
		if( !is_array($this->m_classes) ) {
			$this->m_classes = array();
		}
		$this->m_classes[] = $className;
	}

	/**
	 * Adds a tag to the list of tags for this asset
	 * @return 
	 * @param $value String The tag to be added
	 */
	public function addTag(Tag $tag, User $forUser = null)
	{
		// Get the array
		$tags = $this->getTags($forUser);

		// Check tag not already used
		foreach($tags as $aTag) {
			// Return from function if alrady there
			if($tag->isSameAs($aTag)) return false;
		}
		
		// Add tag to array
		$userKey = Tag::UserKey($forUser);
		$this->m_tags[$userKey][] = $tag;

		// Save new tag
		$data = array(
			'asset_id' => $this->m_id,
			'tag_id' => $tag->getId()
		);
		if(isset($forUser)) {
			$data['user_id'] = $forUser->getId();
		}
		
		$db = Database::getInstance();
		$db->perform('tags_assets', $data, Database::INSERT);

		return $this->getTagList();
	}

	public function RemoveTag(Tag $tag, User $forUser=null)
	{
		$db = Database::getInstance();
		$userSql = (isset($forUser)) ? " AND user_id={$forUser->getId()}" :
									'';
		
		$sql = "DELETE FROM tags_assets WHERE asset_id={$this->getId()} AND tag_id={$tag->getId()}{$userSql} LIMIT 1";
		$db->query($sql);
	}
	
	/**
	 * Get the tags for this asset
	 * @return String-Array All tags
	 */
	public function getTags(User $forUser=null, Institution $institution=null)
	{
		$userKey = Tag::UserKey($forUser);

		// Check if we've already queried this
		if(!isset($this->m_tags[$userKey])) {
			$this->m_tags[$userKey] = Tag::RetrieveByAsset($this, $forUser);
		}
		return $this->m_tags[$userKey];
	}

	public function getTagList(User $forUser=null)
	{
		$tags = $this->getTags($forUser, $this->m_createdBy->getInstitution());
		if(count($tags)>0) {
			$tagNames = array();
			foreach($tags as $tag) {
				$tagNames[] = $tag->getName();
			}
			return implode(',', $tagNames);
		}
	}


	public function setEditLink(Link $editLink)
	{
		$this->m_editLink = $editLink;
	}

	//public function setFolder($value){ $this->m_folder=$value;}
	//public function getFolder(){ return $this->m_folder;}

	public function getFullHref()
	{
		return $this->m_folder . $this->m_href;
	}

	public function setHref($value){ $this->m_href=$value;}
	public function getHref()
	{
		$this->checkFilled();
		return $this->m_href;
	}
	
	public function setFileType($value){ $this->m_type=$value;}
	public function getFileType(){ return $this->m_type;}				
	
	protected function setFolders($institutionUrl)
	{
		if(!isset($institutionUrl))
		{
			$institutionUrl = 'shared';
		}
		// Set system folder based on institute
		$this->m_folder = DIR_WS_DATA . $institutionUrl . '-asset/' . $this->m_type . '/';
		$this->m_system_folder = DIR_FS_DATA . $institutionUrl . '-asset/' . $this->m_type . '/';
	}
	public function getSystemFolder(){ return $this->m_system_folder;}
	public function getFolder() { return $this->m_folder; }

	public function getType() { return $this->m_type; }

	/**
	 * Works out the type of an asset based on file extension.
	 * @param <type> $filename
	 * @return <type>
	 */
	public static function getAssetType($fileName)
	{
		$extension = Uploader::FindFileExtension($fileName);

		$videoExt = explode(',', 'avi,mp4,mpeg,mpg,flv,mov,wmv');
		$audioExt = explode(',', 'mp3,wav');
		$imageExt = explode(',', 'bmp,jpg,png,gif,jpeg');

		if(in_array($extension, $videoExt)) {
			return self::VIDEO;
		}
		elseif(in_array($extension, $audioExt)) {
			return self::AUDIO;
		}
		elseif(in_array($extension, $imageExt)) {
			return self::IMAGE;
		}
		else {
			return $extension;
		}
	}

	public function getFileNamePart()
	{
		$fileBits = explode(".", $this->m_href);
		array_pop($fileBits);

		// Use implode incase filename has more dots in it
		return implode('.', $fileBits);
	}
	
	public function isExist(){
		$filePath = $this->getSystemFolder() . $this->m_href;
		return file_exists($filePath);
	}

	/**
	 *
	 * @param <type> $hashArray
	 * @param <type> $asset
	 * @return <type> 
	 */
	protected static function createFromHashArray($hashArray, Asset &$asset=null)
	{
		$fileType = self::getAssetType($hashArray['href']);

		// Create new asset of correct type
		if(!isset($asset)) {
			switch( $fileType ){
				case self::IMAGE:
					$asset = new Image($hashArray['id']);
					break;
				case self::VIDEO:
					//will be change to appropriate class
					$asset = new Video($hashArray['id']);
					break;
				case self::AUDIO:
					$asset = new Audio($hashArray['id']);
					break;
				default:
					Logger::Write("Failed to create asset of type {$fileType}", Logger::TYPE_ERROR);
					return null;
					break;
			}
		}

		$asset->setHref( $hashArray['href'] );
		$asset->setTitle( $hashArray['title'] );
		$asset->setDescription( $hashArray['description'] );
		$asset->m_enabled = $hashArray['enabled'];
		$asset->setWidth( $hashArray['width'] );
		$asset->setHeight( $hashArray['height'] );
		//$asset->setAllTags( split(',', $hashArray['tag']) );
		$asset->m_public = ($hashArray['public'] == 1);

		// Favourites
		if(isset($hashArray['favourite'])) {
			$asset->m_favourite = ($hashArray['favourite'] == 1);
		}

		// Set system folder based on institute
		if ( isset($hashArray['institution_url']) ) {
			$asset->setFolders($hashArray['institution_url']);
		}

		if ( isset($hashArray['count']) ) {
			$asset->m_timesUsed = $hashArray['count'];
		}
		
		
		$asset->setAuditFieldsFromHashArray($hashArray);
		$asset->m_filled = true;
		return $asset;
	}

	/**
	 * Create an array of assets from a database resultset
	 * @param <type> $result
	 * @param <type> $dbInstance
	 * @return <type>
	 */
	protected static function createArrayFromResultSet($result, $dbInstance)
	{
		$assets = array();
		while( $row = $dbInstance->fetchArray($result, MYSQL_ASSOC) ) {
			$assets[$row['id']] = self::createFromHashArray($row);
		}

		return $assets;
	}

	public static function CreateJsonString($dbObjectArray, $imageSizeToGenerate=null)
	{
		// Create images (if a generate size is specified)
		if(isset($imageSizeToGenerate)) {
			self::setImagesPreviewSizes($dbObjectArray, $imageSizeToGenerate);
		}

		// Call parent to generate JSON
		return parent::CreateJsonString($dbObjectArray, null);
	}

	public static function CreateXmlString($dbObjectArray, $imageSizeToGenerate=null)
	{
		// Create images (if a generate size is specified)
		if(isset($imageSizeToGenerate)) {
			self::setImagesPreviewSizes($dbObjectArray, $imageSizeToGenerate);
		}

		// Call parent to generate JSON
		return parent::CreateXmlString($dbObjectArray, null);
	}

	public function setPreviewSize($value)
	{
		$this->m_currentSize = $value;
	}

	private static function setImagesPreviewSizes($dbObjectArray, $imageSizeToGenerate)
	{
		foreach($dbObjectArray as $item) {
			if( isset($item) && $item->m_type == self::IMAGE ) {
				$item->m_currentSize = $imageSizeToGenerate;
			}
		}
	}

	/**
	 * Create a new asset with correct type based on href
	 * @param <type> $href
	 * @param <type> $title
	 * @return <type>
	 */
	public static function CreateNew($href, User $user)
	{
		$type = self::getAssetType($href);
		switch($type)
		{
			case self::IMAGE:
				$asset = new Image(null);
				break;
			case self::VIDEO:
				$asset = new Video(null);
				break;
			case self::AUDIO:
				$asset = new Audio(null, null);
				break;
			default:
				Throw new Exception("Can't upload {$type} files");
				break;
		}

		$asset->m_href = $href;
		$asset->m_enabled = true;
		$asset->m_public = false;
		$asset->m_title = $asset->getFileNamePart();
		$asset->setFolders($user->getInstitution()->getUrl());

		return $asset;
	}

	/**
	 * Gets the data for asset user using the ID.
	 * Lazy loading. In most casse we only need the institution ID
	 * @return
	 * @param $id Object
	 */
	protected function populateFromDB()
	{
		$db = Database::getInstance();
		$sql = "SELECT * FROM vassetswithcounts WHERE id={$this->m_id}";
		$result = $db->query($sql);

		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$this->createFromHashArray($row, $this);
		}
	}

	/**
	 * Create a collection of assets from the SQL query
	 * @param <type> $sql 
	 */
	private static function retrieveArray($sql)
	{
		$db = Database::getInstance();
		$result = $db->query($sql);

		$assets = array();
		while($row = $db->fetchArray($result)) {
			$assets[$row['id']] = self::createFromHashArray($row);
		}

		// Return asset list if there are any
		return (count($assets) > 0) ?
			$assets :
			null;
	}

	/**
	 * Get the SQL WHERE part to make sure admin user only sees their stuff
	 * @param <type> $authUser
	 * @param <type> $fromInstitution
	 * @return <type> An SQL WHERE clause without the WHERE keyword
	 */
	private static function whereSqlAuth(User $authUser, Institution $fromInstitution = null)
	{
		if($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN)) {
			if(isset($fromInstitution)) {
				return " institution_id={$fromInstitution->getId()}";
				//return "institution_id={$fromInstitution->getId()}";
			}
			else {
				return null;
			}
		}
		elseif($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN)) {
			return " institution_id={$authUser->getInstitution()->getId()}";
			//return "institution_id={$authUser->getInstitution()->getId()}";
		}
		else {
			// No rights
			return 'id=-1';
		}
	}

	public static function RetrieveAll(User $authUser, Institution $fromInstitution = null)
	{
		// Check what users the authUser has right to view
		$sqlWhere = self::whereSqlAuth($authUser, $fromInstitution);
		if(isset($sqlWhere)) $sqlWhere = ' WHERE ' . $sqlWhere;

		$sql = 'SELECT * FROM vassetswithcounts' . $sqlWhere . ' ORDER BY updated_time DESC';

		Debugger::debug($sql, 'Asset::RetrieveAll', Debugger::LEVEL_SQL);
		return self::retrieveArray($sql);
	}

		/**
	 * Creates a new Image class instance with data from the DB
	 * @return
	 * @param $id Object
	 */
	public static function RetrieveById($id, $authUser=null)
	{
		if(!is_numeric($id)) {
			return;
		}
		
		if(isset($id)) {
			$db = DataBase::getInstance();
			$sql = "SELECT * FROM vassetswithcounts WHERE id={$id}";
			Debugger::debug("SQL: $sql", 'Asset::RetrieveById', Debugger::LEVEL_SQL);
			$result = $db->query($sql);

			if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
				return self::createFromHashArray($row);
			}
		}

		//ToDo: Check $authUser can get this image
	}

	/**
	 * Creates an array new Image classes with data from the DB (not sure it's used)
	 * @return
	 * @param $idArray Int-Array
	 * @param $authUser User A user that should have permission to view this image
	 */
	public static function RetrieveByIds($idArray, $authUser)
	{
		$db = DataBase::getInstance();
		$sql = 'SELECT * FROM vassetswithcounts WHERE id IN (' . implode(',', $idArray) . ')' .
				'  ORDER BY updated_time DESC';
		Debugger::debug("SQL: $sql", 'Asset::RetrieveByIds', Debugger::LEVEL_SQL);
		$result = $db->query($sql);

		//ToDo: Check $authUser can get this image

		return self::createArrayFromResultSet($result, $db);
	}

	public static function RetrieveByTag(Tag $tag, User $user=null)
	{
		$db = DataBase::getInstance();
		$sql = "SELECT * FROM vassetswithcounts WHERE id IN (SELECT asset_id FROM tags_assets WHERE tag_id={$tag->getId()}";

		// Also check owned by right user, if one is specified
		if(isset($user)) $sql .= " AND user_id={$user->getId()}";

		// Ordering
		$sql .= ') ORDER BY updated_time DESC';

		Debugger::debug("SQL: $sql", 'Asset::RetrieveByTag', Debugger::LEVEL_SQL);
		$result = $db->query($sql);

		return self::createArrayFromResultSet($result, $db);
	}

	public static function RetrieveCreatedByAdmin(User $adminUser, Institution $fromInstitution = null)
	{
		// Check what users the authUser has right to view
		$sqlWhere = self::whereSqlAuth($adminUser, $fromInstitution);
		if(isset($sqlWhere)) $sqlWhere = ' AND ' . $sqlWhere;

		$sql = 'SELECT * FROM vassetswithcounts WHERE enabled=1 AND created_by=' . $adminUser->getId() . $sqlWhere .
				' ORDER BY updated_time DESC';

		Debugger::debug($sql, 'Asset::RetrieveCreatedByAdmin', Debugger::LEVEL_SQL);
		return self::retrieveArray($sql);
	}

/**
	 * Gets all the assets a user can use.
	 */
	public static function RetrieveGroupAssets($group, $filterSql = null)
	{
		if( isset($filterSql) && $filterSql != '') {
			$filterSql = ' AND ' . $filterSql;
		}

		// SQL query
		$sql = "SELECT * FROM vassetswithcounts WHERE enabled=1 AND id IN (SELECT asset_id FROM collection WHERE group_id={$group->getId()}){$filterSql}" .
				' ORDER BY updated_time DESC';
		Debugger::debug("SQL: $sql", 'Asset::RetrieveGroupAssets_1', Debugger::LEVEL_SQL);

		return self::retrieveArray($sql);
	}

	public static function RetrieveRecent(User $authUser, Institution $fromInstitution = null, $limit = 20)
	{
		// Check what users the authUser has right to view
		$sqlWhere = self::whereSqlAuth($authUser, $fromInstitution);
		if(isset($sqlWhere)) $sqlWhere = ' AND ' . $sqlWhere;

		$sql = 'SELECT * FROM vassetswithcounts WHERE enabled=1' . $sqlWhere . ' ORDER BY created_time DESC LIMIT ' . $limit;

		Debugger::debug($sql, 'Asset::RetrieveRecent', Debugger::LEVEL_SQL);
		return self::retrieveArray($sql);
	}

	public static function RetrieveRemovedAndOlderThan3Months(Institution $institution, User $authUser)
	{
		if( $authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN) ) {
			$threeMonthsAgo = Date::formatForDatabase( strtotime("-3 month") );
			$sql = "SELECT * FROM assets WHERE enabled=0 AND updated_time < '{$threeMonthsAgo}' AND created_by IN (SELECT id FROM user WHERE institution_id=1)";
			$assets = self::retrieveArray($sql);

			// Need to add this here as institution url not present in view we've requested
			$institutionUrl = $institution->getUrl();
			foreach($assets as $asset) {
				$asset->setFolders($institutionUrl);
			}
			return $assets;
		}
	}

	/**
	 * Retrieve all assets that can't be seen by any users
	 * @param <type> $authUser
	 * @param <type> $fromInstitution
	 * @return <type> 
	 */
	public static function RetrieveUnassigned(User $authUser, Institution $fromInstitution = null)
	{
		// Check what users the authUser has right to view
		$sqlExtraWhere = self::whereSqlAuth($authUser, $fromInstitution);
		if(isset($sqlWhere)) $sqlExtraWhere = ' AND ' . $sqlWhere;

		$sql = 'SELECT * FROM vassetswithcounts WHERE enabled=1 AND count=0' . $sqlExtraWhere .
				' ORDER BY updated_time DESC';
		Debugger::debug($sql, 'Asset::RetrieveUnassigned', Debugger::LEVEL_SQL);
		return self::retrieveArray($sql);
	}

	/**
	 * Gets all the assets a user can use.
	 * Also works out which have been marked as faveourites
	 */
	public static function RetrieveUsersAssets($user, $filterSql = null)
	{
		Debugger::debug('Called', 'Asset::RetrieveUsersAssets', Debugger::LEVEL_INFO);

		if( isset($filterSql) && $filterSql != '') {
			$filterSql = ' AND ' . $filterSql;
		}

		// Query the DB
		$sql = "SELECT assets.*, collection.created_time as assigned_time, COALESCE(faves.favourite, false) as favourite FROM vassetswithcounts as assets " .
				"INNER JOIN collection ON assets.id = collection.asset_id ".
				"LEFT OUTER JOIN (SELECT asset_id, true AS favourite FROM favourite_assets WHERE user_id={$user->getId()}) faves ON assets.id = faves.asset_id " .
				"WHERE enabled=1 AND (assets.id IN (SELECT asset_id FROM collection WHERE user_id={$user->getId()} OR group_id IN (SELECT group_id FROM group_members WHERE user_id={$user->getId()}))){$filterSql} " .
				"ORDER BY updated_time DESC";

		Debugger::debug("SQL: $sql", 'Asset::RetrieveUsersAssets_1', Debugger::LEVEL_SQL);
		return self::retrieveArray($sql);
	}
	
	/**
	 * Get file extension of a file
	 * @return file extension
	 */
	public function getFileExtension()
	{
		return Uploader::FindFileExtension($this->getHref());
	}

	public static function replaceFileExtension($fileName, $newExtension)
	{
		$fileBits = explode('.', $fileName);
		array_pop($fileBits);
		array_push($fileBits, $newExtension);

		return implode('.', $fileBits);
	}
	
	/**
	 * This function will move temporary uploaded file to the 
	 * correct folder according to the type
	 * @return 
	 */
	public function moveTempFileToAssetFolder(){
		$destinationFile = DIR_FS_DATA_ASSET . $this->m_type . "/" . basename($this->m_href);
		return copy(DIR_FS_CACHE . $this->m_href, $destinationFile);
	}
	
	/* ** Database methods ** */
	
	public function assignToCollectionInDb(User $user=null, Group $group=null, User $authUser=null)
	{
		$createdBy = (isset($authUser)) ? $authUser : $user;

		if(isset($createdBy)) {
			$data=array(
				'asset_id' => $this->m_id,
				'created_by' => $createdBy->getId(),
				'created_time' =>Date::formatForDatabase(Date::Now())
			);
			
			// Assign to user or group
			if( isset($user) ) {
				$data['user_id'] = $user->getId();
			}
			elseif ( isset($group) ) {
				$data['group_id'] = $group->getId();
			}
			else {
				throw new Exception('Asset::assignToCollectionInDb Must specify user or group to assign asset to');
			}
			
			$db = DATABASE::getInstance();
			$db->perform('collection', $data);
		}
		else {
			print 'Can\'t assign asset to collection';
			Logger::Write("Can't assign asset {$this->getTitle()} ($this->getId()) to collection", Logger::TYPE_ERROR);
		}
	}
	
	public function update()
	{
		$this->Save();
	}
	
	protected function dbCreate()
	{
		// Updater = creator
		$this->m_createdTime = $this->m_updatedTime;
		$this->m_createdBy = $this->m_updatedBy;
		
		$db = Database::getInstance();
		$data=array(
			'href' => $this->m_href,
			//'tag' => $this->m_tags,
			'title' => $this->m_title,
			'type' => $this->m_type,
			'description' => $this->m_description,
			'enabled' => $this->m_enabled,
			'public' => $this->m_public,
			'updated_by' => $this->m_updatedBy->getId(),
			'updated_time' => Date::formatForDatabase($this->m_updatedTime),
			'created_by' => $this->m_createdBy->getId(),
			'created_time' => Date::formatForDatabase($this->m_createdTime)
		);
		$db->perform('assets', $data);
		$this->m_id = $db->insertId();
	}

	protected function dbUpdate()
	{
		$db = Database::getInstance();
		$data=array(
					'href' => $this->m_href,
					//'tag' => $this->m_tags,
					'enabled' => $this->m_enabled,
					'public' => $this->m_public,
					'title' => $this->m_title,
					'type' => $this->m_type,
					'updated_by' => $this->m_updatedBy->getId(),
					'updated_time' => Date::formatForDatabase($this->m_updatedTime));
		$db->perform('assets', $data, Database::UPDATE, "id={$this->getId()}" );
		$this->m_id = $this->getId(); // TODo: check this line is required
	}
	
	/**
	 * Removes the asset from this user's collection,
	 * and disables it, if it's owned by the user.
	 * @return 
	 */
	public function DeleteForUser(User $user, User $authUser)
	{
		$db = Database::getInstance();

		// Check who owns the asset
		if(!($this->m_public || $this->getCreatedBy()->getId() != $user->getId())) {
			// private asset
			Debugger::debug("Public: {$this->m_public}, Owner: {$this->getCreatedBy()->getId()}, User: {$user->getId()}", 'Asset::DeleteForUser');

			// Disable the asset
			$this->m_enabled = false;
			$this->Save($authUser);
		}

		// Remove from user's collection
		$sql = "DELETE FROM collection WHERE user_id={$user->getId()} AND asset_id={$this->getId()}";
		$db->query($sql);

		// Log it
		Logger::Write("Deleted asset {$this->m_href} (id:{$this->getId()}) from user collection",Logger::TYPE_DELETE, $user);
	}

	public function RestoreForUser(User $user, User $authUser)
	{
		// Re-enable asset
		$this->m_enabled = true;
		$this->Save($authUser);

		// Re-add asset to their collection
		$user->addAsset($this, $authUser);

		return $this->getId();
	}
}
