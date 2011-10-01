<?php
/**
 * Permission Manager
 * 
 * LICENSE: This is an Open Source Project
 * 
 * This class is responsible to handle login function in backoffice
 * 
 * @author     	Richard Garside [www.richardsprojects.co.uk]
 * @copyright  	2008 Rix Centre
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    	$Id: PermissionManager.class.php 783 2009-08-27 11:24:13Z richard $
 * @link       	NA
 * @since      	NA
 * 
 */

include_once('class/Logger.class.php');
include_once('SymbolLogin.class.php');

class PermissionManager
{	
	/* ** Constants ** */
	const USER_SUPER_ADMIN = 'super admin';
	const USER_INSTITUTION_ADMIN = 'admin';
	const USER_TEACHER = 'teacher';
	const USER_STUDENT = 'student';
	
	const RIGHT_ALL_ADMIN = 2;
	const RIGHT_GENERAL_ADMIN = 1;
	const RIGHT_NONE = 0;

	// Switch login error codes
	// Error codes
	const SWITCH_LOGIN_ERROR_NO_ERROR = -1;
	const SWITCH_LOGIN_ERROR_UNSPECIFIED = 0;
	const SWITCH_LOGIN_ERROR_INCORRECT_PASSWORD = 1;
	const SWITCH_LOGIN_ERROR_UKNOWN_USER_ID = 2;
	const SWITCH_LOGIN_ERROR_SWITCH_LOGIN_DISABLED = 3;
	const SWITCH_LOGIN_ERROR_SWITCH_LOGIN_BLOCKED = 4;
	const SWITCH_LOGIN_ERROR_CUSTOM = 5;

	/* ** Member data ** */
	private $m_userName;
	private $m_password;
	private $m_symbolLogin;
	private $m_userType;
	private $m_accessLevel;

	/* ** Constructor ** */
	
	/**
	 * Creates a new PermissionManager
	 * @param $userName string
	 * @param $userType string
	 */
	private function __construct($userName, $userType)
	{
		$this->m_userName=$userName;
		$this->setTypeAndRole($userType);
	}
	
	/* ** Accessors ** */
	
	/**
	 * Returns an array of all persistent data in this class (keys are same as DB field names)
	 * @return 
	 */
	public function getAllAsHashArray()
	{
		$hashArray = array();
	
		// Encrypt password for none student users
		if( isset($this->m_password) ) {
			$password = ($this->isMemberOf(self::USER_STUDENT)) ?
								$this->m_password :
								md5($this->m_password);
		}

		// Only add set properties to hash array
		if( isset($this->m_userName) ) $hashArray['username'] = $this->m_userName;
		if( isset($password) ) $hashArray['password'] = $password;
		if( isset($this->m_userType) ) $hashArray['userType'] = $this->m_userType;

		// Get symbol login details if they exist
		if(isset($this->m_symbolLogin)) {
			$hashArray = array_merge($hashArray, $this->m_symbolLogin->getAllAsHashArray());
		}

		return $hashArray;
	}
	
	public function getUserName(){ return $this->m_userName; }
	public function setUserName($value)
	{
		$this->m_userName = $value;
	}
	
	public function getUserType() { return $this->m_userType; }
	public function setUserType($userType, User $authUser) {
		$userType = self::checkUserType($userType, $authUser);
		$this->setTypeAndRole($userType);
	}
	
	public function getPassword()
	{
		// Get password if not currently in memory
		if(!isset($this->m_password)) {
			$db = Database::GetInstance();
			$sql = "SELECT password FROM user WHERE username='{$this->m_userName}'";
			$result = $db->query($sql);
			if($row = $db->fetchArray($result)) {
				$this->m_password = $row['password'];
			}
		}
		
		return $this->m_password;
	}

	/**
	 * Sets the password, ignores blank passwords and keeps existing one.
	 * @param <type> $value
	 */
	public function setPassword($value)
	{
		if(isset($value) && $value != '')$this->m_password = $value;
	}

	public function getSymbolLogin()
	{
		return $this->m_symbolLogin;
	}

	public static function HtmlSelectOptions(User $user = null)
	{
		$studentSelected = '';
		$studentValue = ' value="' . self::USER_STUDENT .'"';
		$teacherSelected = '';
		$teacherValue = ' value="' . self::USER_TEACHER .'"';
		$adminSelected = '';
		$adminValue = ' value="' . self::USER_INSTITUTION_ADMIN .'"';
		$superAdminSelected = '';
		$superAdminValue = ' value="' . self::USER_SUPER_ADMIN .'"';

		// One user type must be set if user exists
		if(isset($user)) {
			switch($user->getPermissionManager()->getUserType()) {
				case self::USER_STUDENT:
					$studentSelected = ' selected="selected"';
					$studentValue = ' value="0"';
					break;
				case self::USER_TEACHER:
					$teacherSelected = ' selected="selected"';
					$teacherValue = ' value="0"';
					break;
				case self::USER_INSTITUTION_ADMIN:
					$adminSelected = ' selected="selected"';
					$adminValue = ' value="0"';
					break;
				case self::USER_SUPER_ADMIN:
					$superAdminSelected = ' selected="selected"';
					$superAdminValue = ' value="0"';
					break;
			}
		}
		
		$html = '<option' . $studentValue . $studentSelected . '>Student</option>' .
				'<option' . $teacherValue . $teacherSelected . '>Teacher</option>' .
				'<option' . $adminValue . $adminSelected . '>Admin</option>' .
				'<option' . $superAdminValue . $superAdminSelected . '>Super Admin</option>';
		return $html;
	}
	
	/* ** Factory Methods ** */

	public static function Create($userName, $password, $userType, $authUser)
	{
		// User must have general admin to create a permission manager
		if(!$authUser->getPermissionManager()->hasRight(self::RIGHT_GENERAL_ADMIN)){
			return null;
		}

		// If a user attempts to give a user more permission than they're allowed then that user will be made a student.
		$userType = self::checkUserType($userType, $authUser);

		$pm = new PermissionManager($userName, $userType);
		$pm->m_password = $password;
		return $pm;
	}

	public static function CreateFromUserRecord($recordset)
	{
		// Create a Permission Manager for this user if valid details exist
		if(isset($recordset['username']) && isset($recordset['userType'])) {
			$pm = new PermissionManager($recordset['username'], $recordset['userType']);

			// Get symbol login details
			$pm->m_symbolLogin = new SymbolLogin($recordset['switch_shape'], $recordset['switch_photo'], $recordset['switch_enabled']);
			
			return $pm;
		}
		else {
			return null;
		}	
	}
	
	/* ** Public Login functions ** */
	
	public static function TextLogin($username, $password, $institutionUrlName)
	{
		$db = Database::getInstance();
		$encryptedPassword = md5($password);
		$sql = "SELECT ID FROM user where enabled=1 AND (username='{$username}' " .
					"AND (password='{$password}' OR (password='{$encryptedPassword}' AND userType<>'student'))) " .
					"AND institution_id IN (SELECT id FROM institution WHERE url='{$institutionUrlName}')";
		Debugger::debug($sql, 'Login SQL', Debugger::LEVEL_SQL);
		$result = $db->query($sql);
		
		if ( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			// A user exists with those credentials
			$_SESSION['userID'] = $row['ID'];
			Logger::Write('User logged in', Logger::TYPE_INFO, new User($row['ID']), $username);
			return true;
		}
		else {
			// No user with those credentials

			// Get institution so we can log this attempt in the right place
			$institution = Institution::RetrieveByName($institutionUrlName);

			Logger::Write("User login failed with password '{$password}'", Logger::TYPE_USER_MISTAKE, null, $username, $institution);
			return false;
		}
	}

	/**
	 * Logs the user in using data from the switch access
	 * @param int $userId
	 * @param int $switchLoginNumber
	 * @return bool
	 */
	public static function SwitchLogin($userId, $switchLoginNumber)
	{
		$db = Database::getInstance();

		// Check id is numeric
		if(!is_numeric($userId)) {
			return self::SWITCH_LOGIN_ERROR_UKNOWN_USER_ID;
		}

		// Check id exists and switch login allowed
		$sql = "SELECT switch_enabled, switch_shape, switch_photo, blocked FROM user WHERE enabled=1 AND id={$userId}";
		$result = $db->query($sql);
		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			// Check user has switch login enabled
			if ($row['switch_enabled'] != 1) {
				return self::SWITCH_LOGIN_ERROR_SWITCH_LOGIN_DISABLED;
			}
			elseif($row['switch_shape'] <=0 || $row['switch_photo'] <= 0) {
				// Password not set
				return self::SWITCH_LOGIN_ERROR_SWITCH_LOGIN_DISABLED;
			}
			// Check it's not blocked
			elseif ($row['blocked'] != 0) {
				return self::SWITCH_LOGIN_ERROR_SWITCH_LOGIN_BLOCKED;
			}
		}
		else {
			// No result means user doesn't exist
			return self::SWITCH_LOGIN_ERROR_UKNOWN_USER_ID;
		}

		// Test for brute force attack somehow (lock if detected)
		
		// Check $switchLoginNumber is correct
		$symbolLogin = new SymbolLogin($row['switch_shape'], $row['switch_photo']);
		if($switchLoginNumber == $symbolLogin->getShapePhotoNumber($userId)) {
			$_SESSION['userID'] = $userId;
			return self::SWITCH_LOGIN_ERROR_NO_ERROR;
		}
		else {
			return self::SWITCH_LOGIN_ERROR_INCORRECT_PASSWORD;
		}
	}
	
	public static function Logout()
	{
		unset($_SESSION['userID']);
	}
	
	/* ** Public methods ** */

	public function hasRight($right)
	{
		return ($this->m_accessLevel >= $right);
	}
	
	public function isMemberOf($userRole) {
		return $userRole == $this->m_userType;
	}

	/**
	 * Checks the authenticating user has permission to make this user type
	 * @param <type> $userType
	 * @param <type> $authUser
	 * @return <type>
	 */
	private static function checkUserType($userType, User $authUser)
	{
		// If a user attempts to give a user more permission than they're allowed then that user will be made a student.
		$authPM = $authUser->getPermissionManager();
		switch($userType) {
			case self::USER_STUDENT:
				break;
			case self::USER_TEACHER:
				if($authPM->isMemberOf(self::USER_TEACHER)) {
					throw new Exception('You are a teacher and can only create users of type student');
				}
				break;
			case self::USER_INSTITUTION_ADMIN;
				if( !($authPM->hasRight(self::RIGHT_ALL_ADMIN) || $authPM->isMemberOf(self::RIGHT_GENERAL_ADMIN)) ) {
					throw new Exception('You do not have enough rights to create an admin user');
				}
				break;
			case self::USER_SUPER_ADMIN;
				if(!$authPM->hasRight(self::RIGHT_ALL_ADMIN)) {
					throw new Exception('You do not have enough rights to create a super admin user');
				}
				break;
		}
		return $userType;
	}

	private function setTypeAndRole($userType)
	{
		$this->m_userType = $userType;
		// Set access level for this user
		switch($userType) {
			case self::USER_SUPER_ADMIN:
				$this->m_accessLevel = self::RIGHT_ALL_ADMIN;
				break;
			case self::USER_INSTITUTION_ADMIN:
			case self::USER_TEACHER:
				$this->m_accessLevel = self::RIGHT_GENERAL_ADMIN;
				break;
			case self::USER_STUDENT:
			default:
				$this->m_accessLevel = self::RIGHT_NONE;
				break;
		}
	}
}
?>
