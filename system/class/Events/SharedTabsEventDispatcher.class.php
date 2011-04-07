<?php

/**
 * sharedtabsEventDispatcher.class.php -
 *
 * @author     Dan Marsden, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
*/

include_once('EventDispatcher.class.php');

/**
 * Collects user commands and calls the set event functions for a page
 */
class SharedTabsEventDispatcher extends EventDispatcher
{

    private $mf_onEnterShowSharedTabsHandler;
    private $mf_onMoveTabHandler;

	public function setOnEnterShowSharedTabsHandler($handlerFunction) {
		$this->setHandler($this->mf_onEnterShowSharedTabsHandler, $handlerFunction);
	}

	/* ** Public methods ** */

	/**
	 * Call this when you've set all the event handlers
	 * @return
	 */
	public function DispatchEvents()
	{

        $mode = Safe::getWithDefault('mode', SimplePage::MODE_SHOW, PARAM_ALPHANUMEXT);

		$this->m_page = new SimplePage("Shared tabs");
		$this->m_page->setName('Shared Tabs');
		$this->m_page->getJavaScriptVariables()->addVariable('phpTabPlace', 1);

		// Call parent function and stop if it raises any events
		if( parent::DispatchEvents() ) return true;

		// Get input
        $tab = Safe::request('t');


		// Enter tabs page in show mode event - OnEnterShowTabs
		// Must be last event
		if($mode == SimplePage::MODE_SHOW && isset($this->mf_onEnterShowSharedTabsHandler) ) {
			// Call handler
			call_user_func($this->mf_onEnterShowSharedTabsHandler, $this->m_page);

			// No other events to be called
			return true;
		}

		// No events were called
		return false;
	}

}
