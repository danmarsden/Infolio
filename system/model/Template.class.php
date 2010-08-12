<? 

/**
 * Template Class
 *
 * This class is used to hold Template data
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside
 * @copyright  2009 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: Template.class.php 752 2009-08-09 15:37:34Z richard $
 * @link       NA
 * @since      NA
 */

class Template extends DatabaseObject
{
	private $m_description;
	private $m_institution;
	private $m_locked;
	private $m_pages;
	private $m_tab;
	private $m_title;

	// Accessors

	public function setDescription($value) {
		$this->checkFilled();
		$this->m_description = $value;
	}

	public function getDescription() {
		$this->checkFilled();
		return $this->m_description;
	}

	public function getInstitution() {
		$this->checkFilled();
		return $this->m_institution;
	}

	public function isLocked() {
		$this->checkFilled();
		return $this->m_locked;
	}

	public function setLocked($value) {
		$this->checkFilled();
		$this->m_locked = $value;
	}

	public function getPages()
	{
		if(!isset($this->m_pages)) {
			$this->m_pages = Page::RetrieveByTab($this->getTab());
		}
		
		return $this->m_pages;
	}

	public function getTab()
	{
		$this->checkFilled();
		return $this->m_tab;
	}

	public function setTitle($value) {
		$this->checkFilled();
		$this->m_title = $value;
	}

	public function getTitle() {
		$this->checkFilled();
		return $this->m_title;
	}

	public function getViewerNameList()
	{
		$db = Database::getInstance();
		$sql = "SELECT * FROM user WHERE id IN (SELECT user_id FROM block WHERE page_id IN (SELECT id FROM page WHERE tab_id IN (SELECT id FROM tab WHERE template_id={$this->m_id})))";
		$result = $db->query($sql);

		$nameList = array();
		while($row = $db->fetchArray($result)) {
			$nameList[] = $row['firstName'] . ' ' . $row['lastName'];
		}

		return (count($nameList) > 0) ?
					implode(', ', $nameList) :
					0;
	}

	/**
	 * Takes a list of users and groups and lets them see this template
	 * User ids are preceded by a u
	 * Group ids are preceded by a g
	 * @param String $idsString A comma seperated list of users and groups
	 */
	public function AddViewersFromString($idsString)
	{
		$ids = explode(',', $idsString);

		$sqlInserts = array();
		foreach($ids as $id) {
			$firstLetter = substr($id, 0, 1);
			$realId = ltrim($id, 'ug');
			if($firstLetter == 'u') {
				// User
				$sqlInserts[] = "({$this->m_id}, {$realId}, null)";
			}
			elseif($firstLetter == 'g') {
				// Group
				$sqlInserts[] = "({$this->m_id}, null, {$realId})";
			}
		}

		// Check we have stuff to add
		if(count($sqlInserts) > 0) {
			$db = Database::getInstance();
			$sql = 'INSERT INTO template_viewers (template_id, user_id, group_id) VALUES ' . implode(',', $sqlInserts);
			Debugger::debug($sql, 'Template::AddViewersFromString', Debugger::LEVEL_SQL);
			$db->query($sql);
		}
	}

	/**
	 * Takes a list of users and groups and stops them seeing this template
	 * User ids are preceded by a u
	 * Group ids are preceded by a g
	 * @param String $idsString A comma seperated list of users and groups
	 */
	public function RemoveViewersFromString($idsString)
	{
		$ids = explode(',', $idsString);

		$sqlRemoves = array();
		foreach($ids as $id) {
			$firstLetter = substr($id, 0, 1);
			$realId = ltrim($id, 'ug');
			if($firstLetter == 'u') {
				// User
				$sqlRemoves[] = "user_id={$realId}";
			}
			elseif($firstLetter == 'g') {
				// Group
				$sqlRemoves[] = "group_id={$realId}";
			}
		}

		// Check we have stuff to add
		if(count($sqlRemoves) > 0) {
			$db = Database::getInstance();
			$sql = 'DELETE FROM template_viewers WHERE template_id=' . $this->m_id . ' AND (' . implode(' OR ', $sqlRemoves) . ')';
			Debugger::debug($sql, 'Template::RemoveViewersFromString', Debugger::LEVEL_SQL);
			$db->query($sql);
		}
	}

	private static function createFromHashArray($hashArray, Template &$template=null)
	{
		if(!isset($template)) {
			$template = new Template($hashArray['id']);
		}

		$template->m_title = $hashArray['title'];
		$template->m_description = $hashArray['description'];
		$template->m_institution = new Institution($hashArray['institution_id']);
		$template->m_locked = $hashArray['locked'];
		$template->m_tab = new Tab($hashArray['tab_id']);
		$template->setAuditFieldsFromHashArray($hashArray);
		$template->m_filled = true;
		return $template;
	}

	// Factory methods

	public static function CreateNew($title, $description, Institution $institution)
	{
		$template = new Template();
		$template->m_title = $title;
		$template->m_description = $description;
		$template->m_institution = $institution;
		$template->m_locked = false;

		// Set up template's main tab
		$template->m_tab = Tab::CreateNewTab($title, null);
		$template->m_tab->setTemplate($template);
		return $template;
	}

	/**
	 * Retrieves all templates from this users 
	 */
	public function RetrieveAll(User $authUser, $fromInstitution = null)
	{
		// Get the insitution they have rights to acess
		if($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN)) {
			if(isset($fromInstitution)) {
				$institutionId = $fromInstitution->getId();
			}
			else {
				$institutionId = $authUser->getInstitution()->getId();
			}
		}
		elseif($authUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN)) {
			$institutionId = $authUser->getInstitution()->getId();
		}
		else {
			// No rights
			return false;
		}


		$db = Database::getInstance();
		$sql = "SELECT templates.*, tab.ID AS tab_id, tab.name AS tab_name FROM templates INNER JOIN tab ON templates.id=tab.template_id WHERE templates.enabled=1 AND institution_id={$institutionId} ORDER BY title";
		Debugger::debug($sql, 'Template::RetrieveAll', Debugger::LEVEL_SQL);
		$result = $db->query($sql);
		$templates = array();
		while ($row = $db->fetchArray($result)) {
			$templates[] = self::createFromHashArray($row);
		}
		return $templates;
	}

	protected function dbCreate()
	{
		$this->m_createdBy = $this->m_updatedBy;
		$this->m_createdTime = $this->m_updatedTime;

		$db = Database::getInstance();
		$data=array(
			'title' => $this->m_title,
			'description' => $this->m_description,
			'institution_id' => $this->m_institution->getId(),
			'locked' => $this->m_locked,
			'created_by' => $this->m_createdBy->getId(),
			'updated_by' => $this->m_updatedBy->getId(),
			'created_time' => Date::formatForDatabase($this->m_createdTime),
			'updated_time' => Date::formatForDatabase($this->m_updatedTime)
		);
		
		$db->perform('templates', $data);
		$this->m_id = $db->insertId();

		// Also create the main tab
		$this->m_tab->Save($this->m_updatedBy);
	}

	protected function dbDisable($authUser)
	{
		// Set updated by info
		$this->m_updatedBy = $authUser;
		$this->m_updatedTime = Date::Now();

		// Generic disable data
		$data=array(
			'enabled' => 0,
			'updated_by' => $this->m_updatedBy->getId(),
			'updated_time' => Date::formatForDatabase($this->m_updatedTime)
		);

		$db = Database::getInstance();

		// Disable templates
		$db->perform("templates", $data, Database::UPDATE, "id={$this->getId()}");

		// Disable tab
		$db->perform("tab", $data, Database::UPDATE, "id={$this->getTab()->getId()}");

		// Disable pages
		$db->perform("page", $data, Database::UPDATE, "tab_id={$this->getTab()->getId()}");

		return true;
	}

	protected function dbUpdate()
	{
		$db = Database::getInstance();
		$data=array(
			'title' => $this->getTitle(),
			'institution_id' => $this->m_institution->getId(),
			'description' => $this->getDescription(),
			'locked' => $this->m_locked,
			'updated_by' => $this->m_updatedBy->getId(),
			'updated_time' => Date::formatForDatabase($this->m_updatedTime)
		);
		$db->perform("templates", $data, Database::UPDATE, "id={$this->getId()}");

		// Also create the main tab
		$this->m_tab->Save($this->m_updatedBy);
	}

	public function Delete($authUser)
	{
		// Check if it has owners
		$db = Database::getInstance();
		$sql = "SELECT COUNT(id) as count FROM template_viewers WHERE template_id={$this->getId()}";
		$result = $db->query($sql);

		$numOwners = 0;
		if($row = $db->fetchArray($result)) {
			$numOwners = $row['count'];
		}

		if($numOwners == 0) {
			return $this->dbDisable($authUser);
		}
		else {
			return 'You must remove all viewers first.';
		}
	}

	/**
	 * Returns a simple object of this object.
	 * Used for JSON making in createJsonString and createJsonItems.
	 */
	protected function getAsObject()
	{
		throw new Exception('Template::getAsObject not implemented.');
	}

	protected function populateFromDB()
	{
		
		$db = Database::getInstance();
		$sql = "SELECT templates.*, tab.ID AS tab_id, tab.name AS tab_name FROM templates INNER JOIN tab ON templates.id=tab.template_id WHERE templates.id={$this->m_id}";
		Debugger::debug($sql, 'Template::populateFromDB', Debugger::LEVEL_SQL);

		$result = $db->query($sql);

		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$this->createFromHashArray($row, $this);
		}
	}

}