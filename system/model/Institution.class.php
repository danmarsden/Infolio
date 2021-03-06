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
 * The Institution Class
 *
 * @author     Elvir Leonard
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: Institution.class.php 821 2009-11-10 21:28:57Z richard $
 */

include_once('DatabaseObject.class.php');

class Institution extends DatabaseObject
{
	private $m_asset;
	private $m_assetId;
	private $m_name;
	public $m_url; //used as public to allow setting during import correctly.
	private $m_urlOldValue;
        private $m_share;
        private $m_comment;
        private $m_commentApi;
        private $m_limitshare;
	
	/* ** Accessors ** */

	/**
	 * Returns a simple object of this object. Is used for JSON making.
	 */
	protected function getAsObject()
	{
		return (object) array(
			'id'		=>	$this->getId(),
			'name'		=>	$this->getName()
		);
	}

	public function getAsset()
	{
		if(!isset($this->m_asset)) {
			$this->m_asset = Asset::RetrieveById($this->m_assetId);
		}

		// Replace with placeholder if still not set
		if(!isset($this->m_asset)) {
			$this->m_asset = Image::GetPlaceHolder();
		}

		return $this->m_asset;
	}

	public function setAssetId($value)
	{
		$this->m_assetId = $value;
		$this->m_asset = null;
	}

	public function getFullPath($instPath = null)
	{
		$this->checkFilled();
		if($instPath == null)$instPath = $this->m_url;
		return DIR_FS_DATA . $instPath . '-asset';
	}

	public function allowSharing()
	{
		$this->checkFilled();
		return $this->m_share;
	}
	public function limitShare()
	{
		$this->checkFilled();
		return $this->m_limitshare;
	}
        public function setLimitShare($value) {
                $this->m_limitshare = $value;
        }
	public function setSharing($value){
        $this->m_share = $value;
    }
	public function getComment()
	{
		$this->checkFilled();
		return $this->m_comment;
	}
	public function setComment($value){
        $this->m_comment = $value;
    }
    public function getCommentApi()
    {
        $this->checkFilled();
        return $this->m_commentApi;
    }
    public function setCommentApi($value){
        $this->m_commentApi = $value;
    }

	public function getName()
	{
		$this->checkFilled();
		return $this->m_name;
	}
	public function setName($value){$this->m_name = $value;}

	/**
	 * The number of assets owned by this institution.
	 * @return int Number of assets
	 */
	public function getNumAssets()
	{
		$db = Database::getInstance();
		$sql = "SELECT id FROM assets WHERE created_by IN (SELECT id FROM user WHERE institution_id={$this->getId()})";
		return $db->numRows($sql);
	}

	/**
	 * The number of assets owned by this institution.
	 * @return int Number of assets
	 */
	public function getNumGroups()
	{
		$db = Database::getInstance();
		$sql = "SELECT id FROM groups WHERE created_by IN (SELECT id FROM user WHERE institution_id={$this->getId()})";
		return $db->numRows($sql);
	}

	/**
	 * The number of student users who are a member of this institution.
	 * @return int Number of users
	 */
	public function getNumStudents()
	{
		$db = Database::getInstance();
		$sql = "SELECT id FROM user WHERE userType='student' AND institution_id={$this->getId()}";
		return $db->numRows($sql);
	}

	/**
	 * The number of users (including teachers and admins) who are a member of this institution.
	 * @return int Number of users
	 */
	public function getNumUsers()
	{
		$db = Database::getInstance();
		$sql = "SELECT id FROM user WHERE institution_id={$this->getId()}";
		return $db->numRows($sql);
	}

	public function getUrl()
	{
		$this->checkFilled();
		return $this->m_url;
	}

	/**
	 * Changes the URL if not already used
	 */
	public function setUrl($value)
	{
		$this->checkFilled();

		if($this->m_url == $value)
		{
			// URL not changed, do nothing
			return true;
		}
		elseif(file_exists($this->getFullPath($value)))
		{
			return false;
		}
		else
		{
			$this->m_urlOldValue = $this->m_url;
			$this->m_url = $value;
			return true;
		}
	}

	/**
	 * Creates the asset folders for this Institution
	 */
	public function CreateFolders()
	{
		//copyr(DIR_FS_DATA . 'blank_template-asset', $this->getFullPath());
        $filepatharray = array('/audio',
                               '/file',
                               '/image/page_thumbnail',
                               '/image/photo_login',
                               '/image/size_box',
                               '/image/size_small_box',
                               '/image/size_tabicon',
                               '/image/size_thumbnail',
                               '/upload/size_thumbnail',
                               '/video/flv',
                               '/video/size_thumbnail',
                               '/shared-asset',
                               '/test-asset');
        $old = umask(0);
        foreach ($filepatharray as $dir) {
            if (!is_dir($this->getFullPath().$dir)) {
                mkdir($this->getFullPath().$dir, 00777, true);
            }
        }
        umask($old);
	}

	/**
	 * Moves the assets folders for this institution
	 * Use if the URL is changed.
	 */
	private function moveDataFolder()
	{
		rename($this->getFullPath($this->m_urlOldValue) , $this->getFullPath());
	}
	
	public function HtmlLinkBox()
	{
		$loginUrl = '/' . $this->getUrl() . '/login.php';
		$instAsset = $this->getAsset();
		$instAsset->setTitle($this->getName());
		$html = '<div class="box left small"><div class="box-head"><h2>' .
				'<a href="' . $loginUrl . '">' . $this->m_name . '</a></h2></div>' .
				'<div class="box-content"><a href="' . $loginUrl . '">' . $instAsset->Html(Image::SIZE_SMALL_BOX) . '<br/></a></div>' .
				'</div>';
		return $html;
	}

	/* ** Factory methods ** */
	
	public static function RetrieveById($id)
	{
		$db = Database::GetInstance();
		$sql = "SELECT * FROM institution WHERE id={$id}";
		$result = $db->query($sql);

		if($row = $db->fetchArray($result)) {
			$institution = self::createFromHashArray($row);
			return $institution;
		}
	}
	
	public static function RetrieveAll()
	{
		$db = Database::GetInstance();
		$sql = "SELECT * FROM institution ORDER BY name ASC";
		$result = $db->query($sql);
		
		$institutions = array();
		while($row = $db->fetchArray($result)) {
			$institutions[$row['id']] = self::createFromHashArray($row);
		}
		
		return $institutions;
	}

	/**
	 * Retrieves an institution from the Database based on it's name
	 * @param <type> $name
	 * @return <type>
	 */
	public static function RetrieveByName($name)
	{
		$db = Database::GetInstance();
		$sql = "SELECT * FROM institution WHERE url='{$name}'";
		$result = $db->query($sql);

		if($row = $db->fetchArray($result)) {
			$institution = self::createFromHashArray($row);
			return $institution;
		}
	}
	
	/* ** DB Methods ** */
	
	/**
	 * 
	 * @return 
	 */
	protected function dbCreate()
	{
		$this->m_createdBy = $this->m_updatedBy;
		$this->m_createdTime = $this->m_updatedTime;
		
		$data = array(
			'asset_id' => $this->m_assetId,
			'name' => $this->m_name,
			'url' => $this->m_url,
                        'share' => $this->m_share,
                        'comment' => $this->m_comment,
                        'commentapi' => $this->m_commentApi,
                        'limitshare' => $this->m_limitshare,
			'created_by' => $this->m_createdBy->getId(),
			'updated_by' => $this->m_updatedBy->getId(),
			'created_time' => Date::formatForDatabase($this->m_createdTime),
			'updated_time' => Date::formatForDatabase($this->m_updatedTime)
		);

		// Write to DB
		$db = Database::getInstance();
		$db->perform('institution', $data);

		// Get institution's new DB ID
		$sql = "SELECT id from institution WHERE name='{$this->m_name}' ORDER BY id DESC LIMIT 1";
		$result = $db->query($sql);
		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$this->m_id = $row['id'];
		}
	}
	
	/**
	 * Database operation
	 * @return 
	 */
	public function Delete(){
		$sql = "DELETE FROM institution WHERE id={$this->m_id}";
		$db = Database::getInstance();
		$db->query($sql);
		$this->m_id = null;
	}
	
	/**
	 * Gets the data for this institution using the ID.
	 * Lazy loading. In most casse we only need the institution ID
	 * @return 
	 * @param $id Object
	 */
	protected function populateFromDB(){
		$db = Database::getInstance();
		$sql = "SELECT * FROM institution WHERE id={$this->m_id}";
		$result = $db->query($sql);
		
		if( $row = $db->fetchArray($result, MYSQL_ASSOC) ) {
			$this->createFromHashArray($row, $this);
			$this->m_filled = true;
		}
	}
	
	protected function dbUpdate()
	{

                //die($this->m_limitshare);
		// Rename folder if it has been renamed
		if(isset($this->m_urlOldValue)) {
			$this->moveDataFolder();
		}

		$data = array(
			'asset_id' => $this->m_assetId,
			'name' => $this->m_name,
			'url' => $this->m_url,
                        'share' => $this->m_share,
                        'comment' => $this->m_comment,
                        'commentapi' => $this->m_commentApi,
                        'limitshare' => $this->m_limitshare
		);

                syslog(LOG_DEBUG, 'blah');

		$db = Database::getInstance();
		$db->perform('institution', $data, Database::UPDATE, "id={$this->m_id}");
	}
	
	private static function createFromHashArray($hashArray,  Institution &$institution=null)
	{
		if(!isset($institution)) {
			$institution = new Institution($hashArray['id']);
		}

		$institution->m_assetId = $hashArray['asset_id'];
		$institution->m_name = $hashArray['name'];
		$institution->m_url = $hashArray['url'];
		$institution->m_filled = true;
                if (isset($hashArray['share'])) { //tidy up - don't set if field doesn't exist yet
                    $institution->m_share = $hashArray['share'];
                }
                if (isset($hashArray['comment'])) {
                    $institution->m_comment = $hashArray['comment'];
                }
                if (isset($hashArray['commentapi'])) {
                    $institution->m_commentApi = $hashArray['commentapi'];
                }
                if (isset($hashArray['limitshare'])) {
                    $institution->m_limitshare = $hashArray['limitshare'];
                }
		return $institution;
	}
}
