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
 * AJAX FUNCTION FOR LOGIN
 *
 *
 * @author     Elvir Leonard
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: ajax.dispatcher.php 814 2009-11-04 22:14:39Z richard $
 */

include_once('../system/initialiseBackOffice.php');
include_once('../system/model/Institution.class.php');
include_once('../system/model/User.class.php');


/** cast variable called operation; this will be used on switch statement **/
$rop = Safe::request('operation');
$ra = Safe::request('a');
$rid = Safe::request('id');
$rids = Safe::request('ids');
$operation = (isset($rop))? $a = strtolower($rop) : '';
$a = (isset($ra)) ? $a = strtolower($ra) : '';

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
		$inpUserName = Safe::get('username');
		$inpPassword = Safe::get('password');
		$inpInstitution = Safe::get('institution',PARAM_ALPHANUMEXT);
		print login($inpUserName, $inpPassword, $inpInstitution);
		break;

	case 'institution':
		institutionOperations($operation, $adminUser);
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
				$template = new Template(Safe::get('templateId'));
				$pages = $template->getPages();
				print Template::CreateJsonString($pages);
				break;
			case 'insert':
				$tab = new Tab(null, Safe::get('name'));
				$tab->setDescription(Safe::get('description'));
				$tab->setTemplateId(Safe::get('templateId'));
				$tab->setOwnerId($_SESSION["admin"]["id"]);
				$tab->Save($adminUser);
				print $tab->getId();
				break;			
			case 'update':
                $gid = Safe::get('id');
				if(isset($gid)) {
					$tab = Tab::GetTabById($gid);
				}
				else {
					// TODO: make this work
					$template = new Template(Safe::get('templateId'));
					$tab = $template->getTab();
				}
				//$tab->setDescription(Safe::get('description'));
				$tab->setName(Safe::get('name'));
				
				$tab->Save($adminUser);
				print $tab->getId();
				break;
			case 'delete':
				$tab = new Tab(Safe::get('id'));
				$tab->Delete($adminUser);
				break;
			case 'restore':
				$tab = new Tab(Safe::get('id'));
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
				$asset = Asset::RetrieveById(Safe::get('id'), $adminUser);
				print $asset->RestoreForUser(new User(Safe::get('user_id')), $adminUser);
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
				$page->setTitle(Safe::get('name'));
				$tid = Safe::get('templateId');
				// Get tab id from template
				if(isset($tid)) {
					$template = new Template($tid);
					$page->setTab($template->getTab());
				}
				// directly provide tab id
				else {
					$page->setTab( new Tab(Safe::get('parentId')));
				}
				
				$page->Save($adminUser);
				print $page->getId();
				break;
			case "update":			
				$page = Page::GetPageById(Safe::get('id'));
				$page->setTitle(Safe::get('name'));
				$page->Save($adminUser);
				print $page->getId();
				break;
			case "delete":
				$page = Page::GetPageById(Safe::get('id'));
				$page->Delete($adminUser);
				break;
			default: break;
		}
		break;

	/* ** ** ** Group ** ** ** */
	case 'group':
		include_once(DIR_FS_MODEL . "Group.class.php");
        $ptid = Safe::post('template_id');
        $piid = Safe::post('institution_id');
        $ptitle = Safe::post('title');
        $pdesc  = Safe::post('description');
        $p_id   = Safe::post('id');
		switch($operation){
			case "insert":
				$newGroup = new Group();
				$newGroup->setTitle($ptitle);
				$newGroup->setDescription($pdesc);
                $pinst = Safe::post('inst', PARAM_INT);
				if(isset($pinst) && $adminUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN)) {
					$newGroup->setInstitution(new Institution($pinst));
				}
				else {
					$newGroup->setInstitution($adminUser->getInstitution());
				}
				if(isset($ptid)) $group->setTemplateId($ptid);
				$newGroup->Save($adminUser);
				print $newGroup->getId();
				break;

			case "update":
				$group = Group::RetrieveGroupById($rid);
				$group->setTitle($ptitle);
				$group->setDescription($pdesc);
				if(isset($piid)) $group->setInstitutionId($piid);
				if(isset($ptid)) $group->setTemplateId($ptid);
				$group->Save($adminUser);
				echo $group->getId();
				break;
			
			case 'add_users':
				$newUserIds = split(',', $rids);
				$group = Group::RetrieveGroupById($rid);
				foreach($newUserIds as $newUserId) {
					$group->addMember(new User($newUserId));
				}
				$group->Save($adminUser);
				break;
			
			case 'remove_users':
				$userIds = split(',', $rids);
				$group = Group::RetrieveGroupById($rid);
				foreach($userIds as $userId) {
					$group->removeMember(new User($userId));
				}
				$group->Save($adminUser);
				break;

			case "delete":
				$group = new Group($p_id);
				echo $group->delete();
				break;
				
			case "getTemplatePreviewByTemplateId":
				$sql="SELECT * FROM template WHERE 
						ID='" . Safe::get('templateId') . "'
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
		switchPasswordOperations($operation, $adminUser);
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


function institutionOperations($operation, $adminUser)
{
	include_once(DIR_FS_MODEL . "Institution.class.php");

	switch($operation) {
		case 'insert':
			$newInstitution = new Institution();
			$newInstitution->setName(Safe::post('name'));
			$newInstitution->setUrl(Safe::post('url'));
            $newInstitution->setSharing(Safe::post('share'));
            $newInstitution->setComment(Safe::post('comment'));
            $newInstitution->setCommentApi(Safe::post('commentapi'));
            $newInstitution->setLimitShare(Safe::post('limitshare'));
			$newInstitution->CreateFolders();
			$newInstitution->Save($adminUser);
			print $newInstitution->getId();
			break;
		case 'update':
			$institution = Institution::RetrieveById(Safe::post('id'));
			$institution->setName(Safe::post('name'));
            $institution->setSharing(Safe::post('share'));
            $institution->setComment(Safe::post('comment'));
            $institution->setCommentApi(Safe::post('commentapi'));
            $institution->setLimitShare(Safe::post('limitshare'));
			if(!$institution->setUrl(Safe::post('url'))){
				print("Please try another URL");
			}
			else {
				// Save it
				$institution->Save($adminUser);
				print $institution->getId();
			}
			break;
		case 'delete':
			$institution = Institution::RetrieveById(Safe::post("id"));
			$institution->Delete($adminUser);
			print Safe::post("id");
			break;
		case 'setpic':
			$institution = Institution::RetrieveById(Safe::post("id"));
			$institution->setAssetId(Safe::post("asset_id"));
			$institution->Save($adminUser);
			print 1;
		default:
			break;
	}
}

function switchPasswordOperations($operation, $adminUser)
{
	// All switchPassword ops on a user
	$userId = Safe::postWithDefault('user_id', 0, PARAM_INT);
	if(isValidNumberInRange($userId, 1)) {
		$user = User::RetrieveById($userId);
	}
	else {
		die('Bad user id');
	}

	switch($operation) {
		case 'setphoto':
			$photoId = Safe::postWithDefault('id', 0, PARAM_INT);

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
			$shapeId = Safe::postWithDefault('id', 0, PARAM_INT);

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
	$templateId = Safe::postWithDefault('id', null, PARAM_INT);
    $pinst = Safe::post('inst');
    $pdesc = Safe::post('desc');
    $ptitle = Safe::post('title');
    $plocked = Safe::post('locked');
	switch($operation){
		case 'insert':
			$institutionId = (isset($pinst)) ?
							new Institution($pinst) :
							$adminUser->getInstitution();
			$description = (isset($pdesc)) ?
							$pdesc :
							'';

			$template = Template::CreateNew($ptitle, $description, $institutionId);
			$template->Save($adminUser);
			print $template->getId();
			break;
		case 'update':
			$template = new Template($templateId);
			$template->setTitle($ptitle);
			$template->setDescription($pdesc);
			$template->setLocked(isset($plocked) && $plocked=='on');
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
			print $template->AddViewersFromString(Safe::post('ids'));
			break;
		case 'remove_viewers':
			$template = new Template($templateId);
			$template->RemoveViewersFromString(Safe::post('ids'));
			break;
		case 'seticon':
			$template = new Template($templateId);
			$template->getTab()->setIconById(Safe::post('icon_id'));
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
            $insid = Safe::get('templateId');
			$institutionId = ( isset($insid) ) ? $insid : $adminUser->getInstitution()->getId();

			try {
				$permissionManager = PermissionManager::Create(Safe::get('username'), Safe::get('password'), Safe::get('userType'), $adminUser);
			}
			catch(Exception $e) {
				die($e->getMessage());
			}

			$newUser = User::CreateNewUser(
				Safe::get('firstName'),
				Safe::get('lastName'),
				Safe::get('email'),
				Safe::get('description'),
				$permissionManager,
				new Institution($institutionId));

			if($newUser->isUnique()) {
				$newUser->Save($adminUser);
				print $newUser->getId();
			}
			else {
				print "<p><strong>There is already a '".Safe::get('username')."' at " . $newUser->getInstitution()->getName() .'.</strong></p>';
				print '<p>Try using just the users initials, their nickname or familiar word such as a hobby or interest.</p>';
			}

			break;

		case 'update':
			$user = User::RetrieveById(Safe::get('userid'));
			$user->setFirstName(Safe::get('firstName'));
			$user->setLastName(Safe::get('lastName'));
			$user->setEmail(Safe::get('email'));
			$user->setDescription(Safe::get('description'));
            $user->setShare(Safe::get('share'));
			//$user->setUsername();
            $ut = Safe::get('userType');
			if(isset($ut) && $ut != '0') {
				try {
					$user->getPermissionManager()->setUsertype($ut, $adminUser);
				}
				catch (Exception $e) {
					die($e->getMessage());
				}
			}
			$user->getPermissionManager()->setPassword(Safe::get('password'));
            $ppen = Safe::get('ppEnabled');
			$user->getPermissionManager()->getSymbolLogin()->setEnabled(isset($ppen));
			$user->Save($adminUser);
			print $user->getId();
			break;

		case 'delete':
			$user = User::RetrieveById(Safe::get('id'));
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
