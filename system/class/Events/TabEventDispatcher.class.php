<?php

/**
 * The Tab Event Dispatcher
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: TabEventDispatcher.class.php 769 2009-08-14 11:09:03Z richard $
 * @link       NA
 * @since      NA
*/

include_once('EventDispatcher.class.php');

/**
 * Collects user commands and calls the set event functions for a tab page
 */
class TabEventDispatcher extends EventDispatcher
{
	// Action constants
	const ACTION_EDIT_ICON = 'editIcon';
	const ACTION_SAVE_ICON = 'saveIcon';

	/* ** Private member data ** */

	// Member dynamic functions
	private $mf_onDeleteTabHandler;
	private $mf_onEditIconHandler;
	private $mf_onEnterEditTabHandler;
	private $mf_onEnterShowTabHandler;
	private $mf_onNewTabHandler;
	private $mf_onSaveIconHandler;
	private $mf_onSaveTabHandler;

	/* ** Accessors ** */

	public function setOnDeleteTabHandler($handlerFunction) {
		$this->setHandler($this->mf_onDeleteTabHandler, $handlerFunction);
	}

	/**
	 * Entering a mode that lets the user choose the icon for this tab
	 * @param String $handlerFunction Handler function name
	 */
	public function setOnEditIconHandler($handlerFunction) {
		$this->setHandler($this->mf_onEditIconHandler, $handlerFunction);
	}

	public function setOnEnterEditTabHandler($handlerFunction) {
		$this->setHandler($this->mf_onEnterEditTabHandler, $handlerFunction);	
	}

	public function setOnEnterShowTabHandler($handlerFunction) {
		$this->setHandler($this->mf_onEnterShowTabHandler, $handlerFunction);	
	}

	public function setOnNewTabHandler($handlerFunction) {
		$this->setHandler($this->mf_onNewTabHandler, $handlerFunction);
	}

	public function setOnSaveIconHandler($handlerFunction) {
		$this->setHandler($this->mf_onSaveIconHandler, $handlerFunction);
	}

	public function setOnSaveTabHandler($handlerFunction) {
		$this->setHandler($this->mf_onSaveTabHandler, $handlerFunction);	
	}
	
	/* ** Public methods ** */
	
	/**
	 * Call this when you've set all the event handlers
	 * @return 
	 */
	public function DispatchEvents()
	{
		$tabName = Safe::get('name');
		$this->checkRedirectTabs($tabName);
		
		$sortMethod = Safe::getWithDefault('sort', 'new', PARAM_ALPHANUMEXT);
		
		// Get tab
		
		if( isset($tabName) ){
			$tab = $this->m_user->getTabByName($tabName, $sortMethod);
		}
		else {
			$tabId = Safe::post('tab_id', PARAM_INT);
			if(!empty($tabId) && $tabId > 0) {
				$tab = Tab::GetTabById($tabId);	
			}
		}
		if( !isset($tab) ) {
			$tab = new Tab(null, 'Page not found.');
		}

		$mode = Safe::getWithDefault('mode', SimplePage::MODE_SHOW, PARAM_ALPHANUMEXT);

		// Get page
		$this->m_page = new SimplePage( $tab->getName() );
		$this->m_page->setMode($mode);
		$this->m_page->getJavaScriptVariables()->addVariable('phpTabPlace', $tab->getIndex());
		
		// Call parent function and stop if it raises any events
		if( parent::DispatchEvents() ) return true;
		
		// ** Save event - onSaveTab
		if($this->isAction(self::ACTION_SAVE) && isset($this->mf_onSaveTabHandler) )
		{
			// Collect user input
			$newTabName = Safe::post('title');
			$newTabName = substr($newTabName, 0, Tab::MAX_TITLE_LENGTH); // Doesn't allow larger titles
			
			// Call handler
			call_user_func($this->mf_onSaveTabHandler, $tab, $newTabName);
			return true;
		}

		// ** Edit icon event
		elseif($this->isAction(self::ACTION_EDIT_ICON)) {
			$pictureFilter = Safe::getWithDefault('f', AssetCollection::FILTER_ALL);

            $tag = Safe::get('tag');
			if(isset($tag)) $pictureFilter = 'tag=' . $tag;

			// Call handler
			call_user_func($this->mf_onEditIconHandler, $tab, $this->m_page, $sortMethod, $pictureFilter);

			// No other events to be called
			return true;
		}

		// ** Save icon event
		elseif($this->isAction(self::ACTION_SAVE_ICON)) {
			$iconAssetId = Safe::get('c');

			// Call handler
			call_user_func($this->mf_onSaveIconHandler, $tab, $iconAssetId, $sortMethod);

			// No other events to be called
			return true;
		}

		// ** New Tab event
		elseif($this->isAction(self::ACTION_NEW_TAB) && isset($this->mf_onNewTabHandler)) {
			// Call handler
			call_user_func($this->mf_onNewTabHandler);

			// No other events to be called
			return true;
		}

		elseif($this->isAction(self::ACTION_DELETE) && isset($this->mf_onDeleteTabHandler) ) {
			// Call handler
			call_user_func($this->mf_onDeleteTabHandler, $tab);

			// No other events to be called
			return true;
		}

		// ** Enter tab in ?? mode events (only one)
		
		// Enter tab event - onEnterShowTab
		if( $mode==SimplePage::MODE_SHOW && isset($this->mf_onEnterShowTabHandler) ) {
			// Call handler
			call_user_func($this->mf_onEnterShowTabHandler, $tab, $this->m_page, $sortMethod);
			return true;
		}
		// Enter tab event - onEnterShowTab
		elseif( $mode==Page::MODE_EDIT && isset($this->mf_onEnterEditTabHandler) ) {
			// Call handler
			call_user_func($this->mf_onEnterEditTabHandler, $tab, $this->m_page, $sortMethod);
			return true;
		}
		
		// No events were called
		return false;
	}
	
	/* ** Private methods ** */
	
	
	private function checkRedirectTabs($tabName)
	{
		//print '===' . $tabName;
		// Redirect special cases (All tab navigation is done through tab links, but not all tabs link to a tab)
		switch($tabName) {
			case Tab::HOME_PAGE_NAME:
				header( 'Location: .' ) ;
				break;
			case Tab::COLLECTION_PAGE_NAME:
				header( 'Location: collection.php' ) ;
				break;
			case Tab::MANAGETABS_PAGE_NAME:
				header( 'Location: managetabs.php' ) ;
				break;
			default:
				// Just continue with page, don't redirect
		}
	}
}
