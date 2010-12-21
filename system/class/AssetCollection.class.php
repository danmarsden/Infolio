<?php

/**
 * The AssetCollection Class
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: AssetCollection.class.php 831 2009-12-21 16:41:00Z richard $
 * @link       NA
 * @since      NA
*/

include_once('model/Image.class.php');
include_once('model/Video.class.php');
include_once('model/Audio.class.php');
include_once('class/Events/CollectionEventDispatcher.class.php');
include_once('class/si/Link.class.php');
include_once('class/si/Menu.class.php');

/**
 * Stores a collection of assets that a user/group has access to.
 * These can be filtered in various ways.
 */
class AssetCollection
{
	// An asset belongs to a group or a user.
	private $m_user = null;
	private $m_group = null;

	private $m_assets;
	private $m_assetTypeFilter;

	private $m_mode = self::MODE_SHOW;

	// Used for iterating through the list
	private $m_selectedAsset = null;
	
	private static $ms_filterSql;
	
	// Filter constants
	const FILTER_ALL = 'f_all';
	const FILTER_TODAY = 'f_2day';
	const FILTER_DAYS = 'f_days';
	const FILTER_IMAGES = 'f_images';
	const FILTER_WEEK = 'f_week';
	const FILTER_MONTH = 'f_month';
	const FILTER_FAVOURITES = 'f_faves';
    const FILTER_INUSE = 'f_inuse';

	// Mode constants
	const MODE_EDIT = 0;
	const MODE_SHOW = 1;
	
	private function __construct()
	{
		$this->m_assets = array();
		
		// Set up filters
		// ToDo: get correct SQL
		if(!isset($this->ms_filterSql)) {
			self::$ms_filterSql = array (	self::FILTER_ALL => '',
											self::FILTER_TODAY => " collection.created_time > '" . date(Database::DATETIME_FORMAT, mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"))) . "'",
											self::FILTER_DAYS => " collection.created_time > '" . date(Database::DATETIME_FORMAT, mktime(0, 0, 0, date("m"), date("d") - 3, date("Y"))) . "'",
											self::FILTER_WEEK => " collection.created_time > '" . date(Database::DATETIME_FORMAT, mktime(0, 0, 0, date("m"), date("d") - 7, date("Y"))) . "'",
											self::FILTER_MONTH => " collection.created_time > '" . date(Database::DATETIME_FORMAT, mktime(0, 0, 0, date("m") - 1, date("d"), date("Y"))) . "'",
											self::FILTER_FAVOURITES => " favourite=1",
											self::FILTER_IMAGES => " type='" . Asset::IMAGE . "'"
										);
		}
	}

	/* ** Factory Methods ** */

	/**
	 * Creates a collection for a user's assets
	 * @param <type> $user
	 */
	public static function CreateUserAssetCollection($user)
	{
		$collection = new AssetCollection();
		$collection->m_user = $user;
		return $collection;
	}

	/**
	 * Creates a collection for a group's assets
	 * @param <type> $user 
	 */
	public static function CreateGroupAssetCollection($group)
	{
		$collection = new AssetCollection();
		$collection->m_group = $group;
		return $collection;
	}

	public function setAssetType($value)
	{
		echo('Setting asset typef ilter');
		$this->m_assetTypeFilter = $value;
	}

	public function getAssets($filterType = self::FILTER_ALL, $tabids=array())
	{
		$filterKey = (isset($this->assetTypeFilter)) ?
						$filterType . '+' . $this->assetTypeFilter :
						$filterType;
		
		// Asset collection for this filter is set
		if( isset($this->m_assets[$filterKey]) ){
			// Return existing asset collection
			return $this->m_assets[$filterKey];
		}
		// Asset collection for this filter not set, and filter exists
		else
		{
			if( isset(self::$ms_filterSql[$filterType]) ){
				$filterSql = self::$ms_filterSql[$filterType];
				
			}
			elseif( substr($filterType, 0, 4) == 'tag=' ) {
				$tagName = Safe::StringForDatabase(substr($filterType, 4));
				$filterSql = " assets.id IN (SELECT asset_id FROM tags_assets WHERE tag_id IN (SELECT id FROM tags WHERE name='{$tagName}'))";
			}
            elseif($filterType == FILTER_INUSE) {
                $tabids = implode(',', $tabids);

                $filterSql = " assets.id IN (SELECT asset_id FROM tags_assets WHERE user_id = ".$this->m_user->getId().")";
                $filterSql .= " OR assets.id IN (SELECT picture_asset_id FROM graphical_passwords WHERE user_id = ".$this->m_user->getId().")";
                $filterSql .= " OR assets.id IN (SELECT asset_id FROM tab WHERE user_id = ".$this->m_user->getId()." AND ID IN(".$tabids."))";
                $filterSql .= " OR assets.id IN (SELECT profile_picture_id FROM user WHERE id = ".$this->m_user->getId().")"; //TODO: should be able to get picture id from memory instead of sql.
                $filterSql .= " OR assets.id IN (SELECT a.id from tab t, page p, block b, assets a WHERE".
                              " b.page_id = p.id AND p.tab_id = t.ID AND b.user_id = ".$this->m_user->getId().
                              " AND t.ID IN ($tabids) AND (b.picture0 = a.id OR b.picture1 = a.id))";
            }
			else {
				// Filter doesn't exist
				throw new Exception("TechDis: '{$filterType}' is not a valid filter type");
			}

			// Add type filter
			if(isset($this->m_assetTypeFilter)) {
				if($filterSql != '') $filterSql .= ' AND';
				$filterSql .= " type='{$this->m_assetTypeFilter}'";
			}
			
			// Retrieve the assets from DB and then return them
			if(isset($this->m_user)) {
				$this->m_assets[$filterKey] = Asset::RetrieveUsersAssets($this->m_user, $filterSql);
			}
			elseif(isset($this->m_group)) {
				$this->m_assets[$filterKey] = Asset::RetrieveGroupAssets($this->m_group, $filterSql);
			}
			return $this->m_assets[$filterKey];
		}
	}
	
	public function setMode($value)
	{
		$this->m_mode = $value;
	}

	public function getSelectedAsset()
	{
		return $this->m_selectedAsset;
	}

	public function setSelectedAsset($assetId, $filterType = self::FILTER_ALL)
	{
		$filteredAssets = $this->getAssets($filterType);
		
		if( isset($assetId) && isset($filteredAssets[$assetId]))
		{
			// Get asset with currentId
			$theAsset = $filteredAssets[$assetId];
			Debugger::debug("Asset {$assetId} found", 'Asset::setSelectedAsset', Debugger::LEVEL_INFO);
		}
		else {
			// Get first asset
			if( count($filteredAssets) > 0 ) {
				$assetKeys = array_keys($filteredAssets);
				$theAsset = $filteredAssets[$assetKeys[0]];
				Debugger::debug("Asset {$assetId} not found ", 'Asset::setSelectedAsset', Debugger::LEVEL_WARNING);
			}
		}
		
		// Set the value, if we have a valid one
		if( isset($theAsset) ) {
			$this->m_selectedAsset = $theAsset;
		}
	}
	
	/* ** Display opperations ** */

	/**
	 * Creates HTML list of thumbnails of all assets in this collection
	 * @return 
	 * @param $page Page[optional] The containing page for the list
	 * @param $filter Object[optional] A filter constant on how to select the images to show
	 * @param $commandQueryParams Object[optional] Any commands to pass on in the thumbnail links
	 */
	public function HtmlThumbnails(SimplePage $page, User $user, $filter = self::FILTER_ALL, $commandQueryParams = null, $filterQueryParams = null, $showMenus=true, $imageSize=Image::SIZE_THUMBNAIL)
	{
		// Get the assets
		$filteredAssets = $this->getAssets($filter);

		// Create the HTML thumbnails for all assets
		$html = '';
		if( isset($filteredAssets) && count($filteredAssets) > 0 ) {
			// Get HTML for each asset

			foreach($filteredAssets as $asset) {
				if( isset($page) && $showMenus ) {
					$linkParams = array('c'=>$asset->getId());
					$linkParams = (is_array($commandQueryParams))? array_merge( $linkParams, $commandQueryParams ) : $linkParams;
					$link = Link::CreateImageLink($asset, $page->PathWithQueryString($linkParams), $imageSize);
					$html .= '<li>' . $link->Html() . '</li>';
				}
				else {
					$html .= "<li>{$asset->Html($imageSize)}<br />{$asset->getTitle()}</li>";
				}
			}
		}
		else {
			// Empty image
			Debugger::debug("No images", 'AssetCollection::HtmlThumbnails_1', Debugger::LEVEL_INFO);
			$html = '<li>No images</li>';
		}

		$theme = $user->getTheme();
		
		if($showMenus)
		{
			// Filters
			
			if(!isset($filterQueryParams)) $filterQueryParams = array();
			$filterMenu = new Menu( array(
										Link::CreateIconLink('All', $page->PathWithQueryString( array_merge(array('f'=>AssetCollection::FILTER_ALL), $filterQueryParams) ), $theme->Icon('show-all'), array('title' => 'Filter All')),
										Link::CreateIconLink('Today', $page->PathWithQueryString( array_merge(array('f'=>AssetCollection::FILTER_TODAY), $filterQueryParams) ), $theme->Icon('today'), array('title' => 'Filter Today')),
										Link::CreateIconLink('Days', $page->PathWithQueryString( array_merge(array('f'=>AssetCollection::FILTER_DAYS), $filterQueryParams) ), $theme->Icon('days-old'), array('title' => 'Filter Days')),
										Link::CreateIconLink('Week', $page->PathWithQueryString( array_merge(array('f'=>AssetCollection::FILTER_WEEK), $filterQueryParams) ), $theme->Icon('weeks-old'), array('title' => 'Filter Week')),
										Link::CreateIconLink('Month', $page->PathWithQueryString( array_merge(array('f'=>AssetCollection::FILTER_MONTH), $filterQueryParams) ), $theme->Icon('months-old'), array('title' => 'Filter Months'))
									), 'filters1', 'Show');
			$filterMenu->setClass('inline-list');
			$filterMenu2 = new Menu( array(
										Link::CreateIconLink('Favourites', $page->PathWithQueryString( array_merge(array('f'=>AssetCollection::FILTER_FAVOURITES), $filterQueryParams) ), $theme->Icon('tag-as-fave2'), array('title' => 'Filter Favourites'))
									), 'filters2');
			$filterMenu2->setClass('inline-list');

			// Set up tag list
			$tagList = Tag::RetrieveByUser($user);
			$tagArray = array();
			foreach($tagList as $tag) {
				$tagArray[] = $tag->FilterLink($page, $filterQueryParams)->Html();
			}

			// Set up tag list
			$tags = (count($tagArray) > 0) ?
							'<p class="clear"><strong>Tags:</strong> ' . implode(', ', $tagArray) . '</p>':
							'';
		}

		$html = '<div id="collection-scroller"><p id="scroll-left"><a class="prev"></a></p><ul class="items">' .  $html . '</ul><p id="scroll-right"><a class="next"></a></p></div>';

		if($showMenus)
		{
			$html .= '<div class="clear">'
					. $theme->HtmlMenu($filterMenu, Theme::LEFT)
					. $theme->HtmlMenu($filterMenu2, Theme::RIGHT) . $tags;
		}
		$html .= '</div>';

		return $html;
	}
	
	public function HtmlSelectedAssetDetailed(Theme $theme, SimplePage $page)
	{
		// atempt to set to first asset, if none are currently set
		if( !isset($this->m_selectedAsset) ) {
			$this->setSelectedAsset(null);
		}

		// Get the HTML for selected asset
		if( isset($this->m_selectedAsset) ) {
			$html = '<div class="box-image">' . $this->m_selectedAsset->Html(Image::SIZE_BOX) . '</div>';
			//$html .= '<div class="box-text">' . $this->m_selectedAsset->HtmlDescription();

			$assetOptions = new Menu(null, 'image-tools');
			$assetOptions->addLink( Link::CreateIconLink('Delete', $page->PathWithQueryString(array('a'=>CollectionEventDispatcher::ACTION_DELETE_ASSET, 'c'=>$this->m_selectedAsset->getId())), $theme->Icon('delete', 'Delete'), array('title' => 'Delete')) );
			if($this->m_mode==self::MODE_SHOW && !$this->m_selectedAsset->isPublic()) {
				$assetOptions->addLink( Link::CreateIconLink('Edit', $page->PathWithQueryString(array('a'=>CollectionEventDispatcher::ACTION_EDIT_ASSET, 'c'=>$this->m_selectedAsset->getId())), $theme->Icon('edit', 'Edit'), array('title' => 'Edit')) );
			}

			$html .= '<div class="box-text">';

			$html .= '<div style="width: 30%; float: left;">';

			// Favourite
			if($this->m_selectedAsset->isFavourite()) {
				$html .= '<p>' . $theme->Icon('fav', 'Favourite')->Html() . ' This is a favourite</p>';
				$assetOptions->addLink( Link::CreateIconLink('Remove favourite', $page->PathWithQueryString(array('a'=>CollectionEventDispatcher::ACTION_REMOVE_FAVOURITE, 'c'=>$this->m_selectedAsset->getId())), $theme->Icon('remove-fav', 'Remove favourite')) );
			}
			else {
				$assetOptions->addLink( Link::CreateIconLink('Add as favourite', $page->PathWithQueryString(array('a'=>CollectionEventDispatcher::ACTION_ADD_FAVOURITE, 'c'=>$this->m_selectedAsset->getId())), $theme->Icon('add-fav', 'Add as favourite')) );
			}

			// Tags
			$tags = Tag::RetrieveByAsset($this->m_selectedAsset, $this->m_user);
			if(count($tags) > 0) {
				$html .= '<h3>Tags</h3><ul>';
				foreach($tags as $tag) {
					$html .= '<li>' . $tag->FilterLink($page)->Html();
					$removeLink = $tag->FilterLinkRemove($page, $this->m_selectedAsset->getId());
					if(isset($removeLink)) $html .= ' |' . $removeLink->Html();
					$html .= '</li>';
				}
				$html .= '</ul>';
			}

			// Add new tag
			$html .= '<form action="' . $page->PathWithQueryString() . '" method="post">' .
				'<input type="hidden" name="c" value="' . $this->m_selectedAsset->getId() . '" />' .
				'<input id="txtNewTag" type="text" name="newtag" maxlength="28" /><input type="submit" value="Add tag" />' .
				'</form>';

			$html .= '</div>' . $assetOptions->Html() . '</div>';
			return $html;
		}
		else {
			return '<p>Empty</p>';
		}
	}

	public function HtmlSelectedAssetTitle(SimplePage $page)
	{
		$assetTitle = (isset($this->m_selectedAsset)) ? $this->m_selectedAsset->getTitle() : 'No asset';

		if($this->m_mode == self::MODE_EDIT && isset($this->m_selectedAsset)) {
			$assetTitle = '<form method="post" action="' . $page->PathWithQueryString() . '">' .
				'<input type="text" name="title" value="' . $assetTitle . '" /> ' .
				'<input type="hidden" name="c" value="' .$this->m_selectedAsset->getId(). '" />' .
				'<input type="submit" value="Save" /></form>';
		}

		return '<h2>' . $assetTitle . '</h2>';
	}
}