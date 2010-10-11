<?php

/**
 * The PageBlock Class
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: PageBlock.class.php 813 2009-11-04 15:04:11Z richard $
 * @link       NA
 * @since      NA
*/

include_once('Image.class.php');
include_once('PageBlockLayout.class.php');
include_once('class/TextToSpeech.class.php');

/**
 * A class that stores the content for a Block that is shown on a page.
 */
class PageBlock extends DatabaseObject
{
	private $m_pageId;
	private $m_title;
	private $m_weight;
	private $m_wordBlocks;
	private $m_pictures;
	private $m_blockLayout;
	private $m_user;
	
	private $m_editMode = false;
	
	static $m_blockTemplates;
	static $m_blockTemplatesMenu;

	/**
	 * A weight for a block so heavy that it will always be the lowest block
	 */
	const HEAVIEST_WEIGHT = 9999;
	
	// Position constants
	const TOP = 'top';
	const MIDDLE = 'middle';
	const BOTTOM = 'bottom';
	const ALONE = 'alone';
	
	/* ** Accessors ** */

	/**
	 * Returns a simple object of this object. Is used for XML/JSON making.
	 * Currently cheats to make nested XML easier, but would break JSON.
	 */
	protected function getAsObject()
	{
		$object = (object) array(
			'id' => $this->getId(),
			'name' => $this->getTitle(),
			'weight' => $this->getWeight(),
			'WordBlocks' => '<wordblock>' . implode('</wordblock><wordblock>', $this->getWordBlocks()) . '</wordblock>'
		);

		// Add pictures and other assets
		$pictures = $this->getPictures();
		$pictureXml = '';
		foreach($pictures as $picture) {
			$pictureXml .= $picture->toXml();
		}
		$object->Assets = $pictureXml;

		return $object;
	}

	public function setLayoutTemplateId($templateId)
	{
		// Get Block templates from DB the first time they're needed
		if( !isset(self::$m_blockTemplates) ) {
			self::$m_blockTemplates = PageBlockLayout::GetLayouts();
		}

		// Get Block Layout for this block
		if( isset(self::$m_blockTemplates[$templateId]) ) {
			$this->m_blockLayout = self::$m_blockTemplates[$templateId];
		}
		else {
			// Default to first block template

			// Get first key
			foreach(self::$m_blockTemplates as $key=>$value) {
				$defaultTemplateId = $key;
				break;
			}

			// Set the template to use default key
			if ( isset(self::$m_blockTemplates[$defaultTemplateId]) ) {
				Logger::Write("Required block layout '{$templateId}' didn't exist", Logger::TYPE_WARNING, $block->m_createdBy);
				$this->m_blockLayout = self::$m_blockTemplates[$defaultTemplateId];
			}
			// Catch all. This shouldn't happen
			else {
				Logger::Write('No block templates loaded on block ' . $this->m_id, Logger::TYPE_ERROR, $this->m_updatedBy);
			}
		}
	}

	/**
	 * WordBlocks contains the blocks of words to show in the Block's placeholders
	 * @return String-Array
	 */
	public function getWordBlocks() { return $this->m_wordBlocks; }
	
	public function setWordBlocks($wordBlocks)
	{
		if(is_array($wordBlocks) || $wordBlocks==null) {
			$this->m_wordBlocks = $wordBlocks;
		}
		else {
			throw new Exception("TechDis: setWordBlocks expects an array or null");
		}
	}
	
	/**
	 * Sets the page that this block is part of.
	 * @param Page $containerPage The page that this block belongs to
	 */
	public function setPage(Page $containerPage)
	{
		$this->m_pageId = $containerPage->getId();
	}

	/**
	 * Pictures contains the pictures to show in the Block's placeholders
	 * @return Image-Array
	 */
	public function getPictures() { return $this->m_pictures; }
	
	public function getPictureId($picturePlace)
	{
		$id = ( isset($this->m_pictures[$picturePlace]) ) ? $this->m_pictures[$picturePlace]->getId() : null;
		return $id;
	}
	
	public function setPicture($picturePlace, $pictureId)
	{
		if(!is_array($this->m_pictures)) {
			$this->m_pictures = array();
		}
		$this->m_pictures[$picturePlace] = Image::RetrieveById($pictureId);
	}
	
	public function getWeight() { return $this->m_weight; }
	
	public function setWeight($value) {
		if( is_numeric($value) ) {
			$this->m_weight = $value;
			Debugger::debug("Setting weight", "PageBlock({$this->m_id})::setWeight($value)", Debugger::LEVEL_INFO);
		}
		else {
			throw new Exception("TechDis: weight ({$value}) is not a number");
		}
	}
	
	public function setEditMode($value)
	{
		$this->m_editMode = ($value == true);
	}
	
	public function getTitle()
	{
		return $this->m_title;
	}
	
	public function setTitle($value)
	{
		$this->m_title = $value;
	}
	
	/* ** Methods ** */
	
	/**
	 * Like equals, but just checks they refer to the same DB entity
	 * @return 
	 * @param $p Object
	 */
	public function isSameEntity(PageBlock $p)
	{
		return ( isset($this->m_id) && $p->m_id == $this->m_id  );
	}
	
	/* ** Factory methods ** */

	public static function CreateNew($title, Page $page, User $owner)
	{
		$block = new PageBlock(null);
		$block->setTitle($title);
		$block->setPage($page);
		$block->m_user = $owner;
		return $block;
	}

	public static function RetrieveById($id)
	{
		$db = Database::getInstance();
		$sql = "SELECT * FROM block WHERE id={$id} LIMIT 1";
		Debugger::debug("SQL: $sql", 'PageBlock::RetrieveById_1', Debugger::LEVEL_SQL);
		$result = $db->query($sql);
		
		if ($row = $db->fetchArray($result, MYSQL_ASSOC)) { 
			$block = self::createFromHashArray($row);
			return $block;
		}
	}
	
	/**
	 * Creates an array of PageBlock objects that are contained in a page
	 * @return PageBlock-Array
	 * @param $page Page
	 */
	public static function GetBlocksByPage(Page $page, User $user=null)
	{
		$db = Database::getInstance();
		$sql = "SELECT * FROM block WHERE page_id={$page->getId()}";
		if(isset($user)) $sql .= " AND user_id={$user->getId()}";
		$sql .= " ORDER BY weight";
		Debugger::debug("SQL: $sql", 'PageBlock::GetBlocksByPage_1', Debugger::LEVEL_SQL);
		$result = $db->query($sql);

		$blockArray = array();
		while ($row = $db->fetchArray($result, MYSQL_ASSOC)) { 
			$blockArray[$row['id']] = self::createFromHashArray($row);
		}
		
		return $blockArray;
	}
	
	/**
	 * Gets a menu with the different block layout templates that can be used
	 * @return 
	 */
	public static function GetLayoutsMenu($page)
	{	
		// Create menu the first time
		if( !isset(self::$m_blockTemplatesMenu) ) {
			// Get Block templates from DB the first time they're needed
			if( !isset(self::$m_blockTemplates) ) {
				self::$m_blockTemplates = PageBlockLayout::GetLayouts();
			}
			
			// Create menu if there are any loaded templates
			if (count(self::$m_blockTemplates) > 0) {
				$links = array();
				foreach(self::$m_blockTemplates as $template) {
					$templateId = $template->getId();
					$links[] = Link::CreateImageLink(Image::CreateSystemImage("layout-thumbnails/{$templateId}.gif", "Template {$templateId}: {$template->getDescription()}", 40, 29),
														$page->PathWithQueryString( array('add'=>$templateId) ));
				}
				self::$m_blockTemplatesMenu = new Menu($links, null, 'New block');
				self::$m_blockTemplatesMenu->setClass('inline-list');
			}
		}

		return self::$m_blockTemplatesMenu;
	}

	/* ** Data methods ** */
	
	protected function dbCreate()
	{
		$this->m_createdBy = $this->m_updatedBy;
		$this->m_createdTime = $this->m_updatedTime;

		$data=array(
			'title' => $this->m_title,
			'weight' => $this->m_weight,
			'words0' => $this->m_wordBlocks[0],
			'page_id' => $this->m_pageId,
			'block_layout_id' => $this->m_blockLayout->getId(),
			'user_id' => $this->m_user->getId(),
			'updated_time' => Date::formatForDatabase($this->m_updatedTime),
			'updated_by' => $this->m_updatedBy->getId(),
			'created_time' => Date::formatForDatabase($this->m_createdTime),
			'created_by' => $this->m_createdBy->getId()
		);
        if (!empty($this->m_pictures)) {
            $data['picture0'] = $this->getPictureId(0);
            $data['picture1'] = $this->getPictureId(1);
        }

		$db = Database::getInstance();
		$db->perform('block', $data, Database::INSERT);

		$this->populateInsertedId('block');
	}
	
	protected function dbUpdate()
	{
		Debugger::debug("Updating page block", "PageBlock({$this->m_id})::dbUpdate()_1", Debugger::LEVEL_INFO);
		
		$data=array(
			'title' => $this->m_title,
			'weight' => $this->m_weight,
			'words0' => $this->m_wordBlocks[0],
			'picture0' => $this->getPictureId(0),
			'picture1' => $this->getPictureId(1),
			'updated_time' => Date::formatForDatabase($this->m_updatedTime),
			'updated_by' => $this->m_updatedBy->getId()
		);
		
		$db = Database::getInstance();
		$success = $db->perform("block", $data, Database::UPDATE, "id={$this->m_id} LIMIT 1");
		
		// Debug message for any failures
		if(!$success) {
			Debugger::debug("Update failed", "PageBlock({$this->m_id})::dbUpdate()_2", Debugger::LEVEL_ERROR);
		}
	}

	/**
	 * Deletes this block from the DB.
	 * Warning, this does not keep a copy for undo.
	 */
	public function Delete()
	{
		$sql = "DELETE FROM block WHERE id={$this->m_id}";
		$db = Database::getInstance();
		$db->query($sql);
		$this->m_id = null;
	}

	protected function populateFromDB()
	{
		$db = Database::getInstance();
		$sql = "SELECT * FROM block WHERE id={$this->m_id}";
		$result = $db->query($sql);

		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$this->createFromHashArray($row, $this);
		}
	}

	/**
	 * Creates a block from an associative array, such as from a row in DB results
	 * @return 
	 * @param $blockArray Object
	 */
	private static function createFromHashArray($hashArray, PageBlock &$block=null)
	{
		if(!isset($block)) {
			$block = new PageBlock($hashArray['id']);
		}
		
		$block->m_pageId = $hashArray['page_id'];
		$block->m_title = $hashArray['title'];
		$block->m_weight = $hashArray['weight'];
		$block->m_user = new User($hashArray['user_id']);

		$wordBlocks = self::retrieveWordBlocks($hashArray);
		$block->setWordBlocks($wordBlocks);

		$block->m_pictures = self::retrievePictures($hashArray);

		$block->setLayoutTemplateId($hashArray['block_layout_id']);

		$block->setAuditFieldsFromHashArray($hashArray);

		$block->m_filled = true;

		return $block;
	}

	private static function retrieveWordBlocks($hashArray)
	{
		$words = array();
		$moreWords = true;
		$wordPlace = 0;
		while($moreWords) {
			if( isset( $hashArray["words{$wordPlace}"] ) ) {
				$words[] = $hashArray["words{$wordPlace}"];
			}
			else {
				$moreWords = false;
				break;
			}
			$wordPlace++;
		}
		return $words;
	}

	private static function retrievePictures($hashArray)
	{
		$pictureIds = array();
		$morePictures = true;
		$picturePlace = 0;
		// Build an array of picture ids
		while($morePictures) {
			if( isset( $hashArray["picture{$picturePlace}"] ) ) {
				$pictureId = $hashArray["picture{$picturePlace}"];
				if( is_numeric($pictureId) ) {
					$pictureIds[] = $pictureId;
				}
			}
			else {
				$morePictures = false;
				break;
			}
			$picturePlace++;
		}
		$pictures = null;
		if( count($pictureIds) > 0){
			$pictures = Image::RetrieveByIds($pictureIds, null);
			$orderedPictures = array();
			foreach($pictureIds as $order=>$pictureId) {
				if($pictureId > 0 && isset($pictures[$pictureId])) {
					$orderedPictures[$order] = $pictures[$pictureId];
				}
			}
			return $orderedPictures;
		}
	}

	/* ** Display methods ** */
	
	/**
	 * Creates an HTML representation of this Block
	 * @return String The HTML for this Block
	 */
	public function Html(Page $page, $blockNumber, $blockPosition, $theme)
	{
		if(! $this->m_editMode ) {
			// Block not in edit mode, check mode of page
			switch( $page->getMode() ) {
				case Page::MODE_SHOW:
					$html = $this->m_blockLayout->Html($this, $theme);
					break;
				case Page::MODE_EDIT:
					$html = $this->m_blockLayout->HtmlWithEditOptions($this, $page, $theme, $blockNumber, $blockPosition);
					break;
				default:
					throw new Exception("TechDis: Attempt to draw block using invalid page mode ({$pageMode})");
			}
		}
		else {
			// block in edit mode
			$html = $this->m_blockLayout->HtmlEdit($this, $theme, $page);
		}
		
		//return $theme->Box($html, $topContent, $this->getBlockLayoutClass());
		return $html;
	}

	public function EditMenu(Page $page, $blockNumber, Theme $theme)
	{
		$menu = new Menu( array(
				Link::CreateIconLink('Edit', $page->PathWithQueryString( array('blockedit'=>$this->getId()), true, 'b'.$this->getId()), $theme->Icon('edit2'), array('class'=>'btnEdit', 'title'=>"Edit block {$blockNumber}")),
					Link::CreateIconLink('Delete', $page->PathWithQueryString( array('blockdelete'=>$this->getId()) ), $theme->Icon('delete-block'), array('class'=>'btnDelete', 'title'=>"Delete block {$blockNumber}")),
							));
		$menu->setClass('inline-list');
		return $menu;
	}

	public function HtmlEdit()
	{
		$html = $this->m_blockLayout->HtmlEdit($this);
		return $html;
	}

	/**
	 * Creates a mp3 file of the text in this block
	 */
	public function TextToSpeech()
	{
		// Work out destination file name <id>-<mod-date>
		// This naming format lets us create a unique filename for block speech
		$fileName = '_voicedtext/' . $this->getId() . '-' . $this->getUpdatedTime();

		// Put all the word blocks into one string
		$text = $this->m_title . '. ' . implode('', $this->m_wordBlocks);
		$text = strip_tags($text);
		$speech = new TextToSpeech($text, $fileName, DIR_FS_DATA);

		// Return the web location of the file
		//return DIR_WS_DATA . $fileLastBit;
		return $speech->getFilePath();
	}

	/**
	 * Creates the XMl required by the switch browser
	 */
	public function SwitchXml()
	{
		$text = (isset($this->m_wordBlocks[0])) ?
					$this->m_wordBlocks[0] :
					'';
		// Spec states that wordblocks should be seperate,
		// currently no blocks have more than one block.
		// get.sound.php currently only gets sound for a complete block.

		$xml = "<block id=\"{$this->getId()}\" title=\"{$this->getTitle()}\">" .
			"<blocktext>\n" .
			"<text audiosrc=\"http://{$_SERVER['SERVER_NAME']}:{$_SERVER['SERVER_PORT']}/system/get.sound.php?blockid={$this->getId()}\">{$text}</text>\n" .
			"</blocktext><assets>\n";

		// Add assets to XML
		$assets = $this->getPictures();
		if(isset($assets)) {
			foreach($assets as $asset) {
				$xml .= $asset->SwitchXml();
			}
		}

		$xml .= "</assets></block>\n";
		return $xml;
	}
}