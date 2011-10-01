<?php
/**
 * Group Class
 *
 * This class is used to hold group data 
 * 
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: Attachment.class.php 816 2009-11-05 13:04:04Z richard $
 * @link       NA
 * @since      NA
 */

include_once('DatabaseObject.class.php');
include_once('iFile.int.php');

class Attachment extends DatabaseObject implements iFile
{	
	/* ** Member variables ** */
	private $m_href;
	private $m_page;
	private $m_folder;
	private $m_system_folder;
	private $m_type = 'file';

	/* ** Accessors ** */

	/**
	 * The class to mark a file list item as
	 */
	public function getIconClass()
	{
		$this->checkFilled();
		
		$assetType = Asset::getAssetType($this->m_href);
		switch($assetType)
		{
			case Asset::IMAGE:
			case Asset::AUDIO:
			case Asset::VIDEO:
				return 'file_' . $assetType;
			default:
				switch($this->getFileExtension())
				{
					case 'docx': case 'doc': case 'pdf': case 'txt': case 'xls': case 'xlsx': case 'ppt': case 'pptx':
						return 'file_document';
					default:
						return 'file_other';
				}
		}
	}

	public function setHref($value)
	{
		$this->m_href = $value;
	}
	public function getHref()
	{
		$this->checkFilled();
		return $this->m_href;
	}

	public function getType()
	{
		return $this->m_type;
	}

	/**
	 * Returns a simple object of this object. Is used for JSON making.
	 */
	protected function getAsObject()
	{
		return (object) array(
			'id' => $this->getId(),
			'href' => $this->m_href
		);
	}

	public function getFileExtension()
	{
		return Uploader::FindFileExtension($this->getHref());
	}

	protected function setFolders($institutionUrl)
	{
		Debugger::debug("Setting folders", 'Attachment::setFolders');
		if(!isset($institutionUrl))
		{
			$institutionUrl = 'shared';
		}
		// Set system folder based on institute
		$this->m_folder = DIR_WS_DATA . $institutionUrl . '-asset/' . $this->m_type . '/';
		$this->m_system_folder = DIR_FS_DATA . $institutionUrl . '-asset/' . $this->m_type . '/';
	}

	public function getFolder()
	{
		return $this->m_folder;
	}

	public function getSystemFolder()
	{
		return $this->m_system_folder;
	}

	/* ** Factoy methods ** */

	public static function CreateNew($href, Page $page, User $user)
	{
        if (!self::checkAllowedType($href)) {
            Throw new Exception("That is not an allowed file type - try compressing it using something like winzip and upload the new file");
        };
		$attachment = new Attachment();
		$attachment->m_href = $href;
		$attachment->m_page = $page;
		$attachment->setFolders($user->getInstitution()->getUrl());

		return $attachment;
	}

    /**
	 * Works out the type of an asset based on file extension.
	 * @param <type> $filename
	 * @return <type>
	 */
	public static function checkAllowedType($fileName)
	{
        //In-folio stores files in the webroot so a whitelist must be used to define which files are safe to upload
        //It is deliberate that a blacklist is not used instead.
		$extension = Uploader::FindFileExtension($fileName);


		if(in_array($extension, explode(',',EXT_WHITELIST))) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Retrieves all the attachments for this page
	 * @return 
	 */
	public static function RetrieveByPage($page)
	{
		$db = Database::getInstance();
		$sql = "SELECT * FROM attachments WHERE enabled=1 AND page_id={$page->getId()}";
		$result = $db->query($sql);
		
		$attachments = array();
		while($row = $db->fetchArray($result)) {
			$row['page'] = $page;
			$attachments[] = self::createFromHashArray($row);
		}
		
		if(isset($attachments)) return $attachments;
	}

	/* ** DB Methods ** */

	private static function createFromHashArray($hashArray, Attachment &$attachment=null)
	{
		if(!isset($attachment)) {
			$attachment = new Attachment($hashArray['id']);
		}
		$attachment->m_href = $hashArray['href'];
		$attachment->m_page = new Page($hashArray['page_id']);

		$attachment->m_createdTime = $hashArray['created_time'];
		$attachment->m_updatedTime = $hashArray['updated_time'];
		$attachment->m_updatedBy = new User($hashArray['updated_by']);
		$attachment->m_createdBy = new User($hashArray['created_by']);

		// Work out the institution URL
		$user = New User($hashArray['created_by']);
		$institution = $user->getInstitution();
		$attachment->setFolders($institution->getUrl());

		$attachment->m_filled = true;
		return $attachment;
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
			'href' => $this->m_href,
			'page_id' => $this->m_page->getId(),
		
			'created_by' => $this->m_createdBy->getId(),
			'updated_by' => $this->m_updatedBy->getId(),
			'created_time' => Date::formatForDatabase($this->m_createdTime),
			'updated_time' => Date::formatForDatabase($this->m_updatedTime)
		);
		$db->perform('attachments', $data);
		$this->m_id = $db->insertId();
	}
	
	protected function dbUpdate()
	{
		$db = Database::getInstance();
	
		$data=array(
			'href' => $this->m_href,
			'page_id' => $this->m_page->getId(),
			'updated_by' => $this->m_updatedBy->getId(),
			'updated_time' => Date::formatForDatabase($this->m_updatedTime)			
		);
		$db->perform("attachments", $data, "update", "id=" . $this->getId());
	}
	
		/**
	 * Gets the data for this attachment using the ID.
	 * Lazy loading. In some cases we only need the attachment ID
	 * @return 
	 * @param $id Object
	 */
	protected function populateFromDB(){
		$db = Database::getInstance();
		$sql = "SELECT * FROM attachments WHERE id={$this->m_id}";
		$result = $db->query($sql);
		
		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			self::createFromHashArray($row, $this);
			$this->m_filled = true;
		}
	}

	/**
	 * Deletes a user's attachment.
	 * This is not a permanent delete.
	 */
	public function Delete($authUser)
	{
		$this->m_updatedBy = $authUser;
		$this->m_updatedTime = Date::now();

		$db = Database::getInstance();
		$data=array(
				'enabled' => 0,
				'updated_by' => $this->m_updatedBy->getId(),
				'updated_time' => Date::formatForDatabase($this->m_updatedTime),
		);
		$db->perform('attachments', $data, Database::UPDATE, "id={$this->getId()}");
	}

	/* ** Display methods ** */

	public function Html(Page $page)
	{
		$this->checkFilled();
		$fileLink = Link::CreateLink($this->m_href, $this->getFolder() . $this->m_href);
		$deleteLink = Link::CreateLink('x', $page->PathWithQueryString(array('attdelete'=>$this->getId()), true, 'attachments'), array('title'=>"Delete {$this->m_href}", 'class'=>'del_attach'));

		return $fileLink->Html() . '&nbsp;&nbsp;&nbsp;&nbsp;' . $deleteLink->Html();
	}
}