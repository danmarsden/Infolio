<?php

/**
 * The User Class
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: User.class.php 849 2010-01-07 11:19:12Z richard $
 * @link       NA
 * @since      NA
*/

include_once('DatabaseObject.class.php'); 
include_once('Institution.class.php'); 
include_once('Page.class.php');
include_once('Tab.class.php');
include_once('class/si/Theme.class.php');
include_once('class/AssetCollection.class.php');
include_once('class/Database.php');
include_once('class/Date.class.php');
include_once('class/PermissionManager/PermissionManager.class.php');
include_once('class/si/Safe.class.php');

/**
 * Class User
 * This class will hold the details of user of this website. Extensible to student, admin, etc
 */
class User extends DatabaseObject
{
	/* ** Member data ** */
	
	// From User table
	private $m_firstName;
	private $m_lastName;
	private $m_email;
	private $m_description;
    private $m_share;
    private $m_share_hash;
	
	protected $m_profilePicture;
	
	protected $m_permissionManager;
	
	// From other table
	private $m_assetCollection;
	var $m_tabs;
	private $m_tabMenu;

	private $m_enabled;

	// Used
	protected $m_theme;
	
	/* ** Constructor ** */
	
	public function __construct($id = null)
	{
		parent::__construct($id);

		$this->m_theme = new Theme();
		$this->m_profilePicture = Image::GetPlaceHolder();
	}
	
	/* ** Accessors ** */

	/**
	 * Add an asset to this user's collection
	 * @param int $assetId
	 */
	public function addAsset(Asset $newAsset, $authUser = null)
	{
		// Check asset isn't already in collection
		$assets = $this->getAssetCollection();
		$duplicateFound = false;
		foreach($assets as $asset) {
			if( $newAsset->isSameAs($asset) ) {
				$duplicateFound = true;
				break;
			}
		}

		if($duplicateFound) {
			// Don't add it again
			return false;
		}
		else {
			$newAsset->assignToCollectionInDb($this, null, $authUser);
		}
	}

	/**
	 * Remove asset from this user's collection
	 * @param int $assetId
	 */
	public function removeAsset($assetId)
	{
		// Check asset is in collection
		$assets = $this->getAssetCollection()->getAssets();

		$assetExists = false;
		foreach($assets as $asset) {
			// TODO: Finish this bit
			if($asset->getId() == $assetId) {
				$assetExists = true;
				
				break;
			}
		}

		if($assetExists) {
			// Check it's not been used in content
			$db = DATABASE::getInstance();
			$sql = "SELECT picture0, picture1 FROM block WHERE page_id IN (SELECT id FROM page WHERE tab_id IN (SELECT id from tab WHERE user_id={$this->getId()}))";
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
			else {
				// Delete asset from collection
				$sql = "DELETE FROM collection WHERE asset_id={$assetId} AND user_id={$this->getId()}";
				$db->query($sql);
				return 'Asset removed';
			}
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
			'id'		=>	$this->getId(),
			'name'		=>	$this->getFullName()
		);
	}

	public function getAssetCollection()
	{
		if(!isset($this->m_assetCollection)) {
			$this->m_assetCollection = AssetCollection::CreateUserAssetCollection($this);
		}
		return $this->m_assetCollection;
	}
	
	public function getDescription()
	{
		$this->checkFilled();
		return $this->m_description;
	}
	public function setDescription($value)
	{
		$this->checkFilled();
		$this->m_description = $value;
	}
	
	public function getEmail()
	{
		$this->checkFilled();
		return $this->m_email;
	}
	public function setEmail($value)
	{
		$this->checkFilled();
		$this->m_email = $value;
	}
	
	public function getFirstName()
	{
		$this->checkFilled();
		return $this->m_firstName;
	}
	public function setFirstName($value)
	{
		$this->checkFilled();
		$this->m_firstName = $value;
	}

	public function getShare()
	{
		$this->checkFilled();
		return $this->m_share;
	}
	public function setShare($value)
	{
		$this->checkFilled();
		$this->m_share = $value;
	}

	public function getShareHash()
	{
		$this->checkFilled();
		return $this->m_share_hash;
	}
	public function setShareHash($value)
	{
		$this->checkFilled();
		$this->m_share_hash = $value;
	}

	/**
	 * The users full name. (Surname, Firstname)
	 */
	public function getFullName()
	{
		$this->checkFilled();
		return $this->m_lastName . ', ' . $this->m_firstName;
	}

	public function getInstitution()
	{
		$this->checkFilled();
		return $this->m_institution;
	}
 	public function setInstitution($value)
	{
		$this->checkFilled();
		$this->m_institution = $value;
	}

	public function getLastName()
	{
		$this->checkFilled();
		return $this->m_lastName;
	}
	public function setLastName($value)
	{
		$this->checkFilled();
		$this->m_lastName = $value;
	}

	public function getProfilePicture()
	{
		$this->checkFilled();
		return $this->m_profilePicture;
	}
	public function setProfilePicture(Image $value)
	{
		$this->checkFilled();
		$this->m_profilePicture = $value;
	}
	public function getProfilePictureId()
	{
		$this->checkFilled();
		return ( isset($this->m_profilePicture) ) ?
			$this->m_profilePicture->getId() :
			null;
	}
	public function setProfilePictureId($pictureId)
	{
		$this->checkFilled();
		$this->m_profilePicture = Image::RetrieveById($pictureId, $this);
	}

	public function getTheme()
	{
		$this->checkFilled();
		return $this->m_theme;
	}

	public function getUserName()
	{
		$this->checkFilled();
		return $this->m_permissionManager->getUserName();
	}
	public function setUserName($value)
	{
		$this->checkFilled();
		$this->m_permissionManager->setUserName($value);
	}
	
	/**
	 * Gets the tabs for a User
	 * @return Menu
	 */
	public function getTabMenu()
	{
		if(!isset($this->m_tabMenu)) {
			$this->fetchAndSetTabs();
		}
		
		return $this->m_tabMenu;
	}
	
	/**
	 * Gets an array of tabs for this user
	 * @return Tab-Array
	 */
	public function getTabs($tabIds = null)
	{
		if(!isset($this->m_tabs)) {
			$this->fetchAndSetTabs($tabIds);
		}
		
		return $this->m_tabs;
	}
	
	public function getTabById($id, $pageSortMethod)
	{
		if( !isset($this->m_tabs) ) {
			$this->fetchAndSetTabs();
		}
		foreach($this->m_tabs as $aTab)
		{
			if($aTab->getId() == $id)
			{
				$aTab->setPageSortMethod($pageSortMethod);
				return $aTab;
			}
		}

	}
	
	public function getTabByName($name, $pageSortMethod)
	{
		if( !isset($this->m_tabs) ) {
			$this->fetchAndSetTabs();
		}
	
		$name = Safe::StripUnwantedSlashes($name);
		Debugger::debug("Searching for tab {$name}", 'Tab-name');
		
		if( isset($this->m_tabs[$name]) ) {
			$this->m_tabs[$name]->setPageSortMethod($pageSortMethod);
			return $this->m_tabs[$name];
		}
	}
	
	public function getTabByPage($page)
	{
		if( !isset($this->m_tabs) ) {
			$this->fetchAndSetTabs();
		}
		
		// Loop through tabs to get page's tab
		$tabId = $page->getTab()->getId();
		foreach($this->m_tabs as $tab) {
			if ( $tab->getId() == $tabId ) {
				return $tab;
			}
		}
	}
	
	public function getPermissionManager()
	{
		$this->checkFilled();
		return $this->m_permissionManager;
	}
	
	/**
	 * Checks if a user exists with this username in the same institution
	 * @return 
	 */
	public function isUnique()
	{
		$sql = "SELECT COUNT(id) AS count FROM user WHERE username='{$this->getUsername()}' AND " .
			"institution_id={$this->getInstitution()->getId()}";
		$db = Database::GetInstance();
		$result = $db->query($sql);
		if($row = $db->fetchArray($result)) {
			return ($row['count'] < 1);
		}
		else {
			return false;
		}
	}
	
	/* ** Factory methods: Create new User objects ** */
	
	public static function CreateNewUser($firstName, $lastName, $email, 
							$description, $permissionManager, $institution)
	{
		$user = new User();
		$user->m_firstName = $firstName;
		$user->m_lastName = $lastName;
		$user->m_email = $email;
		$user->m_description = $description;
		$user->m_institution = $institution;
		$user->m_permissionManager = $permissionManager;
		$user->m_filled = true;
		
		return $user;
	}
	
	/**
	 * Gets a User object with the specified ID
	 * @param integer $id A user id
	 * @return User The requested user, or null if none exist with these details
	 */
	public static function RetrieveById($id)
	{
		//TODO: Check $authUser can do this
		return self::_getUserById($id);
	}
	
    /**
     * Gets a User object with the specified ID
     * @param integer $id A user id
     * @return User The requested user, or null if none exist with these details
     */
    public static function RetrieveByEmail($email, $institution)
    {
        //TODO: Check $authUser can do this
        return self::_getUserByEmail($email, $institution);
    }


	/**
	 * Gets a User object for a logged in user
	 * @param string $session The session user data
	 * @return User The requested user, or null if none exist with these details
	 */
	public static function RetrieveBySessionData($session)
	{
		if (isset($session['userID'])) {
			return self::_getUserById( Safe::SessionInput($session['userID']) );
		}else{
			return null;
		}
	}


	public function getFlashLoginCoords()
	{
		$sql = "SELECT * FROM graphical_password_coords WHERE graphical_passwords_id IN (SELECT id FROM graphical_passwords WHERE user_id={$this->getId()})";
		$db = Database::getInstance();

		$result = $db->query($sql);
		$passwordCoords = array();
		while($row = $db->fetchArray($result)) {
			$passwordCoords[] = array('x'=>$row['x'], 'y'=>$row['y']);
		}
		return $passwordCoords;
	}

	/**
	 * Gets the details this user needs to logon with using flash
	 * @param String $userName
	 */
	public function getFlashLoginPhoto()
	{
		// Get the user's login photo
		$sql = "SELECT id FROM assets WHERE id IN (SELECT picture_asset_id FROM graphical_passwords gp INNER JOIN user u ON u.id = gp.user_id where u.id={$this->getId()})";
		$db = Database::getInstance();

		$result = $db->query($sql);
		if($row = $db->fetchArray($result)) {
			$passwordPhoto = new Image($row['id']);
			return $passwordPhoto;
		}
	}

	/**
	 * Sets the graphical login details for this user
	 * @param <type> $pictureId
	 * @param <type> $coords
	 */
	public function setGraphicalLoginDetails($pictureId, Array $coords)
	{
		// Check if user already has a graphical password
		$sql = "SELECT id FROM graphical_passwords WHERE user_id={$this->getId()}";
		$db = Database::getInstance();

		$numCoords = count($coords);
		$result = $db->query($sql);
		if($row = $db->fetchArray($result)) {
			// User has graphical password
			$graphicalPasswordId = $row['id'];
			
			// Remove old points
			$sql = "DELETE FROM graphical_password_coords WHERE graphical_passwords_id={$graphicalPasswordId}";
			$db->query($sql);
			
			// Update main gaphical password entry
			$sql = "UPDATE graphical_passwords SET picture_asset_id={$pictureId}, click_number_of={$numCoords} WHERE id={$graphicalPasswordId}";
			$db->query($sql);

		}
		else {
			// User doesn't already have graphical password
			//
			// Create main gaphical password entry
			$sql = "INSERT INTO graphical_passwords (user_id, picture_asset_id, click_number_of, click_accuracy) VALUES ({$this->getId()}, {$pictureId}, {$numCoords}, 10)";
			$db->query($sql);
			
			$graphicalPasswordId = $db->insertId();
		}

		// Insert new points
		$insertValuesSQl = '';
		foreach($coords as $coord) {
			$insertValuesSQl .= '(' . $graphicalPasswordId . ',' . $coord['x'] . ',' . $coord['y'] . '),';
		}
		$insertValuesSQl = rtrim($insertValuesSQl, ',');

		$sql = 'INSERT INTO graphical_password_coords (graphical_passwords_id, x, y) VALUES ' . $insertValuesSQl;
		$db->query($sql);

		//Logger::Write($pictureId . ' ' . print_r($coords, true), Logger::TYPE_INFO, $user);
	}

	/**
	 * Gets the details this user needs to logon with using flash
	 * @param String $userName
	 */
	public static function RetrieveFlashLoginStuffForUser($userName, Institution $institution)
	{
		// Get the user's id
		$sql = "SELECT id FROM user WHERE enabled=1 AND username='{$userName}' AND institution_id={$institution->getId()}";
		Debugger::debug($sql, 'User::RetrieveFlashLoginStuffForUser - 1', Debugger::LEVEL_SQL);
		$db = Database::getInstance();

		$result = $db->query($sql);
		if($row = $db->fetchArray($result)) {
			$userId = $row['id'];
		}
		else {
			return 'err_msg=Wrong username';
		}

		// Get the user's login photo
		$sql = "SELECT id FROM assets WHERE id IN (SELECT picture_asset_id FROM graphical_passwords WHERE user_id={$userId})";
		Debugger::debug($sql, 'User::RetrieveFlashLoginStuffForUser - 2', Debugger::LEVEL_SQL);

		$result = $db->query($sql);
		if($row = $db->fetchArray($result)) {
			$passwordPhoto = new Image($row['id']);
			return "photo_url={$passwordPhoto->getFullHref(Image::SIZE_PHOTO_LOGIN)}";
		}
		else {
			return 'err_msg=You don\'t have a picture';
		}

	}

	public static function CheckFlashPhotoCoordVal($coordVal, $userName, Institution $institution)
	{
		// Get the number of clicks and click accuracy for this user
		$sql = "SELECT click_accuracy, click_number_of, user_id FROM graphical_passwords WHERE user_id IN (SELECT id FROM user WHERE username='{$userName}' AND institution_id={$institution->getId()})";
		$db = Database::getInstance();
		$result = $db->query($sql);
		if($row = $db->fetchArray($result)) {
			$clickAccuracy = $row['click_accuracy'];
			$numberOfClicks = $row['click_number_of'];
			$clickAccuracy *= $numberOfClicks;
			$userId = $row['user_id'];
		}

		// Get the coord password number for this user
		$sql = "SELECT SUM(x) + SUM(y) as photo_password FROM graphical_password_coords WHERE graphical_passwords_id IN (SELECT id FROM graphical_passwords WHERE user_id IN (SELECT id FROM user WHERE username='{$userName}' AND institution_id={$institution->getId()}))";
		$db = Database::getInstance();
		$result = $db->query($sql);
		if($row = $db->fetchArray($result)) {
			$photoPassword = $row['photo_password'];
		}

		$coordInputUnconfuscatedValue = intval($coordVal - ($numberOfClicks * 2875250003));

		// Check user password matches stored one (with give
		if($coordInputUnconfuscatedValue >= ($photoPassword - $clickAccuracy) && $coordInputUnconfuscatedValue <= ($photoPassword + $clickAccuracy)) {
			$_SESSION['userID'] = $userId;
			Logger::Write('User picture login', Logger::TYPE_INFO, new User($userId));
			return true;
		}
		else {
			Logger::Write('User failed picture login with ' . $numberOfClicks . ' clicks', Logger::TYPE_USER_MISTAKE, null, $userName, $institution);
			return false;
		}
	}

	public static function RetrieveUsers(User $authUser, Institution $fromInstitution = null)
	{
		// Check what users the authUser has right to view
		if($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN)) {
			if(isset($fromInstitution)) {
				$sqlWhere = " AND institution_id={$fromInstitution->getId()}";
			}
			else {
				$sqlWhere = '';
			}
		}
		elseif($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN)) {
			$sqlWhere = " AND institution_id={$authUser->getInstitution()->getId()}";
		}
		else {
			// No rights
			return null;
		}
		
		$db = Database::getInstance();
		$sql = 'SELECT * FROM user WHERE enabled=1 ' . $sqlWhere . ' ORDER BY lastName';
		Debugger::debug($sql, 'User::RetrieveUsers', Debugger::LEVEL_SQL);
		$result = $db->query($sql);
		
		$users = array();
		while($row = $db->fetchArray($result)) {
			$users[] = self::createFromHashArray($row);
		}
		
		return $users;
	}
	
	public static function RetrieveUsersInGroup(Group $group, User $authUser)
	{
		// Assuming authUser created group, so there are no users they can't see
		
		$db = Database::getInstance();
		$sql = "SELECT * FROM user WHERE enabled=1 AND id IN (SELECT user_id FROM group_members WHERE group_id={$group->getId()}) ORDER BY lastName";
		Debugger::debug($sql, 'User::RetrieveUsersInGroup', Debugger::LEVEL_SQL);
		$result = $db->query($sql);
		
		$users = array();
		while($row = $db->fetchArray($result)) {
			$users[] = self::createFromHashArray($row);
		}
		
		return $users;
	}
	
	public static function RetrieveUsersNotInGroup(Group $group, User $authUser, Institution $fromInstitution = null)
	{
		// Check what users the authUser has right to view
		if($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN ) && isset($fromInstitution)){
			$sqlWhere = " AND institution_id={$fromInstitution->getId()}";
		}
		elseif($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN)) {
			$sqlWhere = " AND institution_id={$authUser->getInstitution()->getId()}";
		}
		else {
			// No rights
			return null;
		}
		
		$db = Database::getInstance();
		$sql = "SELECT * FROM user WHERE enabled=1 AND id NOT IN (SELECT user_id FROM group_members WHERE group_id={$group->getId()})" . $sqlWhere . ' ORDER BY lastName';
		Debugger::debug($sql, 'User::RetrieveUsersNotInGroup', Debugger::LEVEL_SQL);
		$result = $db->query($sql);
		
		$users = array();
		while($row = $db->fetchArray($result)) {
			$users[] = self::createFromHashArray($row);
		}
		
		return $users;
	}

	public static function RetrieveUsersWhoCanSeeTemplate(Template $template, User $authUser)
	{
		// Check what users the authUser has right to view
		if($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN)) {
			$sqlWhere = " AND institution_id={$template->getInstitution()->getId()}";
		}
		else {
			// No rights
			return null;
		}

		$db = Database::getInstance();
		$sql = "SELECT * FROM user WHERE enabled=1 AND id IN (SELECT user_id FROM template_viewers WHERE template_id={$template->getId()})" . $sqlWhere . ' ORDER BY lastName';
		Debugger::debug($sql, 'User::RetrieveUsersWhoCanSeeTemplate', Debugger::LEVEL_SQL);
		$result = $db->query($sql);

		$users = array();
		while($row = $db->fetchArray($result)) {
			$users[] = self::createFromHashArray($row);
		}

		return $users;
	}

	public static function RetrieveUsersWhoCantSeeTemplate(Template $template, User $authUser)
	{
		// Check what users the authUser has right to view
		if($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN)) {
			$sqlWhere = " AND institution_id={$template->getInstitution()->getId()}";
		}
		else {
			// No rights
			return null;
		}

		$db = Database::getInstance();
		$sql = "SELECT * FROM user WHERE enabled=1 AND id NOT IN (SELECT user_id FROM template_viewers WHERE NOT user_id IS NULL AND template_id={$template->getId()})" . $sqlWhere . ' ORDER BY lastName';
		Debugger::debug($sql, 'User::RetrieveUsersWhoCantSeeTemplate', Debugger::LEVEL_SQL);
		$result = $db->query($sql);

		$users = array();
		while($row = $db->fetchArray($result)) {
			$users[] = self::createFromHashArray($row);
		}

		return $users;
	}

	private static function createFromHashArray($hashArray, User &$user=null)
	{
		if(!isset($user)) {
			$user = new User($hashArray['ID']);
		}
		
		$theme = new Theme( $hashArray['colour'], $hashArray['size'] );
		$profilePicture = Image::RetrieveById($hashArray['profile_picture_id'], $user);
		$permissionManager = PermissionManager::CreateFromUserRecord($hashArray);
		
		$user->m_firstName = $hashArray['firstName'];
		$user->m_lastName = $hashArray['lastName'];
		$user->m_email = $hashArray['email'];
		$user->m_description = $hashArray['description'];
		$user->m_institution = new Institution($hashArray['institution_id']);
		$user->m_enabled = $hashArray['enabled'];
		$user->setAuditFieldsFromHashArray($hashArray);

		if(isset($profilePicture))$user->m_profilePicture = $profilePicture;
		$user->m_profilePicture->setTitle("{$user->m_firstName}'s picture");
		
		$user->m_theme = $theme;
		$user->m_permissionManager = $permissionManager;
		$user->m_filled = true;
        //first check this users institutions share settings
        $inshare = $user->m_institution->allowSharing();
        if (empty($inshare)) {
            $user->m_share = 0;
        } elseif ($inshare == '2' && $hashArray['share'] !== '0') {
            $user->m_share = 1;
        } else {
            $user->m_share = $hashArray['share'];
        }
        if (isset($hashArray['sharehash'])) {
            $user->m_share_hash = $hashArray['sharehash'];
        }
        
		return $user;
	}
	
	private static function _getUserById($id)
	{
		$db = Database::getInstance();
		$sql = "SELECT * from user WHERE ID={$id} AND enabled=1";
		$result = $db->query($sql);
		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$user = self::createFromHashArray($row);
			return $user;
		}
	}

	private static function _getUserByEmail($email, $institution)
	{
		$db = Database::getInstance();
		$sql = "SELECT * from user WHERE email='{$email}' AND enabled=1 AND institution_id=".$institution->getId();
        $result = $db->query($sql);
		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$user = self::createFromHashArray($row);
			return $user;
		}
	}

	/* ** Useful static function ** */
	
	/**
	 * Logs a user in by storing session data.
	 * Doesn't create a valid User object. Use function GetUserBySessionData for this once logged in.
	 * @param string $username The username
	 * @param string $password The password
	 * @return bool Does a user exist with these credentials
	 */
	public static function Login($username, $password, $institionUrlName)
	{
		return PermissionManager::TextLogin($username, $password, $institionUrlName);
	}

	public static function LoginSwitch($userId, $switchLoginNumber)
	{
		return PermissionManager::SwitchLogin($userId, $switchLoginNumber);
	}

	/* ** Database operations ** */

	/**
	 * Deletes this user from the DB
	 * @param User $authUser A user with enough rights to delete this user
	 */
	public function Delete($authUser)
	{
		$db = Database::getInstance();
		$this->m_enabled = false;
		$this->Save($authUser);

		$this->m_id = null;
	}
	
	/**
	 * Gets the data for this user using the ID.
	 * Lazy loading. In most casse we only need the institution ID
	 * @return 
	 * @param $id Object
	 */
	protected function populateFromDB()
	{
		$db = Database::getInstance();
		$sql = "SELECT * FROM user WHERE id={$this->m_id}";
		$result = $db->query($sql);
		
		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$this->createFromHashArray($row, $this);
		}
	}
	
	protected function dbCreate()
	{
		$this->m_createdBy = $this->m_updatedBy;
		$this->m_createdTime = $this->m_updatedTime;
        $inshare = $this->m_institution->allowSharing();
        if (empty($inshare) or $inshare == '1') {
            $share = 0;
        } elseif ($inshare == '2') {
            $share = 1;
        }
		$data=array(
			'firstName' => $this->getFirstName(),
			'lastName' => $this->getLastName(),
			'email' => $this->m_email,
			'description' => $this->m_description,
			'institution_id' => $this->m_institution->getId(),
			'username' => $this->getUsername(),
			'updated_time' => Date::formatForDatabase($this->m_updatedTime),
			'updated_by' => $this->m_updatedBy->getId(),
			'created_time' => Date::formatForDatabase($this->m_createdTime),
			'created_by' => $this->m_createdBy->getId(),
			'profile_picture_id' => $this->getProfilePictureId(),
            'share' => $share,
            'sharehash' => $this->m_share_hash
		);
		
		// Add permission data to table, if it exists
		if(isset($this->m_permissionManager)) {
			$data = array_merge($data, $this->m_permissionManager->getAllAsHashArray());
		}
		
		// Write user to DB
		$db = DATABASE::getInstance();
		$db->perform("user", $data);
		
		// Get user's new DB ID
		$sql = "SELECT ID from user WHERE email='{$this->m_email}' ORDER BY ID DESC LIMIT 1";
		$result = $db->query($sql);
		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$this->m_id = $row['ID'];
		}
	}
	
	protected function dbUpdate()
	{
		$data=array(
				'firstName' => $this->getFirstName(),
				'lastName' => $this->getLastName(),
				'email' => $this->m_email,
				'description' => $this->m_description,
				'institution_id' => $this->m_institution->getId(),
				'enabled' => $this->m_enabled,
				'updated_time' => Date::formatForDatabase($this->m_updatedTime),
				'updated_by' => $this->m_updatedBy->getId(),
				'profile_picture_id' => $this->getProfilePictureId(),
                'share' => $this->m_share,
                'sharehash' => $this->m_share_hash
		);

		// Add permission data to table, if it exists
		if(isset($this->m_permissionManager)) {
			$data = array_merge($data, $this->m_permissionManager->getAllAsHashArray());
		}
		
		$db = DATABASE::getInstance();
		$db->perform("user", $data, Database::UPDATE, 'ID=' . $this->getId());
	}

	
    /* ** Display operations ** */

    /**
     * Set up the html for managing users tabs in the admin section
     *
     */
    public function HtmlManageTabs() {

        $this->fetchAndSetTabs(null, false, false);

        $html  = '<form method="post" action="/enabletabs.php">';
		$html .= '<input type="hidden" name="tab_count" value="' . count($this->m_tabs) . '" />';
        $html .= '<table width="50%">';
        $html .= '<thead><tr><th>&nbsp;</th><th>Enabled</th><th>Disabled</th></tr></thead>';
        $html .= '</tbody>';
        $index = 1;
		foreach($this->m_tabs as $aTab) {
            if($aTab->getId() != 1) {
                $enabled = $aTab->getEnabled();
                $html .= '<tr>';
                $html .= '<td><label><strong>'.$aTab->getName().'</strong></label></td>';
                if ($enabled) {
                    $html .= '<td><input type="radio" name="tab_id'.$index.'" value="enabled_'.$aTab->getId().'" checked=checked /></td>';
                    $html .= '<td><input type="radio" name="tab_id'.$index.'" value="disabled_'.$aTab->getId().'" /></td>';
                } else {
                    $html .= '<td><input type="radio" name="tab_id'.$index.'" value="enabled_'.$aTab->getId().'" /></td>';
                    $html .= '<td><input type="radio" name="tab_id'.$index.'" value="disabled_'.$aTab->getId().'" checked=checked /></td>';
                }
                $html .= '</tr>';
                $index++;
			}
		}
        $html .= '</table>';
        $html .= '<br/>';
		$html .= '<input type="hidden" name="user_id" value="' . $this->getId() . '" />';
        //if institution allows sharing show this
        $sharing =$this->m_institution->allowSharing();
        if (!empty($sharing)) {
            //first check if sharing is already set in user record (hacky way to do this
            $selected0 ='';
            $selected1 ='';
            if ($this->m_share==='1') {
                $selected1 = 'selected';
            } elseif ($this->m_share ==='0') {
                $selected0 = 'selected';
            } elseif ($sharing=='2') {
                $selected1 = 'selected';
            }
            $html .= '<h2>Tab Sharing</h2>
			      	  <select name="share" id="share">
                             <option value="0" '.$selected0.'>Disabled</option>
                            <option value="1" '.$selected1.'>Enabled</option>
                      </select><br/><br/>';
        }
		$html .= '<input type="submit" value="Update Tabs" />';
		$html .= '</form>';

		return $html;
    }

	public function HtmlExportProfileForm()
	{
		if(!isset($this->m_tabs)) {
			$this->fetchAndSetTabs();
		}

		$html = '<form method="post" action="/export.php"><ul style="list-style:none;">';
		$html .= '<input type="hidden" name="tab_count" value="' . (count($this->m_tabs)-1) . '" />';
		$tabCount = 0;
		foreach($this->m_tabs as $aTab) {
			if($aTab->getId() != 1) {
				$html .= '<li><label><input type="checkbox" name="tab_id' . $tabCount . '" value="' .$aTab->getId(). '" checked="checked" /> ' .$aTab->getName().  '</label></li>';
				$tabCount++;
			}
		}
		$html .= '</ul>';
        $html .= "<h3>Choose an export format</h3><div class=\"element\"><div><input type=\"radio\" class=\"radio\" id=\"export_formate091\" name=\"format\" tabindex=\"1\" value=\"html\" checked=\"checked\">"
                ."<label for=\"export_formate091\">Standalone HTML Website</label><div class=\"radio-description\">Creates a self-contained website with your portfolio data. You cannot import this again, but it's readable in a standard web browser.</div></div><div>"
                ."<input type=\"radio\" class=\"radio\" id=\"export_format442a\" name=\"format\" tabindex=\"1\" value=\"leap\"> <label for=\"export_format442a\">LEAP2A</label><div class=\"radio-description\">Gives you an export in the LEAP2A standard format. You can use this to import your data into other LEAP2A compliant systems, although the export is hard for humans to read.</div></div></div>";
		// Show link to last made zip file, if there is one
		$profileExportFile = DIR_FS_ROOT . 'staticversion/user-' . $this->getId() . '.zip';
		if(file_exists($profileExportFile))
		{
			$fileTime = filemtime($profileExportFile);
			$html .= '<p>This is an existing html export:</p>';
			$html .= "<ul><li><a href=\"/export/{$this->getId()}/\">Download last html export zip file</a> &ndash; " . Date::formatLongForScreen($fileTime) . "</li></ul>";
		}

		$html .= '<input type="hidden" name="user_id" value="' . $this->getId() . '" />';
		$html .= '<input type="submit" value="Generate new export file" />';

		$html .= '</form>';

		return $html;
	}

	/**
	 * Show the password setting form
	 * @param String $setPassword Contains the password if form has just been used to set it.
	 * @return String HTML of user password setting form
	 */
	private function htmlPasswordForm($passwordChanged)
	{
		$isStudentUser = $this->getPermissionManager()->isMemberOf(PermissionManager::USER_STUDENT);
		$password = ($isStudentUser) ? $this->getPermissionManager()->getPassword() : '';

		$html = '<form action="settings.php#password-sect" id="password-form" method="post">';

		// Saved message
		if($passwordChanged == 1) {
			$html .= '<p>Password saved.</p>';
		}

		$html .= '<label for="pass">Password <input type="text" id="pass" name="passwd" value="' . $password . '" /></label>';
		// Save button
		$html .= '<input type="image" src="/_images/si/icons/save.gif" value="Save" />';
		if(isset($_REQUEST['show'])) $html .= '<input type="hidden" id="hShow" name="show" value="true" />';
		$html .= '</form>';

		if(!$isStudentUser) {
			$html .= '<p>Existing password is not shown for admin users.</p>';
		}
		
		return $html;
	}

	/**
	 * An HTML list of log activity for a user.
	 * Side effect: clears old messages from system log (info kept for month, everything else kept for 3 months)
	 * @return <type>
	 */
	public function HtmlLogActivity()
	{
		// Delete old log messages (info kept for month, everything else kept for 3 months)
		$secondsInMonth = 3600 * 24 * 30;
		$monthAgo = Date::formatForDatabase( Date::now() - $secondsInMonth );
		$threeMonthsAgo = Date::formatForDatabase( Date::now() - ($secondsInMonth * 3) );

		$institutionId = $this->getInstitution()->getId();

		$sql = "DELETE FROM system_log WHERE institution_id={$institutionId} AND ((created_time<'{$monthAgo}' AND message_type='info') OR (created_time<'{$threeMonthsAgo}' AND NOT message_type='info' ))";
		$db = Database::getInstance();
		$db->query($sql);

		// Select user's log messages
		$sql = "SELECT * FROM system_log WHERE user_id = {$this->getId()} OR (username='{$this->getUserName()}' AND institution_id={$institutionId}) ORDER BY created_time DESC";
		$result = $db->query($sql);

		$html = "<h2>Activity for {$this->getFullName()}</h2>" .
				'<table class="dataGridTable tablesorter"><tr><th>Date</th><th>Type</th><th colspan="2">Activity</th></tr>';
		while($row = $db->fetchArray($result)) {
			$date = Date::formatShortForScreen( Date::varFromDatabase($row['created_time']) );
			$html .= "<tr class=\"{$row['message_type']}\"><td>{$date}</td><td>{$row['message_type']}</td><td>{$row['message']}</td></tr>";
		}
		$html .= '</table>';
		return $html;
	}

	/**
	 * Produces HTML with options for this user's password
	 * @return String HTML of user's password options
	 */
	public function HtmlPasswordOptions($passwordChanged = false)
	{
		$htmlString =	'<ul class="menu" id="password">' .
						'<li><a href="#password" id="btnShowPassword">Change my password</a><br />' .
						'<div class="hideBit">' . $this->htmlPasswordForm($passwordChanged) . '</div>' .
						'</li>' .
						'<li><a href="#">Print a password reminder</a></li>' .
						'</ul>';
		return $htmlString;
	}

	/**
	 * Produces HTML with details about this user to display on profile page
	 * @return String HTML of user's main details
	 */
	public function HtmlUserDetails(SimplePage $page, Theme $theme, $edit=false, $readonly=false)
	{
		$imgHtml = $this->m_profilePicture->Html(Image::SIZE_BOX);

		if (!$readonly) {
            $changeImgPath = $page->PathWithQueryString(array('a'=>HomePageEventDispatcher::ACTION_CHANGE_USER_IMAGE));
		    $changeImgLink = Link::CreateIconLink('Change picture', $changeImgPath, $theme->Icon('change-picture'), array('title' => 'Change picture'));
		    $html =	'<div class="box-image">' . $imgHtml .
						"<p class=\"box-admin\">{$changeImgLink->Html()}</p>" .
						'</div>' .
						'<div class="box-text">';
        } else {
            $html =	'<div class="box-image">' . $imgHtml .
                        '</div>' .
                        '<div class="box-text">';
        }
		if(!$edit) {
			$html .= str_replace("\n", '<br />', $this->getDescription());
            if (!$readonly) {
			    $changeTextPath = $page->PathWithQueryString(array('a'=>HomePageEventDispatcher::ACTION_CHANGE_USER_DESCRIPTION));
			    $changeTextLink = Link::CreateIconLink('Change description', $changeTextPath, $theme->Icon('edit'), array('title' => 'Change description'));

			    $html .= "<p>&nbsp;</p><p>{$changeTextLink->Html()}</p>";
            }
		}
		else {
			$html .= '<form action=".">';
			$html .= '<textarea name="description" rows="8">' . $this->getDescription() .'</textarea>';
			// Save button
			$html .= '<p><input type="image" src="/_images/si/icons/save.gif" value="Save" /></p>';
			$html .= '</form>';
		}

		// Add play button
		$html .= '<p class="playbutton2"><object type="application/x-shockwave-flash" data="/_flash/Play.swf?snd=/system/get.sound.php?blockid=home" width="50" height="50"><param name="movie" value="/_flash/Play.swf?snd=/system/get.sound.php?blockid=home" /></object></p>';

		$html .= '</div>';
		return $html;
	}

	/**
	 * Creates a mp3 file of the text in this block
	 */
	public function SpeechUserDetails()
	{
		$this->checkFilled();
		
		// Work out destination file name <id>-<mod-date>
		// This naming format lets us create a unique filename for block speech
		$fileName = '_voicedtext/user-' . $this->getId() . '-' . $this->getUpdatedTime();

		// Put all the word blocks into one string
		$text = $this->m_firstName . ' ' . $this->m_lastName . '. ' . $this->m_description;
		$text = strip_tags($text);
		$speech = new TextToSpeech($text, $fileName, DIR_FS_DATA);

		// Return the web location of the file
		return $speech->getFilePath();
	}
	
	/* ** Private methods ** */
	
	/**
	 * Fetches the tab info for this user from the DB and sets this classes tab related member data
	 * @return 
	 */
	function fetchAndSetTabs($tabIds = null, $enabled=true, $incltemplate=true)
    {
		// Populate $m_tabs with an array of Tab objects
		$this->m_tabs = Tab::RetrieveTabsByUser($this, $enabled, $incltemplate);

		// If specified only show the given tabIds (required for static version)
		if(isset($tabIds)) {
			$newTabs = array();
			foreach($this->m_tabs as $aTab) {
				if( in_array($aTab->getId(), $tabIds) || $aTab->getId() == 1 ) {
					$newTabs[$aTab->getName()] = $aTab;
				}
			}
			$this->m_tabs = $newTabs;
		}

		$linkArray = array();
		foreach ($this->m_tabs as $tab) {
			$linkArray[] = $tab->getLink();
		}

		// Create menu
		$this->m_tabMenu = new Menu($linkArray);
		$this->m_tabMenu->setClass('items');
	}
}
