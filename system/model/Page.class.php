<?php

/**
 * The Page Class
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: Page.class.php 847 2010-01-07 08:46:16Z richard $
 * @link       NA
 * @since      NA
*/

include_once('class/si/SimplePage.class.php');
include_once('model/Attachment.class.php');
//include_once('model/PageBlock.class.php');
include_once('PageBlock.class.php');

/**
 * Class to hold a student user's page
 */
class Page extends SimplePage
{
	private $m_id;
	private $m_blocks;
	private $m_enabled;
	private $m_tab;
	private $m_user;
	private $m_createdTime;
	private $m_updatedTime;
	private $m_updatedBy;
	private $m_createdBy;

	// From other tables
	private $m_attachments;

	private $m_edittingBlock = false;
	private $m_lightestBlockWeight;
	private $m_viewer;
	
	// Constants
	const PAGE_LINK = 'page-';
	
	/**
	 * A very simple constuctor for Page
	 * Use factory methods for more flexibity ie.
	 * $aPage = Page::GetPageById($id);
	 * @return 
	 * @param $title String[optional]
	 */
	public function __construct($title = 'Untitled page')
	{
		$this->m_enabled = true;
		parent::__construct($title);
	}
	
	/* ** Accessors ** */

	/**
	 * Returns a simple object of this object. Is used for JSON making.
	 */
	public function getAsObject()
	{
		return (object) array(
			'id'		=>	$this->getId(),
			'title'		=>	$this->getTitle()
		);
	}

	public function getAttachments()
	{
		if( !isset($this->m_attachments)) {
			$this->m_attachments = Attachment::RetrieveByPage($this);
		}
		return $this->m_attachments;
	}

	public function getCreatedTime(){ return $this->m_createdTime; }
	public function setCreatedTime($value){$this->m_createdTime = $value;}

	public function setEnabled($value) {
		$this->m_enabled = $value;
	}

	/**
	 * The user this page is linked to.
	 * Will be null for template pages
	 */
	public function setUser(User $value)
	{
		$this->m_user = $value;
	}

	/**
	 * Set the person currently looking at this page
	 * Different users who can see the same page will see different blocks.
	 */
	public function setViewer(User $value)
	{
		$this->m_viewer = $value;
	}

	public function getUpdatedTime(){ return $this->m_updatedTime;}
	public function setUpdatedTime($value){ $this->m_updatedTime = $value;}
	
	public function getUpdatedBy(){ return $this->m_updatedBy; }
	public function setUpdatedBy($value){ $this->m_updatedBy = $value; }
	
	public function getCreatedBy(){ return $this->m_createdBy;}
	public function setCreatedBy($value){ $this->m_createdBy = $value;}
	
	public function setTab($value){
		$this->m_tab = $value;
	}
	public function getTab(){
		return $this->m_tab;
	}
	
	/**
	 * Gets the section this page belongs to
	 * @return 
	 */
	public function getSectionName()
	{
		return 'section-' . Safe::UrlQueryVarOutput($this->getTab()->getName());
	}
	
	public function AddBlock(PageBlock $block)
	{
		// checkblocks have been fetched
		if( !isset($this->m_blocks) ) {
			$this->getBlocks();
		}
		
		// Add to start of array
		$blockId = $block->getId();
		$this->m_blocks = array($blockId=>$block) + $this->m_blocks;
	}
	
	public function getBlock($blockId)
	{
		$blocks = $this->getBlocks();
		if( isset($blocks[$blockId]) ) {
			return $blocks[$blockId];
		}
		else {
			return false;
		}
	}
	
	/**
	 * Gets the blocks for this page
	 * @return PageBlock-Array
	 */
	private function getBlocks()
	{
		if( !isset($this->m_blocks) ) {
			// Get the blocks for this page
			$this->m_blocks = PageBlock::GetBlocksByPage($this, $this->m_viewer);
			
			// Get the lightest block weight
			$this->m_lightestBlockWeight = PageBlock::HEAVIEST_WEIGHT;
			foreach($this->m_blocks as $block) {
				if($block->getWeight() < $this->m_lightestBlockWeight) {
					$this->m_lightestBlockWeight = $block->getWeight();
				}
			}
		}
		return $this->m_blocks;
	}
	
	/**
	 * Gets the number of content blocks on this page
	 * @return 
	 */
	private function getNumBlocks()
	{
		$blocks = $this->getBlocks();
		return count($blocks);
	}
	
	public function getId() {
		return $this->m_id;
	}
	
	
	public function setEditBlock($blockId)
	{
		$blocks = $this->getBlocks();
		$this->m_edittingBlock = true;
		
		if( isset($blocks[$blockId]) ) {
			$blocks[$blockId]->setEditMode(true);
		}
		else {
			throw new Exception('No block with that ID');
		}
	}
	
	/* ** Factory methods ** */
	
	public static function GetPageById($id, $authUser=null)
	{
		$db = Database::getInstance();
		//$sql = "SELECT * FROM page INNER JOIN tab ON page.tab_id = tab.id WHERE page.id={$id} LIMIT 1";
		$sql = "SELECT * FROM page WHERE page.id={$id} LIMIT 1";
		$result = $db->query($sql);

		if ($row = $db->fetchArray($result, MYSQL_ASSOC)) {
			return self::createFromHashArray($row);
		}
	}
	
	public static function RetrieveByTab(Tab $tab, User $viewer = null, $sortSql = '')
	{
		$db = Database::getInstance();
		$viewerSql = (isset($viewer)) ? " OR user_id={$viewer->getId()}" : '';
		$sql = "SELECT * from page WHERE (ISNULL(user_id){$viewerSql}) AND enabled=1 AND tab_id={$tab->getId()}" . $sortSql;
		Debugger::debug("SQL: $sql", 'Page::RetrieveByTab_1', Debugger::LEVEL_SQL);

		$result = $db->query($sql);
		$pages = array();
		while($row = $db->fetchArray($result, MYSQL_ASSOC)) {
			 $pages[$row['id']] = self::createFromHashArray($row);
		}
		return $pages;
	}
	
	private static function createFromHashArray($hashArray)
	{
		$page = new Page($hashArray['title']);
	 	$page->m_id = $hashArray['id'];
		//$page->m_title = $hashArray['title'];
		$page->m_enabled = $hashArray['enabled'] == 1;
		$page->m_tab = new Tab($hashArray['tab_id']);
		if(isset($hashArray['user_id'])) {
			$page->m_user = new User($hashArray['user_id']);
		}
		$page->m_createdTime = Date::varFromDatabase($hashArray['created_time']);
		$page->m_updatedTime = Date::varFromDatabase($hashArray['updated_time']);
		$page->m_updatedBy = new User($hashArray['updated_by']);
		$page->m_createdBy = new User($hashArray['created_by']);

		return $page;
	}
	
	/**
	 * Creates a new block that can be added to this page
	 * (Will appear as highest block on page)
	 * @return 
	 */
	public function CreateNewBlock($templateId, User $owner)
	{
		$newBlock = PageBlock::CreateNew('New block', $this, $owner);
		$newBlock->setWeight($this->getNewBlockWeight());
		$newBlock->setLayoutTemplateId($templateId);
		return $newBlock;
	}

	private function isNotTemplateControlled()
	{
		return $this->m_tab->getTemplate() == null || isset($this->m_user);
	}

	public function isSameEntity(Page $p)
	{
		return ( isset($this->m_id) && $p->m_id == $this->m_id  );
	}
	
	/**
	 * Produces a path to this page with an extra or replaced querystring parameter 
	 * @return String A path
	 * @param $key String
	 * @param $value String
	 * @param $otherKeyValues Array The existing key value pairs to add
	 * @param $htmLinkSafe Bool If true encodes appasands, no good for redirects
	 */
	public function PathWithQueryString($keyValues=null, $htmlLinkSafe=true, $hashAnchorName = null)
	{
		// Check we have an array to work with
		if(!is_array($keyValues)){
			$keyValues = array();
		}

		if(isset($this->m_mode) && !isset($keyValues['mode'])) {
			$keyValues['mode'] = $this->m_mode;
		}

		$path = parent::PathWithQueryString($keyValues, $htmlLinkSafe, $hashAnchorName);
		// Check page id is set correct (only affects new pages)
		$path = preg_replace('/page-\d+/', 'page-' . $this->m_id, $path);
		return $path;
	}

	/**
	 * Creates the XMl required by the switch browser
	 */
	public function SwitchSummaryXml()
	{
		$xml = "<page id=\"{$this->getId()}\" title=\"{$this->getTitle()}\">" .
			"<imageurl>http://{$_SERVER['SERVER_NAME']}:{$_SERVER['SERVER_PORT']}{$this->PreviewImage()}</imageurl>" .
			"</page>\n";
		return $xml;
	}

	/**
	 * Creates the XMl required by the switch browser
	 */
	public function SwitchXml()
	{
		$xml = "<page id=\"{$this->getId()}\">" .
			"<title>{$this->getTitle()}</title>\n" .
			"<pageblocks>\n";

		// Add blocks to XML
		$blocks = $this->getBlocks();
		foreach($blocks as $block) {
			$xml .= $block->SwitchXml();
		}

		$xml .= "</pageblocks></page>\n";
		return $xml;
	}

	/**
	 * Deletes a block from this page and removes it from the DB
	 * @param int $blockId The blocks DB id
	 */
	public function DeleteBlock($blockId)
	{
		$blocks = $this->getBlocks();
		
		if(isset($blocks[$blockId])) {
			return $blocks[$blockId]->Delete();
		}
		else {
			return false;
		}

	}

	/**
	 * Moves a block closer to the top of a page
	 * and stores the change
	 * @return 
	 * @param $blockId Object
	 */
	public function MoveBlockUp($blockId)
	{
		$this->swapBlocks($blockId, -1, 0);
	}
	
	/**
	 * Moves a block closer to the bottom of the page
	 * and stores the change
	 * @return 
	 * @param $blockId Object
	 */
	public function MoveBlockDown($blockId)
	{
		$this->swapBlocks($blockId, 0, 1);
	}
	
	/**
	 * Swaps two blocks round so they can be moved up or down
	 * @return 
	 * @param $blockId Int The id of the block to swap
	 * @param $swapOffset1 Int -1 = swap with block before, 0 = swap with block after
	 * @param $swapOffset2 Int  0 = swap with block before, 1 = swap with block after
	 */
	private function swapBlocks($blockId, $swapOffset1, $swapOffset2)
	{
		$blocks = $this->getBlocks();
		
		// Need to count array and keep array of keys as this is an associative array (no numeric index)
		$arrayCount = 0;
		$keys = array_keys($blocks);
		foreach($blocks as $key=>$block) {
			if($block->getId() == $blockId) { 
				$offset1 = $arrayCount + $swapOffset1;
				$offset2 = $arrayCount + $swapOffset2;
				if( $offset1 >= 0 && $offset2 >= 0) {
					array_splice  ( $blocks, 
					$offset1, 
					2, 
					array($blocks[$keys[$offset2]], 
					$blocks[$keys[$offset1]]) );
					$this->m_blocks = $blocks;
				}
				break;
			}
			$arrayCount++;
		}
		
		$this->renumberBlockWeights();
	}
	
	/**
	 * Sort out the weights so the blocks are stored in the order they are in the array
	 * @return 
	 */
	private function renumberBlockWeights()
	{
		$blocks = $this->getBlocks();
		Debugger::debug('Renumbering ' . count($blocks) . ' blocks', 'Page::renumberBlockWeights_1', Debugger::LEVEL_INFO);
		
		$blockWeight = 2;
		foreach($blocks as $block) {
			$block->setWeight($blockWeight);
			$block->Save($this->m_createdBy);
			$blockWeight += 2;
		}
	}
	
	/**
	 * Gets the weight for a new block that will float above all the others
	 * @return Int
	 */
	function getNewBlockWeight()
	{
		if(!isset($this->m_blocks)) {
			$this->getBlocks();
		}
		return $this->m_lightestBlockWeight - 2;
	}

	/**
	 * Creates an XML string of this page and its blocks
	 */
	public function toXml()
	{
		$xml = "<Page><title>{$this->getTitle()}</title>\n<PageBlocks>";
		$blocks = $this->getBlocks();
		foreach($blocks as $block){
			$xml .= $block->toXml();
		}
		$xml .= '</PageBlocks></Page>';

		return $xml;
	}

	/* ** Display methods ** */
	
	public function PagingLink($pageNumber)
	{
		//$link = new Link($pageNumber, Page::PAGE_LINK . $this->m_id, 'section-' . Safe::UrlQueryVarOutput($this->m_tab->getName()));
		$link = Link::CreateLink($pageNumber, Page::PAGE_LINK . $this->m_id);
		$link->setTitle( $this->getTitle() );
		return $link;
	}

	public function HtmlAttachments()
	{
		$attachments = $this->getAttachments();

		$html = '<ul>';
		if(count($attachments) == 0) {
			// No attachments
			$html .= '<li>No attachments</li>';
		}
		else {
			foreach($attachments as $attachment) {
				$html .= "<li class=\"{$attachment->getIconClass()}\">{$attachment->Html($this)}</li>";
			}
		}
		$html .= '</ul>';
		return $html;
	}

	/**
	 * Creates HTML for all blocks on this page
	 * @return String HTML
	 */
	public function HtmlBlocks(Theme $theme)
	{
		$html = '';
		$blocks = $this->getBlocks();

		if( isset($blocks) ) {
			$numBlocks = count($blocks);
			$blockCount = 0;

			foreach($blocks as $block) {

				// Work out blocks position
				$blockCount++;
				if ($numBlocks == 1) {
					$blockPosition = PageBlock::ALONE;
				}
				elseif($blockCount == 1) {
					$blockPosition = PageBlock::TOP;
				}
				elseif ($blockCount == $numBlocks) {
					$blockPosition = PageBlock::BOTTOM;
				}

				else {
					$blockPosition = PageBlock::MIDDLE;
				}

				// The blocks content wrapped with a themed box
				//$html .= $theme->Box( $block->Html($this, $blockPosition, $theme), $block->getTitle(), $block->getBlockLayoutClass() );
				$html .= $block->Html($this, $blockCount, $blockPosition, $theme);
			}
		}
		return $html;
	}

	public function HtmlLink()
	{
		$html = '<a href="' . Page::PAGE_LINK . "{$this->m_id}\">{$this->m_title}</a>";
		return $html;
	}

	/**
	 * Creates any messages that should be shown with the page
	 * @return 
	 */
	public function HtmlMessage(Theme $theme)
	{
		$html = null;
		// Blank page message
		if($this->getNumBlocks() < 1) {
			$pageMenu = new Menu( array(
				Link::CreateIconLink('Edit', $this->PathWithQueryString(array('mode'=>Page::MODE_EDIT)), $theme->Icon('edit'))
			));
		
		if($this->isNotTemplateControlled()) {
			$pageMenu->addLink( Link::CreateIconLink('Delete', $this->PathWithQueryString( array('a'=>EventDispatcher::ACTION_DELETE) ), $theme->Icon('delete')) );
		}
		

		$pageMenu->setClass('inline-list');

			$html = '<p>You can:</p>' . $pageMenu->Html();
 //add new blocks to it, or change its name.</p><p><strong>Press edit page first</strong></p>';

		}

		if(isset($this->m_warningMessages)) {
			$html .= $this->m_warningMessages;
		}

		return $html;
	}

	/**
	 * Creates the title for any messages that should be shown with the page
	 * @return
	 */
	public function HtmlMessageTitle()
	{
		// Blank page message
		if($this->getNumBlocks() < 1) {
			return '<h2>Blank page</h2>';
		}
	}
	
	/**
	 * Creates a short HTML summary of this page that can be shown on a page with other summaries
	 * @return String HTML
	 */
	public function HtmlSummary()
	{
		
		$imageHref = $this->PreviewImage();
		if(isset($imageHref)) {
			$previewImageHtml = "<img src=\"{$imageHref}\" alt=\"{$this->m_title}\" width=\"200\" height=\"200\" /><br />";
		}
		else {
			$previewImageHtml = '';
		}
		$html = '<a href="' . Page::PAGE_LINK . "{$this->m_id}\">{$previewImageHtml}</a>";
		return $html;
	}
	
	public function HtmlTitle($headerLevel = 1)
	{
		// Show form when editting whole page
		// Don't show when editting a block, so there are less edit boxes to confuse the user
		// Don't show for template pages
		
		if ($this->m_mode == Page::MODE_EDIT && !$this->m_edittingBlock && $this->isNotTemplateControlled() ) {
			$html = '<form action="' . $this->PathWithQueryString( array('mode'=>Page::MODE_SHOW) ) . '" method="post" class="page_update">' .
				'<input type="hidden" name="a" value="' . EventDispatcher::ACTION_SAVE . '" />' .
				"<input type=\"text\" name=\"title\" value=\"{$this->m_title}\" />&nbsp;" .
				"<input type=\"submit\" value=\"Save\" />" .
				"</form>";
		}
		// Otherwise just show the title wrapped in an h tag
		else {
			$html = parent::HtmlTitle($headerLevel);
		}
		
		return $html;
	}
	
	/**
	 * Get's the path to this preview image and creates one if it doesn't exist.
	 * @return The path to this page's preview image
	 */
	public function PreviewImage()
	{
		// IMage magick command
		/*
		 * http://www.imagemagick.org/Usage/layers/#convert
		 */

		// Get first four images from page
		$images = Image::RetrieveByPage($this, $this->m_viewer, 4);

		// Only generate a preview image if the page has images in it.
		if (count($images) > 0)
		{
			// Generate a unique name for the image based on the ids of the pictures that make it
			$imageIds = array_keys($images);

			$imageName = implode('-', $imageIds);
			$imageSystemFolder = $images[$imageIds[0]]->getSystemFolder();
			$dstPath = $imageSystemFolder . 'page_thumbnail/' . $imageName . '.png';


			// Create the image if it doesn't exist
			if( !file_exists($dstPath) ) {
				$cmd = "convert -size 200x200 xc:transparent ";

				$dst = array(
					array('x'=>0, 'y'=>0),
					array('x'=>100, 'y'=>0),
					array('x'=>0, 'y'=>100),
					array('x'=>100, 'y'=>100)
				);

				for($i=0; $i < count($images); $i++) {

					// Get width and height
					$width = $images[$imageIds[$i]]->getWidth();
					$height = $images[$imageIds[$i]]->getHeight();

					$src = $images[$imageIds[$i]]->getFilePath();
					

					$cmd .= '-page 100x100 ' . IM_OPEN_BRACKET . " \"{$src}\" -gravity center -resize 100x100" . IM_NO_BOUNDS . " -extent 100x100 -repage +{$dst[$i]['x']}+{$dst[$i]['y']} " . IM_CLOSE_BRACKET . ' ';
				}
				
				$cmd .= ' -flatten ' . $dstPath;

				exec($cmd, $output, $returnVal);
			}

			$imageWebFolder = $images[$imageIds[0]]->getFolder();
			$href = $imageWebFolder . 'page_thumbnail/' . $imageName . '.png';
			return $href;
		}
	}
	
	/* ** Database methods ** */
	
	/**
	 * Stores this page in the DB.
	 * Either updates an existing page, or creates a new page
	 * @return 
	 */
	public function Save($authUser = null)
	{
		// Set update time/user
		$this->m_updatedTime = Date::now();
		$this->setUpdatedBy($authUser->getId());
		
		// Create or update
		if( isset($this->m_id) ) {
			$this->dbUpdate($authUser);
		}
		else {
			$this->dbCreate($authUser);
		}
	}
	
	private function dbUpdate($updatingUser)
	{
		$db = Database::getInstance();
		$data=array(
				'title' => $this->getTitle(),
				'enabled' => ($this->m_enabled) ? 1 : 0,
				'updated_by' => $this->getUpdatedBy(),
				'updated_time' => Date::formatForDatabase($this->m_updatedTime),
				'tab_id' => $this->m_tab->getId()
		);

		// Add user info if set
		if(isset($this->m_user)) $data['user_id'] = $this->m_user->getId();

		$db->perform('page', $data, Database::UPDATE, "id={$this->getId()}");
	}
	
	private function dbCreate($creatingUser)
	{
		$this->m_createdBy = $creatingUser->getId();
		$this->m_createdTime = Date::now();
		
		// Create new page record
		$db = Database::getInstance();
		$data=array(
				'title' => $this->getTitle(),
				'enabled' => ($this->m_enabled) ? 1 : 0,
				'updated_by' => $this->getUpdatedBy(),
				'created_by' => $this->getCreatedBy(),
				'updated_time' => Date::formatForDatabase($this->m_updatedTime),
				'created_time' => Date::formatForDatabase($this->m_createdTime),
				'tab_id' => $this->m_tab->getId()
		);

		// Add user info if set
		if(isset($this->m_user)) $data['user_id'] = $this->m_user->getId();
		
		$db->perform('page', $data);
		// Get db id of new page
		$newId = $db->insertId();

		if( is_numeric($newId) ){
			$this->m_id = $newId;
		}
		
		// Log new page creation
		Logger::Write('New page', Logger::TYPE_INFO, $creatingUser);
	}
	
	public function Delete(User $authUser, $complete = false)
	{
		if(!$complete) {
			$this->m_enabled = false;
			$this->Save($authUser);
		}
		else {
			// Delete it completely from DB
			$sql = "DELETE FROM page WHERE id={$this->m_id}";
			$db = DATABASE::getInstance();
			$db->query($sql);
		}
	}
}