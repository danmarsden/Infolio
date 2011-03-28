<?php

/**
 * The Collection Event Dispatcher
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: CollectionEventDispatcher.class.php 766 2009-08-13 15:43:48Z richard $
 * @link       NA
 * @since      NA
*/

include_once('EventDispatcher.class.php');

/**
 * Collects user commands and calls the set event functions for a page
 */
class CollectionEventDispatcher extends EventDispatcher
{
	// Action constants
	const ACTION_ADD_FAVOURITE = 'add-favourite';
	const ACTION_DELETE_ASSET = 'del-asset';
	const ACTION_EDIT_ASSET = 'edit-asset';
	const ACTION_REMOVE_FAVOURITE = 'del-favourite';

	// Member dynamic functions
	private $mf_onDeleteAssetHandler;
	private $mf_onEditAssetHandler;
	private $mf_onEnterShowCollectionHandler;
	private $mf_onFavouriteSetHandler;
	private $mf_onSaveAssetHandler;
	private $mf_onTagAddHandler;
	private $mf_onTagRemoveHandler;
	private $mf_onUploadHandler;

	/* ** Accessors ** */

	public function setOnDeleteAssetHandler($handlerFunction) {
		$this->setHandler($this->mf_onDeleteAssetHandler, $handlerFunction);
	}

	public function setOnEditAssetHandler($handlerFunction) {
		$this->setHandler($this->mf_onEditAssetHandler, $handlerFunction);
	}

	public function setOnEnterShowCollectionHandler($handlerFunction) {
		$this->setHandler($this->mf_onEnterShowCollectionHandler, $handlerFunction);	
	}

	public function setOnFavouriteSetHandler($handlerFunction) {
		$this->setHandler($this->mf_onFavouriteSetHandler, $handlerFunction);
	}

	public function setOnSaveAssetHandler($handlerFunction) {
		$this->setHandler($this->mf_onSaveAssetHandler, $handlerFunction);
	}

	public function setOnTagAddHandler($handlerFunction) {
		$this->setHandler($this->mf_onTagAddHandler, $handlerFunction);
	}

	public function setOnTagRemoveHandler($handlerFunction) {
		$this->setHandler($this->mf_onTagRemoveHandler, $handlerFunction);
	}

	public function setOnUploadHandler($handlerFunction) {
		$this->setHandler($this->mf_onUploadHandler, $handlerFunction);	
	}
	
	/* ** Public methods ** */
	
	/**
	 * Call this when you've set all the event handlers
	 * @return 
	 */
	public function DispatchEvents()
	{
		$this->m_page = new SimplePage($this->m_user->getFirstName() . "'s collection");
		$this->m_page->setName('Collection');
		$this->m_page->getJavaScriptVariables()->addVariable('phpTabPlace', 1);
		
		// Call parent function and stop if it raises any events
		if( parent::DispatchEvents() ) return true;
		
		// Get input
		$assetId = Safe::GetArrayIndexValueWithDefault($this->m_allVars, 'c', null);
		$filter = Safe::GetArrayIndexValueWithDefault($this->m_queryStringVars, 'f', AssetCollection::FILTER_ALL);

		// Get tag filter
        $tag = Safe::get('tag');
		if(!empty($tag)) {
			$filter = "tag={$tag}";
		}

		// Add favourite event - onFavouriteAdd
		if($this->isAction(self::ACTION_ADD_FAVOURITE)) {
			// Call handler
			call_user_func($this->mf_onFavouriteSetHandler, $this->m_page, $assetId, true);
		}

		// Add favourite event - onFavouriteRemove
		if($this->isAction(self::ACTION_REMOVE_FAVOURITE)) {
			// Call handler
			call_user_func($this->mf_onFavouriteSetHandler, $this->m_page, $assetId, false);
		}

		// Upload file event - onUpload
		if($this->isAction(self::ACTION_UPLOAD)) {
			// Call handler
			call_user_func($this->mf_onUploadHandler, $this->m_page);
			return true;
		}

		// Edit asset action - onEditAsst
		if($this->isAction(self::ACTION_EDIT_ASSET)) {
			// Call handler
			call_user_func($this->mf_onEditAssetHandler, $this->m_page, $assetId, $filter);
			return true;
		}

		// Save asset
		if(isset($_POST['title'])) {
			// Call handler
			call_user_func($this->mf_onSaveAssetHandler, $this->m_page, $assetId, $_POST['title']);
			return true;
		}

		// Delete asset event - onDeleteAsset
		if($this->isAction(self::ACTION_DELETE_ASSET)) {
			// Call handler
			call_user_func($this->mf_onDeleteAssetHandler, $this->m_page, $assetId);
			return true;
		}

		// Add tag event
		if(isset($_POST['newtag'])) {
			call_user_func($this->mf_onTagAddHandler, $this->m_page, $assetId, Safe::Input($_POST['newtag']));
			return true;
		}

		// Remove tag event
        $deltag = Safe::get('deltag');
		if(!empty($deltag)) {
			call_user_func($this->mf_onTagRemoveHandler, $this->m_page, $assetId, $deltag);
			return true;
		}

		// Enter collection page in show mode event - OnEnterShowCollection
		// Must be last event
		if( isset($this->mf_onEnterShowCollectionHandler) ) {
			// Call handler
			call_user_func($this->mf_onEnterShowCollectionHandler, $this->m_page, $assetId, $filter);

			// No other events to be called
			return true;
		}

		// No events were called
		return false;
	}
}