<?php

/**
 * The Manage Event Dispatcher
 *
 * LICENSE: This is an Open Source Project
*/

include_once('EventDispatcher.class.php');

/**
 * Collects user commands and calls the set event functions for a page
 */
class ManageTabsEventDispatcher extends EventDispatcher
{
    
    private $mf_onEnterShowManageTabsHandler;
    private $mf_onMoveTabHandler;

	public function setOnEnterShowManageTabsHandler($handlerFunction) {
		$this->setHandler($this->mf_onEnterShowManageTabsHandler, $handlerFunction);	
	}

    public function setOnMoveTabHandler($handlerFunction) {
		$this->setHandler($this->mf_onMoveTabHandler, $handlerFunction);
	}

	/* ** Public methods ** */
	
	/**
	 * Call this when you've set all the event handlers
	 * @return 
	 */
	public function DispatchEvents()
	{
        
        $mode = Safe::GetArrayIndexValueWithDefault($this->m_queryStringVars, 'mode', SimplePage::MODE_SHOW);

		$this->m_page = new SimplePage($this->m_user->getFirstName() . "'s tabs");
		$this->m_page->setName('manage tabs');
		$this->m_page->getJavaScriptVariables()->addVariable('phpTabPlace', 1);
		
		// Call parent function and stop if it raises any events
		if( parent::DispatchEvents() ) return true;
		
		// Get input
        $tab = Safe::GetArrayIndexValueWithDefault($this->m_allVars, 't', null);

		// Tab move action - onTabMove
        if(($mode == self::ACTION_MOVE_UP || $mode == self::ACTION_MOVE_DOWN) && isset($this->mf_onMoveTabHandler)) {
			// Call handler
            call_user_func($this->mf_onMoveTabHandler, $this->m_page, $mode, $tab);

			return true;
        }

		// Enter tabs page in show mode event - OnEnterShowTabs
		// Must be last event
		if($mode == SimplePage::MODE_SHOW && isset($this->mf_onEnterShowManageTabsHandler) ) {
			// Call handler
			call_user_func($this->mf_onEnterShowManageTabsHandler, $this->m_page);

			// No other events to be called
			return true;
		}

		// No events were called
		return false;
	}

}
