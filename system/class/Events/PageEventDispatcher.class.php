<?php

/**
 * The Page Event Dispatcher
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: PageEventDispatcher.class.php 768 2009-08-14 10:48:42Z richard $
 * @link       NA
 * @since      NA
*/

include_once('EventDispatcher.class.php');

/**
 * Collects user commands and calls the set event functions for a page
 */
class PageEventDispatcher extends EventDispatcher
{
	/* ** Constants ** */
	const ACTION_SAVE_BLOCK = 'saveblock';
	const ACTION_SET_IMAGE = 'setimage';
	
	/* ** Private member data ** */

	// Member dynamic functions
	private $mf_onAttachmentDeleteHandler;
	private $mf_onAttachmentUploadHandler;
	private $mf_onBlockDeleteHandler;
	private $mf_onBlockDownHandler;
	private $mf_onBlockUpHandler;
	private $mf_onBlockSaveHandler;
	private $mf_onDeletePageHandler;
	private $mf_onEnterEditPageHandler;
	private $mf_onEnterShowPageHandler;
	private $mf_onNewPageHandler;
	private $mf_onSavePageHandler;
	private $mf_onSetImageHandler;

	/* ** Accessors ** */
	
	public function setOnAttachmentDeleteHandler($handlerFunction) {
		$this->setHandler($this->mf_onAttachmentDeleteHandler, $handlerFunction);
	}
	
	public function setOnBlockUpHandler($handlerFunction) {
		$this->setHandler($this->mf_onBlockUpHandler, $handlerFunction);	
	}

	public function setOnBlockDeleteHandler($handlerFunction) {
		$this->setHandler($this->mf_onBlockDeleteHandler, $handlerFunction);
	}

	public function setOnBlockDownHandler($handlerFunction) {
		$this->setHandler($this->mf_onBlockDownHandler, $handlerFunction);	
	}
	
	public function setOnEnterEditPageHandler($handlerFunction) {
		$this->setHandler($this->mf_onEnterEditPageHandler, $handlerFunction);	
	}
	
	public function setOnBlockSaveHandler($handlerFunction) {
		$this->setHandler($this->mf_onBlockSaveHandler, $handlerFunction);	
	}
	
	public function setOnDeletePageHandler($handlerFunction) {
		$this->setHandler($this->mf_onDeletePageHandler, $handlerFunction);
	}
	
	public function setOnEnterShowPageHandler($handlerFunction) {
		$this->setHandler($this->mf_onEnterShowPageHandler, $handlerFunction);	
	}
	
	public function setOnNewPageHandler($handlerFunction) {
		$this->setHandler($this->mf_onNewPageHandler, $handlerFunction);
	}
	
	public function setOnSavePageHandler($handlerFunction) {
		$this->setHandler($this->mf_onSavePageHandler, $handlerFunction);
	}
	
	public function setOnSetImageHandler($handlerFunction) {
		$this->setHandler($this->mf_onSetImageHandler, $handlerFunction);
	}

	public function setOnUploadAttachmentHandler($handlerFunction) {
		$this->setHandler($this->mf_onAttachmentUploadHandler, $handlerFunction);
	}
	
	/* ** Public methods ** */
	
	/**
	 * Call this when you've set all the event handlers
	 * @return 
	 */
	public function DispatchEvents()
	{
		$pageId = Safe::GetArrayIndexValueWithDefault($this->m_queryStringVars, 'id', null);
		$mode = Safe::GetArrayIndexValueWithDefault($this->m_queryStringVars, 'mode', Page::MODE_SHOW);
		
		// Create current page object
		if($pageId > 0 ) {
			$this->m_page = Page::GetPageById($pageId);
			$this->m_page->setMode($mode);
			
			$tab = $this->m_user->getTabByPage($this->m_page);
			$this->m_page->getJavaScriptVariables()->addVariable('phpTabPlace', $tab->getIndex());
		}
		// Call parent function and stop if it raises any events
		if( parent::DispatchEvents() ) return true;

		$allInputVars = array_merge($this->m_formVars, $this->m_queryStringVars);
		$action = Safe::GetArrayIndexValueWithDefault($allInputVars, 'a', null);
		
		// Zero page ID indicates new page
		if($pageId == 0 && isset($this->mf_onNewPageHandler)) {
			// Check owner tab is provided
			if( isset($this->m_queryStringVars['tab']) ) {
				// Collect user input
				$tabId = Safe::Input($this->m_queryStringVars['tab']);
				
				// Call handler
				call_user_func($this->mf_onNewPageHandler, $tabId);
			}
			else {
				throw new Exception('Techdis: Request for new page with no owning tab');
			}
			// No other events to be called
			return true;
		}
		
		// ** Block move events (only one)
		
		// Block up event - onBlockUp
		if( isset($_GET['blockup']) && is_numeric($this->m_queryStringVars['blockup']) ) {
			// Collect user input
			$blockId = Safe::Input($this->m_queryStringVars['blockup']);
			
			// Call handler
			call_user_func($this->mf_onBlockUpHandler, $blockId, $this->m_page);
			
			// No other events to be called
			return true;
		}
		// Block down event - onBlockDown
		elseif( isset($_GET['blockdown']) && is_numeric($this->m_queryStringVars['blockdown']) ) {
			// Collect user input
			$blockId = Safe::Input($this->m_queryStringVars['blockdown']);
			
			// Stop commands persisting
			unset($this->m_queryStringVars['blockdown']);
			
			// Call handler
			call_user_func($this->mf_onBlockDownHandler, $blockId, $this->m_page);
			
			// No other events to be called
			return true;
		}

		// ** Delete block event - onBlockDelete
		elseif( isset($_GET['blockdelete']) /*&& is_numeric($_GET['blockdelete']) && isset($this->mf_onBlockDeleteHandler)*/) {
			$blockId = $_GET['blockdelete'];
			call_user_func($this->mf_onBlockDeleteHandler, $this->m_page, $blockId);
		}

		// ** Page save event - onPageSave
		if( isset($this->mf_onSavePageHandler) && $action==PageEventDispatcher::ACTION_SAVE ) {
			// Collect user input
			$title = Safe::Input($this->m_formVars['title']);
			
			// Call handler
			call_user_func($this->mf_onSavePageHandler, $this->m_page, $title);
			
			// No other events to be called
			return true;
		}
		
		// ** Block save event - onBlockSave
		if( isset($this->mf_onBlockSaveHandler) && $action==PageEventDispatcher::ACTION_SAVE_BLOCK ) {
			// Collect user input
			$wordBlocks = array(
								Safe::GetArrayIndexValueWithDefault($this->m_formVars, 'wb0', null),
								Safe::GetArrayIndexValueWithDefault($this->m_formVars, 'wb1', null)
								);
			$blockId = Safe::GetArrayIndexValueWithDefault($this->m_formVars, 'block_id', null);
			$blockTitle = Safe::GetArrayIndexValueWithDefault($this->m_formVars, 'title', null);
			$templateId = Safe::GetArrayIndexValueWithDefault($this->m_formVars, 'template_id', null);
			$blockWeight = Safe::GetArrayIndexValueWithDefault($this->m_formVars, 'weight', null);
			
			// Call handler
			call_user_func($this->mf_onBlockSaveHandler, $this->m_page, $blockTitle, $wordBlocks, $blockId, $templateId, $blockWeight);
			
			// No other events to be called
			return true;
		}

		// Enter delete page mode
		if( $action==EventDispatcher::ACTION_DELETE && isset($this->mf_onDeletePageHandler) ) {
			// Call handler
			call_user_func($this->mf_onDeletePageHandler, $this->m_page);
		}
		
		// ** Image set event - onSetImage
		if( isset($this->mf_onSetImageHandler) && $action==PageEventDispatcher::ACTION_SET_IMAGE ) {
			// Collect user input
			$blockId = Safe::GetArrayIndexValueWithDefault($this->m_queryStringVars, 'blockedit', null);
			$imagePlace = Safe::GetArrayIndexValueWithDefault($this->m_queryStringVars, 'imageedit', null);
			$imageId = Safe::GetArrayIndexValueWithDefault($this->m_queryStringVars, 'c', null);
			
			// Call handler
			call_user_func($this->mf_onSetImageHandler, $blockId, $imagePlace, $imageId, $this->m_page);
			
			// No other events to be called
			return true;
		}

		// Upload file event - onUpload
		if($this->isAction(self::ACTION_UPLOAD) && isset($this->mf_onAttachmentUploadHandler)) {
			// Call handler
			call_user_func($this->mf_onAttachmentUploadHandler, $this->m_page);
			return true;
		}

		// Delete attachment - onAttachmentDelete
		if(isset($_GET['attdelete']) && isset($this->mf_onAttachmentDeleteHandler)) {
			call_user_func($this->mf_onAttachmentDeleteHandler, $this->m_page, $_GET['attdelete']);
			return true;
		}
		
		// Enter page in show mode event - OnEnterShowPage
		if( $mode==Page::MODE_SHOW && isset($this->mf_onEnterShowPageHandler) ) {
			// Call handler
			call_user_func($this->mf_onEnterShowPageHandler, $this->m_page);
		}
		// Enter page in edit mode event - OnEnterEditPage
		elseif( $mode ==  Page::MODE_EDIT && isset($this->mf_onEnterEditPageHandler) ) {
			// Collect user input
			$imageIdForEdit = Safe::GetArrayIndexValueWithDefault($this->m_queryStringVars, 'imageedit', null);
			$blockIdForEdit = Safe::GetArrayIndexValueWithDefault($this->m_queryStringVars, 'blockedit', null);
			$assetFilter = Safe::GetArrayIndexValueWithDefault($this->m_queryStringVars, 'f', AssetCollection::FILTER_ALL);
			if(isset($_GET['tag'])) $assetFilter = 'tag=' . Safe::Input($_GET['tag']);


			// Call handler
			call_user_func($this->mf_onEnterEditPageHandler, $this->m_page, $imageIdForEdit, $assetFilter, $blockIdForEdit);
			
			// No other events to be called
			return true;
		}
		
		// No events were called
		return false;
	}
}