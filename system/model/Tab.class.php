<?php

/**
 * The Tab Class
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Elvir Leonard
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: Tab.class.php 839 2009-12-29 17:17:30Z richard $
 * @link       NA
 * @since      NA
*/

include_once('model/Template.class.php');
include_once('function/core.php');

/**
 *  Class to hold Item for template
 */
class Tab extends DatabaseObject
{
	private $m_name;
	private $m_slug;
	private $m_description;
	private $m_enabled;
	private $m_link;
	private $m_icon;
	private $m_index;
	private $m_pages;
	private $m_template;
	private $m_user;
	private $m_owner;
	private $m_viewer;
	private $m_weight;
    private $m_share;
	
	private $m_pageSortSql;
	
	// Constants
	const ABOUT_ME_TAB_ID = 1;
	const TAB_LINK = 'tab-';
	const HOME_PAGE_NAME = 'About-me';
	const COLLECTION_PAGE_NAME = 'collection';
	const MANAGETABS_PAGE_NAME = 'managetabs';

	const MAX_TITLE_LENGTH = 20;

	/* ** Factory methods ** */

	public static function CreateNewTab($name, $user)
    {
		$tab = new Tab(null);
		$tab->m_user = $user;
		$tab->setNameAndSlug($name);
		$tab->m_enabled = true;
		
		return $tab;
	}

	/**
	 * Gets a tab from the DB and creates a class instance of it
	 * @return 
	 * @param $itemId Tab A new tab
	 */
	public static function GetTabById($itemId)
	{
		$sql = "SELECT * FROM tab WHERE ID=" . $itemId;
		return Tab::createTab($sql);
	}
	
	/**
	 * Get the tab that the page belongs to
	 * @return 
	 * @param $page Object
	 */
	public static function GetPagesTab(Page $page)
	{
		$sql = "SELECT * FROM tab WHERE id IN (SELECT tab_id FROM page WHERE id={$page->getId()})";
		
		$tab = Tab::createTab($sql);
		return $tab;
	}
	
	public static function getTabsByTemplateId($templateId, $mode = ""){
		$db = Database::getInstance();
		if($mode=="backoffice"){
			$sqlTabs = 'SELECT * from tab WHERE template_id=' . $templateId . ' AND (user_id=0 OR user_id IS null) ORDER BY weight';
		}else{
			$sqlTabs = 'SELECT * from tab WHERE template_id=' . $templateId . ' ORDER BY weight';
		}
		$query = $db->query($sqlTabs);
		$tabArray = array();
		while($row = $db->fetchArray($query)) {
			$tab = self::createFromHashArray($row);
			$tab->getPages();
			array_push($tabArray, $tab);
		}
		return $tabArray;
	}
	
	public static function RetrieveTabsByUser(User $user, $enabled=true, $incltemplate=true)
    {
        $enabledstr = '';
        if ($enabled) {
            $enabledstr = ' AND enabled=1';
        }
        $templatestr = ' OR template_id=0';
        if ($incltemplate) {
			$templatestr = " OR (template_id = 0 OR template_id IN (SELECT template_id FROM template_viewers WHERE user_id={$user->getId()} OR group_id IN (SELECT group_id FROM group_members WHERE user_id={$user->getId()})))";
        }
		$db = Database::getInstance();
        $sql = "SELECT * from tab WHERE user_id={$user->getId()}" .
                $enabledstr .
                $templatestr .
                ' ORDER BY weight';
		Debugger::debug($sql, 'Tab::GetTabsByUser_1', Debugger::LEVEL_SQL);

		$result = $db->query($sql);
		
		$tabArray = array();
		
		$tabIndex = 0;
		while($row = $db->fetchArray($result, MYSQL_ASSOC)) {
			$tab = self::createFromHashArray($row);
			$tab->m_index = $tabIndex;
			$tab->m_weight = $row['weight'];

			// About me tab is a special case - depends on this having ID set in DB
			if($tab->getId() == self::ABOUT_ME_TAB_ID) {
				$tab->setIcon( $user->getProfilePicture() );
			}

			$tab->getIcon()->setTitle('Tab ' . ($tabIndex + 1));
			$tab->getLink()->addHtmlProperty('title', "View tab {$tab->getName()}");

			$tabArray[$row['slug']] = $tab;
			$tabIndex++;
        }
		Debugger::debug('Tab count: ' . count($tabArray), 'Tab::GetTabsByUser_2', Debugger::LEVEL_SQL);
		return $tabArray;
	}
	
	/**
	 * Creates a new Tab and populates it from a db item
	 * @return Tab
	 * @param $dbItem Object
	 */
	private static function createTab($sql)
	{
		$db = Database::getInstance();
		$result = $db->query($sql);
		
		if( $row = $db->fetchArray($result) ) {
			return self::createFromHashArray($row);
		}
	}
	
	private static function createFromHashArray($hashArray, Tab &$tab=null)
	{
		if(!isset($tab)) {
			$tab = new Tab($hashArray['ID']);
			//print 'creating new tab ' . $hashArray['ID'];
		}

		// Get icon image if there is one
		if( isset($hashArray['asset_id']) ) {
			$tab->m_icon = Image::RetrieveById($hashArray['asset_id'], $hashArray['owner']);
		}
		else {
			// Set placeholder tab image
			$tab->m_icon = Image::GetPlaceHolder();
		}
		
		$tab->m_name = $hashArray['name'];
		$tab->m_slug = $hashArray['slug'];
		$tab->m_enabled = $hashArray['enabled'];
		$tab->setLinkFromName();
		if( isset($hashArray['template_id']) ) {
			$tab->m_template = new Template($hashArray['template_id']);
		}
		$tab->m_description = $hashArray['description'];
		$tab->m_user = new User($hashArray['user_id']);
		$tab->m_owner = new User($hashArray['owner']);
		$tab->m_weight = $hashArray['weight'];
        $tab->m_share = $hashArray['share'];
		
		$tab->m_filled = true;
		return $tab;
	}	
	
	/* ** Accessors ** */

	/**
	 * Returns a simple object of this object. Is used for JSON making.
	 */
	protected function getAsObject()
	{
		return (object) array(
			'id' => $this->getId(),
			'name' => $this->getName()
		);
	}

	public function getIndex() { return $this->m_index; }
	
	public function setName($name)
	{
		$this->checkFilled();
		$this->setNameAndSlug($name);
		$this->setLinkFromName();
	}
	public function getName()
	{
		$this->checkFilled();
		return $this->m_name;
	}

	public function setShare($value)
	{
		$this->checkfilled();
		$this->m_share = $value;
	}
	public function getShare()
	{
		$this->checkFilled();
		return $this->m_share;
	}

	public function getenabled()
	{
		$this->checkfilled();
		return $this->m_enabled;
    }

	public function setEnabled($enabled)
	{
		$this->checkfilled();
		$this->m_enabled = $enabled;
    }

	public function setDescription($text)
	{
		$this->checkFilled();
		$this->m_description=$text;
	}
	public function getDescription()
	{
		$this->checkFilled();
		return $this->m_description;
	}

	public function getIcon()
	{
		$this->checkFilled();
		return $this->m_icon;
	}

	public function setIcon(Image $icon)
	{
		$this->m_icon = $icon;

		// Update link image as well
		if( isset($this->m_link) ) {
			$this->m_link->setImage($icon);
		}
	}

	public function setIconById($iconId)
	{
		$this->checkFilled();
		$this->setIcon( new Image($iconId) );
	}
	
	public function getLink()
	{
		$this->checkFilled();
		return $this->m_link;
	}

	/**
	 * Sets the link for this tab.
	 * Links are always based on the name of the tab.
	 */
	private function setLinkFromName()
	{
		$this->m_link = Link::CreateSectionIconLink($this->m_name, Tab::TAB_LINK . $this->m_slug, "section-{$this->m_slug}", $this->m_icon, Image::SIZE_TAB_ICON);
	}
	
	public function getPages()
	{
		if( !isset($this->m_pages) && $this->getId() != null ) {
			$this->m_pages = Page::RetrieveByTab($this, $this->m_viewer, $this->m_pageSortSql);
		}
		return $this->m_pages;
	}
	
	public function getNumPages()
	{
		$pages = $this->getPages();
		return count($pages);
	}
	
	public function setPageSortMethod($value)
	{
		$this->m_pageSortSql = ' ORDER BY ';
		switch($value) {
			case 'old':
				$this->m_pageSortSql .= 'created_time ASC';
				break;
			case 'a-z':
				$this->m_pageSortSql .= 'title ASC';
				break;
			case 'new':
				// Falls through to default
			default:
				$this->m_pageSortSql .= 'created_time DESC';
				break;
		}
		Debugger::debug("Sort sql:  {$this->m_pageSortSql}", 'Tab::setPageSortMethod({$value})', Debugger::LEVEL_INFO);
	}
	
	/* // Not required for one level tab structure
	public function setParentId($id){ $this->parentId=$id; }
	public function getParentId(){ return $this->parentId; } */	
	
	/**
	 * Elvir, please explain what this is.
	 * @return 
	 * @param $id Object
	 */
	public function setTemplate($value){ $this->m_template = $value; }
	public function getTemplate()
	{
		$this->checkFilled();
		return $this->m_template;
	}

	public function setViewer($value)
	{
		$this->m_viewer = $value;
	}

	protected function dbCreate()
	{
		$this->m_createdBy = $this->m_updatedBy;
		$this->m_createdTime = $this->m_updatedTime;

		// Set the owner as the creator, unless already set
		if(!isset($this->m_owner)) {
			$this->m_owner = $this->m_createdBy;
		}

		$data = array(
			'name' => $this->m_name,
			'slug' => $this->m_slug,
			'description' => $this->m_description,
			'enabled' => $this->m_enabled,
			'owner' => $this->m_owner->getId(),
			'user_id' => (isset($this->m_user)) ? $this->m_user->getId() : null,
			'template_id' => isset($this->m_template)? $this->m_template->getId() : 'null' ,
			'updated_time' => Date::formatForDatabase($this->m_updatedTime),
			'updated_by' => $this->m_updatedBy->getId(),
			'created_time' => Date::formatForDatabase($this->m_createdTime),
			'created_by' => $this->m_createdBy->getId(),
			'weight' => $this->m_weight,
            'share' => $this->m_share
		);
		$db = Database::getInstance();
		$db->perform('tab', $data, Database::INSERT);

		// Get new id
		$this->m_id= $db->insertId();
		
		// Log new tab creation
		Logger::Write("New tab", Logger::TYPE_INFO, $this->m_createdBy);
	}
	
	protected function dbUpdate()
    {
		Debugger::debug($this->m_name, 'dbUpdate', Debugger::LEVEL_INFO);
		$data=array(
			'name' => $this->m_name,
			'slug' => $this->m_slug,
			'description' => $this->m_description,
			'enabled' => $this->m_enabled,
			'owner' => $this->m_owner->getId(),
			'user_id' => $this->m_user->getId(),
			'weight' => $this->m_weight,
			
			'template_id' => isset($this->m_template)? $this->m_template->getId() : 'null',
            'updated_time' => Date::formatForDatabase($this->m_updatedTime),
			'updated_by' => $this->m_updatedBy,
            'share' => $this->m_share
		);

		// Add tab icon if there is one
		if( isset($this->m_icon) && $this->m_icon->getId() > 0 ) {
			$data['asset_id'] = $this->m_icon->getId();
		}

		$db = Database::getInstance();
		$db->perform("tab", $data, "update", "ID=" . $this->m_id);
	}
	
	protected function populateFromDB()
	{
		$db = Database::getInstance();
		$sql = "SELECT * FROM tab WHERE ID={$this->m_id}";
		$result = $db->query($sql);
		
		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$this->createFromHashArray($row, $this);
		}
	}
	
	public function Delete($authUser)
	{
		$db = Database::getInstance();

        $this->m_enabled = false;
        $this->m_weight = 0;
		$this->Save($authUser);

        Tab::readjustWeights($this->m_owner->getId()); // update all the other Tab weights so they order better

		Logger::Write("Deleted tab '{$this->getName()}' (id:{$this->getId()})", Logger::TYPE_DELETE, $authUser);
	}

	public function Restore($authUser)
    {

		$this->checkFilled();
		// Check not enabled
		if (!$this->m_enabled) {
            // restore with a new weight
            if(isset($this->m_owner)) {
                $max = Tab::getMaxWeight((int)$this->m_owner->getId());
                $this->m_weight = ++$max;
            }
            $this->m_enabled = true;
			$this->Save($authUser);

			Logger::Write("Tab '{$this->getName()}' (id:{$this->getId()}) restored", Logger::TYPE_INFO, $authUser);

			return $this->getId();
		}
		else {
			return 'Tab already enabled';
		}
	}

	/** Useful methods **/

	/**
	 * Sets the name and a unique slug
	 * @param <type> $name
	 */
	private function setNameAndSlug($name)
	{
		// Add hyphens and remove unsafe chars to create a URL safe slug
		$slug = Safe::UrlQueryVarOutput($name);
		

		if(isset($this->m_user)) {
			// Find existing tabs for current user with similar name
			$sql = "SELECT slug FROM tab WHERE user_id={$this->m_user->getId()} AND slug LIKE '{$slug}%'";
			$db = Database::getInstance();
			$result = $db->query($sql);

			// Put the possible classes into an array
			$possibleClashes = array();
			while( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
				$possibleClashes[] = $row['slug'];
			}

			// Check for clashes
			$clashExtension = 0;
			$newSlug = $slug;
			do {
				$clash = false;
				foreach($possibleClashes as $possClash) {
					// If there is a clash rename the file
					if($possClash == $newSlug) {
						$newSlug = $slug . '__' . ++$clashExtension;
						$clash = true;
						break;
					}
				}
			// If renamed check again with new name
			} while ($clash);
		}
		else {
			// Template tab
			$newSlug = 'shared-' . $slug;
		}

		$this->m_name = $name;
		$this->m_slug = $newSlug;
    }


	/**
     * sets up the page for managing tabs
     *
	 * @param User $user
	 * @param Theme $theme
     * @param SimplePage $page
     * @return String html
	 */
    public static function manageTabsContent(User $user, Theme $theme, SimplePage $page) 
    {

        $tabs = Tab::retrieveTabsByUser($user, true, false);
        $index = 1; // to display the up/down arrows
        $html = '<ol id="manage-tab-weights">';
        if ($tabs) {
            foreach ($tabs as $tab) {
                if ($tab->getId() != self::ABOUT_ME_TAB_ID) {
                    // both up and down arrows
                    if ($index != 1 && $index != (count($tabs)-1)) {
                        $tabMenu = new Menu (array(
                            Link::CreateIconLink('Up', $page->PathWithQueryString(array('mode'=>EventDispatcher::ACTION_MOVE_UP,'t'=>$tab->getId() )), $theme->Icon('up-arrow'), array('alt' => 'Move tab up')),
                            Link::CreateIconLink('Down', $page->PathWithQueryString(array('mode'=>EventDispatcher::ACTION_MOVE_DOWN,'t'=>$tab->getId())), $theme->Icon('down-arrow'), array('alt' => 'Move tab down')),
                        ));
                        $tabMenu->setClass('inline-list');
                        $html .= '<li class="manage-tab">' . $tab->m_name . $tabMenu->Html() . '</li>';
                    // down arrow only
                    } else if ($index == 1) {
                        $tabMenu = new Menu (array(
                            Link::CreateIconLink('Down', $page->PathWithQueryString(array('mode'=>EventDispatcher::ACTION_MOVE_DOWN,'t'=>$tab->getId())), $theme->Icon('down-arrow'), array('alt' => 'Move tab down')),
                        ));
                        $tabMenu->setClass('inline-list');
                        $html .= '<li class="manage-tab">' . $tab->m_name . $tabMenu->Html() . '</li>';
                    // up arrow only
                    } else if ($index == count($tabs)-1) {
                        $tabMenu = new Menu (array(
                            Link::CreateIconLink('Up', $page->PathWithQueryString(array('mode'=>EventDispatcher::ACTION_MOVE_UP,'t'=>$tab->getId())), $theme->Icon('up-arrow'), array('alt' =>'Move tab up')),
                        ));
                        $tabMenu->setClass('inline-list');
                        $html .= '<li class="manage-tab">' . $tab->m_name . $tabMenu->Html() . '</li>';
                    }
                    $index++;
                }
            }
        }
        $html .= '</ol>';

        return $html;
    }

    /**
     * Update existing weights from all 0 to something
     * useful
     *
     *  NB: this function is to ensure existing tabs are inline with the new tab ordering weights
     *
	 */
    public static function updateWeights($max=0, &$weights) 
    {
        $user = User::RetrieveBySessionData($_SESSION);
        $newWeight = 0;
        foreach ($weights as $weight => $key) {
            if ($key != self::ABOUT_ME_TAB_ID) { // double-checking:  don't update ABOUT_ME tab
                $tab = Tab::GetTabById($key);
                $tab->m_weight = $newWeight;
                $weights[$key] = $newWeight;
                $tab->save($user); 
                $newWeight++;
            }
        }
        $max = $newWeight - 1;
        
        return $max;
    }

    public static function getWeights($user)
    {
    
		$db = Database::getInstance();
        $sql = "SELECT id from tab WHERE user_id={$user->getId()}" .
                " AND enabled = 1" .
                " AND id != " . self::ABOUT_ME_TAB_ID .
                " ORDER BY weight";

        $result = $db->query($sql);

        $weights = array();
        while( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
            $weights[] = (int)$row['id'];
        }

        return $weights;
    }

    public static function readjustWeights($userid)
    {
        $user = new User((int)$userid);
        $weights = Tab::getWeights($user);

        $newWeight = 0;
        foreach ($weights as $weight => $id) {
            if ($id != self::ABOUT_ME_TAB_ID) { // double-checking:  don't update ABOUT_ME tab
                $tab = Tab::GetTabById($id);
                $tab->m_weight = $newWeight;
                $tab->save($user); 
                $newWeight++;
            }
        }
    }

    public static function getMaxWeight($userid)
    {

        $db = Database::getInstance();
        // retrieve the current max weight
        $maxsql = "SELECT MAX(weight) AS max FROM tab WHERE user_id = {$userid}";
        $result = $db->query($maxsql);
        while( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
            $max = (int)$row['max'];
        }
    
        return $max;
    }

	/**
     * Set the new weight of the tab
     *
     * @param string direction (left/right)
	 */
    public function setWeight($direction=null) 
    {
        $db         = Database::getInstance();
        $weights    = Tab::getWeights($this->m_user);
        $tabs       = Tab::RetrieveTabsByUser($this->m_user, true, false);
        $max        = Tab::getMaxWeight((int)$this->m_user->getId());
        $authuser = User::RetrieveBySessionData($_SESSION);

        // if the max is 0 and we have more than one tab
        // update them incase they are all 0
        if (count($weights) > 1 && $max == 0) {
            $max = Tab::updateWeights($max, $weights);
        }

        // if there is no direction just set the initial weight
        // this is a new Tab
        if (!$direction) {
            $this->m_weight = $max + 1;
            return;
        }

        // current weight
        $oldweight = (int)$this->m_weight;
        $id = (int)$this->getId();

        if ($direction == 'move-up' && $oldweight > 0) {
            $neworder = array_merge(array_slice($weights, 0, $oldweight - 1),
                                    array($id, $weights[$oldweight-1]),
                                    array_slice($weights, $oldweight+1));
        }
        else if ($direction == 'move-down' && ($oldweight + 1 < count($weights))) {
            $neworder = array_merge(array_slice($weights, 0, $oldweight),
                                    array($weights[$oldweight+1], $id),
                                    array_slice($weights, $oldweight+2));
        }

        if (isset($neworder)) {
            foreach ($neworder as $k => $v) {
                $tab = Tab::GetTabById($v);
                $tab->m_weight = $k;
                $tab->Save($authuser);
            }
        }
    }

    // function for setting Template weights to -1 when created
    public function setTemplateWeight() {
        $authuser = User::RetrieveBySessionData($_SESSION);
        $this->m_weight = -1;
        $tab->Save($authuser);
    }

	/* ** Display opperations ** */
	
	/**
	 * Create the HTML for a full page listing
	 * @return 
	 */
	public function HtmlPageListing(User $viewer)
	{
		$html = '';
		$pages = $this->getPages();

		foreach($pages as $page) {
			$page->setViewer($viewer);

			$html .=  '<div class="box left small"><div class="box-head"><h2>' . $page->HtmlLink() . '</h2></div>' .
			'<div class="box-content">' . $page->HtmlSummary() . '</div>' .
			'</div>';
		}

		return $html;
	}
	
	/**
	 * Creates any messages that should be shown with the tab
	 * @return 
	 */
	public function HtmlMessage(SimplePage $page, Theme $theme)
	{
		$html = null;
		
		// Bad tab URL
		if($this->getId() == null) {
			$html = '<p>There is no tab here. You may have changed its name.</p>';
		}
		// Blank tab message
		elseif($this->getNumPages() < 1) {
			// Create tab menu
			// Set up menus
			$tabMenu = new Menu( array(
								Link::CreateIconLink('Add', "page-0?tab={$this->getId()}", $theme->Icon('add-page2'), array('alt' => 'Add'))
								) );
			$tabMenu->setClass('inline-list');
			
			// Templated tabs can not be editted or deleted
			if(!isset($this->m_template)) {
				$tabMenu->addLink( Link::CreateIconLink('Edit', $page->PathWithQueryString(array('mode'=>Page::MODE_EDIT)), $theme->Icon('edit'), array('alt' => 'Edit')) );
				$tabMenu->addLink( Link::CreateIconLink('Delete', $page->PathWithQueryString( array('a'=>EventDispatcher::ACTION_DELETE) ), $theme->Icon('delete'), array('alt' => 'Delete')) );
			}

			$html = '<div class="blank"><p>You can:</p>' . $tabMenu->Html() . '</div>';
		}
		
		return $html;
	}

	/**
	 * Creates the title for any messages that should be shown with the tab
	 * @return
	 */
	public function HtmlMessageTitle()
	{
		if($this->getId() == null) {
			return '<h2>Tab not found</h2>';
		}
		// Blank tab message
		elseif($this->getNumPages() < 1) {
			return '<h2>Blank tab</h2>';
		}
	}
	
	public function HtmlTitle($page, $headerLevel = 1)
	{
		switch($page->getMode()) {
			case SimplePage::MODE_EDIT:
				// Link for editting tab icon
				$this->m_icon->addClass('edit');
				$this->m_icon->setTitle('Choose a new tab icon');
				$iconLink = Link::CreateImageLink($this->m_icon, $page->PathWithQueryString( array('a'=>TabEventDispatcher::ACTION_EDIT_ICON) ), Image::SIZE_TAB_ICON, array('alt'=>'Choose tab icon'));

				// HTML for edit form
				$html = "<form action=\"{$page->PathWithQueryString( array('mode'=>Page::MODE_SHOW) )}\" method=\"post\" class=\"page_update\">" .
					'<input type="hidden" name="a" value="' . EventDispatcher::ACTION_SAVE . '" />';
				$html .= $iconLink->Html();
				$html .= "<input type=\"hidden\" name=\"tab_id\" value=\"{$this->m_id}\" />&nbsp;" .
					'<label for="tTab">Tab name:</label> <input id="tTab" type="text" name="title" size="' . self::MAX_TITLE_LENGTH .'" maxlength="' . self::MAX_TITLE_LENGTH .'" value="' .$this->m_name .'" />&nbsp;' .
					"<input type=\"submit\" value=\"Save\" />" .
					"</form>";
				break;
			default:
				$html = "<h{$headerLevel}>{$this->m_name}</h{$headerLevel}>";
		}
		return $html;
	}
	
	/**
	 * Create a menu of the pages in this tab for paging
	 * @return 
	 */
	public function PagePagingMenu(Page $currentPage)
	{
		$pages = $this->getPages();
		$linkArray = array();

		$pageCount = 1;
		$totalPages = count($pages);
		$currentPageNum = 0;
		foreach($pages as $page) {
			$link = $page->PagingLink($pageCount);
			
			// Find active page link
			if( $page->isSameEntity($currentPage) ) {
				$currentPageNum = $pageCount;
				$link->setActive(true);
			}
			
			// First
			if($pageCount == 1) {
				$firstLink = $page->PagingLink($pageCount);
				$firstLink->setText("&laquo;");
				$firstLink->setTitle("Previous page");
				$linkArray[] = $firstLink;
			}
			
			// Add link to array
			$linkArray[] = $link;

			// Last
			if($pageCount >= $totalPages) {
				$lastLink = $page->PagingLink($pageCount);
				$lastLink->setText("&raquo;");
				$lastLink->setTitle("Next page");
				$linkArray[] = $lastLink;
			}

			$pageCount++;
		}
		return new Menu($linkArray, 'menu_tabs', "Pages: ");
	}
	
	/**
	 * Creates a sort menu for this tab if it contains enough pages to sort
	 * @return 
	 * @param $page SimplePage
	 * @param $theme Theme
	 */
	public function SortMenuIfSortable(SimplePage $page, Theme $theme)
	{
		if($this->getNumPages() > 1) {
			$sortMenu = new Menu( array(	Link::CreateIconLink('Newest first', $page->PathWithQueryString(array('sort'=>'new')), $theme->Icon('sort-newest'), array('title'=>'Sort pages newest first', 'alt' => 'Sort pages newest first')),
									Link::CreateIconLink('Oldest first', $page->PathWithQueryString(array('sort'=>'old')), $theme->Icon('sort-oldest'), array('title'=>'Sort pages oldest first', 'alt' => 'Sort pages oldest first')),
									Link::CreateIconLink('A-Z', $page->PathWithQueryString(array('sort'=>'a-z')), $theme->Icon('sort-az'), array('title'=>'Sort pages A to Z', 'alt' => 'Sort pages A to Z'))),
									'menu_tabs', 'Sort');
			$sortMenu->setClass('inline-list');
			return $sortMenu;
		}
	}

	/**
	 * Creates the XMl required by the switch browser
	 */
	public function SwitchXml()
	{
		$imageUrl = (isset($this->m_icon)) ?
						$this->m_icon->getFullHref(Image::SIZE_SMALL_BOX, true) :
						'';
		
		$xml = "<tab id=\"{$this->getId()}\" title=\"{$this->getName()}\">" .
			"<imageurl>{$imageUrl}</imageurl>" .
			"</tab>\n";
		return $xml;
	}
}
