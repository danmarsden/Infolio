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
 * The Manage Event Dispatcher
 *

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
        
        $mode = Safe::getWithDefault('mode', SimplePage::MODE_SHOW, PARAM_ALPHANUMEXT);

		$this->m_page = new SimplePage($this->m_user->getFirstName() . "'s tabs");
		$this->m_page->setName('manage tabs');
		$this->m_page->getJavaScriptVariables()->addVariable('phpTabPlace', 1);
		
		// Call parent function and stop if it raises any events
		if( parent::DispatchEvents() ) return true;
		
		// Get input
        $tab = Safe::request('t', PARAM_INT);

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
