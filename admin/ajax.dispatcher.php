<?php

/**
 * AJAX FUNCTION FOR LOGIN
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Elvir Leonard
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: ajax.dispatcher.php 814 2009-11-04 22:14:39Z richard $
 * @link       NA
 * @since      NA
 */

include_once('../system/initialiseBackOffice.php');
include_once('../system/model/Institution.class.php');
include_once('../system/model/User.class.php');


/** cast variable called operation; this will be used on switch statement **/
$operation = (isset($_REQUEST['operation']))? $a = strtolower($_REQUEST['operation']) : '';
$a = (isset($_REQUEST['a'])) ? $a = strtolower($_REQUEST['a']) : '';

// Check user is logged in before letting them do stuff (except logging in)
if( isset($_SESSION) ) {
	$adminUser = User::RetrieveBySessionData($_SESSION);
	
	// Nullify user if they don't have permission
	if( isset($adminUser) &&  !$adminUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN) ) {
		$adminUser = null;
	}
}

// Stop, if user not valid
if(!isset($adminUser) && $a != 'login' ) {
	die('User not logged in');
}

/**
 * Login function
 * Todo: Log to access_log table on db
 */

switch($a) {
	/* ** ** ** Login ** ** ** */
	case 'login':
		$inpUserName = Safe::Input($_GET["username"]);
		$inpPassword = Safe::Input($_GET["password"]);
		$inpInstitution = Safe::Input($_GET["institution"]);
		print login($inpUserName, $inpPassword, $inpInstitution);
		break;

	case 'institution':
		institutionOperations($operation, $adminUser, $_POST);
		break;

	case 'user':
		userOperations($operation, $adminUser);
		break;

	case 'tab':
		include_once(DIR_FS_MODEL . "Template.class.php"); 
		include_once(DIR_FS_MODEL . "Tab.class.php"); 
		include_once(DIR_FS_MODEL . "Page.class.php"); 	
		
		switch($operation){
			case 'view':
				$template = new Template($_GET["templateId"]);
				$pages = $template->getPages();
				print Template::CreateJsonString($pages);
				break;
			case 'insert':
				$tab = new Tab(null, $_GET["name"]);
				$tab->setDescription($_GET["description"]);
				$tab->setTemplateId($_GET["templateId"]);
				$tab->setOwnerId($_SESSION["admin"]["id"]);
				$tab->Save();
				print $tab->getId();
				break;			
			case 'update':
				if(isset($_GET['id'])) {
					$tab = Tab::GetTabById($_GET['id']);
				}
				else {
					// TODO: make this work
					$template = new Template($_GET['templateId']);
					$tab = $template->getTab();
				}
				//$tab->setDescription($_GET["description"]);
				$tab->setName($_GET['name']);
				
				$tab->Save($adminUser);
				print $tab->getId();
				break;
			case 'delete':
				$tab = new Tab($_GET["id"]);
				$tab->Delete($adminUser);
				break;
			case 'restore':
				$tab = new Tab($_GET['id']);
				print $tab->Restore($adminUser);
				break;
			default: break;
		}
		break;

	/* ** ** ** Template ** ** ** */
	case 'template':
		templateOperations($operation, $adminUser);
		break;

	/* ** ** ** Asset ** ** ** */
	case 'asset':
		include_once(DIR_FS_MODEL . "Asset.class.php"); 
	
		switch($operation){
			case 'restore':
				$asset = Asset::RetrieveById($_GET['id'], $adminUser);
				print $asset->RestoreForUser(new User($_GET['user_id']), $adminUser);
				break;
		}
		break;

	/* ** ** ** Page ** ** ** */
	case 'page':
		include_once('model/Template.class.php');
		//include_once(DIR_FS_MODEL . "User.class.php");
		//$adminUser = new User();
		//$adminUser->setId($_SESSION["admin"]["id"]);
		
		switch($operation){
			case "insert":
				$page = new Page();
				$page->setTitle($_GET["name"]);
				
				// Get tab id from template
				if(isset($_GET["templateId"])) {
					$template = new Template($_GET["templateId"]);
					$page->setTab($template->getTab());
				}
				// directly provide tab id
				else {
					$page->setTab( new Tab($_GET["parentId"]));
				}
				
				$page->Save($adminUser);
				print $page->getId();
				break;
			case "update":			
				$page = Page::GetPageById($_GET["id"]);
				$page->setTitle($_GET["name"]);
				$page->Save($adminUser);
				print $page->getId();
				break;
			case "delete":
				$page = Page::GetPageById($_GET["id"]);
				$page->Delete($adminUser);
				break;
			default: break;
		}
		break;

	/* ** ** ** Group ** ** ** */
	case 'group':
		include_once(DIR_FS_MODEL . "Group.class.php"); 
		switch($operation){
			case "insert":
				$newGroup = new Group();
				$newGroup->setTitle($_POST["title"]);
				$newGroup->setDescription($_POST["description"]);
				if(isset($_POST["inst"]) && is_numeric($_POST["inst"]) && $adminUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN)) {
					$newGroup->setInstitution(new Institution($_POST["inst"]));
				}
				else {
					$newGroup->setInstitution($adminUser->getInstitution());
				}
				if(isset($_POST["template_id"])) $group->setTemplateId($_POST["template_id"]);
				$newGroup->Save($adminUser);
				print $newGroup->getId();
				break;

			case "update":
				$group = Group::RetrieveGroupById($_REQUEST['id']);
				$group->setTitle($_POST["title"]);
				$group->setDescription($_POST["description"]);
				if(isset($_POST["institution_id"])) $group->setInstitutionId($_POST["institution_id"]);
				if(isset($_POST["template_id"])) $group->setTemplateId($_POST["template_id"]);		
				$group->Save($adminUser);
				echo $group->getId();
				break;
			
			case 'add_users':
				$newUserIds = split(',', $_REQUEST['ids']);
				$group = Group::RetrieveGroupById($_REQUEST['id']);
				foreach($newUserIds as $newUserId) {
					$group->addMember(new User($newUserId));
				}
				$group->Save($adminUser);
				break;
			
			case 'remove_users':
				$userIds = split(',', $_REQUEST['ids']);
				$group = Group::RetrieveGroupById($_REQUEST['id']);
				foreach($userIds as $userId) {
					$group->removeMember(new User($userId));
				}
				$group->Save($adminUser);
				break;

			case "delete":
				$group = new Group($_POST["id"]);
				echo $group->delete();
				break;
				
			case "getTemplatePreviewByTemplateId":
				$sql="SELECT * FROM template WHERE 
						ID='" . $_GET["template_id"] . "'
					ORDER BY title ASC
					";
				$queryTemplate=$db->query($sql);
				if($db->numRows($queryTemplate)>0){
					$template=$db->fetchObject($queryTemplate);
					echo "<ul>";
					echo "<li><b>" . $template->title . "</b></li>";
					$sql="SELECT * FROM tab WHERE template_id=" . $template->ID;
					$queryTab=$db->query($sql);
					if($db->numRows($queryTab)>0){
						echo "<ul>";
						while($tab=$db->fetchObject($queryTab)){
							echo "<li>" . $tab->name . "</li>";
							$sql="SELECT * FROM page WHERE tab_id=" . $tab->ID;
							$queryPage=$db->query($sql);
							if($db->numRows($queryPage)>0){						
								echo "<ul>";
								while($page=$db->fetchObject($queryPage)){
									echo "<li>" . $page->title . "</li>";
								}
								echo "</ul>";
							}
						}
						echo "</lu>";
					}
					
					echo "</ul>";
					
				}
				break;
			default: break;
		}
		break;
	case 'switchpassword':
		switchPasswordOperations($operation, $adminUser, $_POST);
		break;
}
 



function login($userName, $password, $institution)
{
	if( User::Login($userName, $password, $institution) ) {
		$user = User::RetrieveBySessionData($_SESSION);
		if($user->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN)) {
			// User has admin permission
			return '1';
		}
		else {
			// User exists, but doesn't have admin rights
			return '2';
		}

	}
	else {
		return '0';
	}
}


function institutionOperations($operation, $adminUser, $requestData)
{
	include_once(DIR_FS_MODEL . "Institution.class.php");

	switch($operation) {
		case 'insert':
			$newInstitution = new Institution();
			$newInstitution->setName($requestData['name']);
			$newInstitution->setUrl($requestData['url']);
            $newInstitution->setSharing($requestData['share']);
			$newInstitution->CreateFolders();
			$newInstitution->Save($adminUser);
			print $newInstitution->getId();
			break;
		case 'update':
			$institution = Institution::RetrieveById($requestData["id"]);
			$institution->setName($requestData['name']);
            $institution->setSharing($requestData['share']);
			if(!$institution->setUrl($requestData['url'])){
				print("Please try another URL");
			}
			else {
				// Save it
				$institution->Save($adminUser);
				print $institution->getId();
			}
			break;
		case 'delete':
			$institution = Institution::RetrieveById($requestData["id"]);
			$institution->Delete($adminUser);
			print $requestData["id"];
			break;
		case 'setpic':
			$institution = Institution::RetrieveById($requestData["id"]);
			$institution->setAssetId($requestData["asset_id"]);
			$institution->Save($adminUser);
			print 1;
		default:
			break;
	}
}

function switchPasswordOperations($operation, $adminUser, $requestData)
{
	// All switchPassword ops on a user
	$userId = Safe::GetArrayIndexValueWithDefault($requestData, 'user_id', 0);
	if(isValidNumberInRange($userId, 1)) {
		$user = User::RetrieveById($userId, $adminUser);
	}
	else {
		die('Bad user id');
	}

	switch($operation) {
		case 'setphoto':
			$photoId = Safe::GetArrayIndexValueWithDefault($requestData, 'id', 0);

			if(isValidNumberInRange($photoId, 0, 14)) {
				$user->getPermissionManager()->getSymbolLogin()->setPhoto($photoId);
				$user->Save($adminUser);
				print $user->getId();
			}
			else {
				print 'Bad photo id';
			}

			break;
		case 'setshape':
			$shapeId = Safe::GetArrayIndexValueWithDefault($requestData, 'id', 0);

			if(isValidNumberInRange($shapeId, 0, 5)) {
				$user->getPermissionManager()->getSymbolLogin()->setShape($shapeId);
				$user->Save($adminUser);
				print $user->getId();
			}
			else {
				print 'Bad shape id';
			}
			break;
	}
}

function templateOperations($operation, $adminUser)
{
	include_once(DIR_FS_MODEL . "Template.class.php");

	// Get template id - most operations need this
	$templateId = Safe::GetArrayIndexValueWithDefault($_POST, 'id', null);
	if( !is_numeric($templateId) ) $templateId = null;

	switch($operation){
		case 'insert':
			$institutionId = (isset($_POST['inst']) && is_numeric($_POST['inst'])) ?
							new Institution($_POST['inst']) :
							$adminUser->getInstitution();
			$description = (isset($_POST["description"])) ?
							$_POST["description"] :
							'';

			$template = Template::CreateNew($_POST["title"], $description, $institutionId);
			$template->Save($adminUser);
			print $template->getId();
			break;
		case 'update':
			$template = new Template($templateId);
			$template->setTitle($_POST["title"]);
			$template->setDescription($_POST["description"]);
			$template->setLocked(isset($_POST['locked']) && $_POST['locked']=='on');
			$template->Save($adminUser);
			print $template->getId();
			break;
		case 'check_delete':
			$template = new Template($templateId);
			print $template->getViewerNameList();
			break;
		case 'delete':
			$template = new Template($templateId);
			print $template->Delete($adminUser);
			break;
		case 'add_viewers':
			$template = new Template($templateId);
			print $template->AddViewersFromString($_POST['ids']);
			break;
		case 'remove_viewers':
			$template = new Template($templateId);
			$template->RemoveViewersFromString($_POST['ids']);
			break;
		case 'seticon':
			$template = new Template($templateId);
			$template->getTab()->setIconById($_POST['icon_id']);
			$template->Save($adminUser);
			print '0';
			break;
		default:
			print "Template error: Don't understand operation '{$operation}'";
			break;
	}
}

function userOperations($operation, $adminUser)
{
	include_once(DIR_FS_MODEL . "User.class.php");
	switch($operation){
		case 'insert':
			// Set the institution ID, defaults to admin's own institution
			$institutionId = ( isset($_GET['institution_id']) ) ? $_GET['institution_id'] : $adminUser->getInstitution()->getId();

			try {
				$permissionManager = PermissionManager::Create($_GET['username'], $_GET['password'], $_GET['userType'], $adminUser);
			}
			catch(Exception $e) {
				die($e->getMessage());
			}

			$newUser = User::CreateNewUser(
				$_GET['firstName'],
				$_GET['lastName'],
				$_GET['email'],
				$_GET['description'],
                $_GET['share'],
				$permissionManager,
				new Institution($institutionId));

			if($newUser->isUnique()) {
				$newUser->Save($adminUser);
				print $newUser->getId();
			}
			else {
				print "<p><strong>There is already a '{$_GET['username']}' at " . $newUser->getInstitution()->getName() .'.</strong></p>';
				print '<p>Try using just the users initials, their nickname or familiar word such as a hobby or interest.</p>';
			}

			break;

		case 'update':
			$user = User::RetrieveById($_GET["userid"], $adminUser);
			$user->setFirstName($_GET['firstName']);
			$user->setLastName($_GET['lastName']);
			$user->setEmail($_GET['email']);
			$user->setDescription($_GET['description']);
            $user->setShare($_GET['share']);
			//$user->setUsername();
			if(isset($_GET['userType']) && $_GET['userType'] != '0') {
				try {
					$user->getPermissionManager()->setUsertype($_GET['userType'], $adminUser);
				}
				catch (Exception $e) {
					die($e->getMessage());
				}
			}
			$user->getPermissionManager()->setPassword($_GET['password']);
			$user->getPermissionManager()->getSymbolLogin()->setEnabled(isset($_GET['ppEnabled']));
			$user->Save($adminUser);
			print $user->getId();
			break;

		case 'delete':
			$user = User::RetrieveById($_GET['id'], $adminUser);
			$userId = $user->getId();
			$user->Delete($adminUser);
			print $userId;
			break;

		default:
			break;
	}
}

function isValidNumberInRange($num, $startRange, $endRange=null)
{
	if(isset($endRange) && $endRange < $startRange) {
		throw new Exception("StartRange ({$startRange}) must be smaller than endRange({$endRange})");
	}

	$validNumber = is_numeric($num);
	if($validNumber) {
		$bigEnough = $num >= $startRange;
		$smallEnough = !isset($endRange) || $num <= $endRange;
	}

	return $validNumber && $bigEnough && $smallEnough;
}