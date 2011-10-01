<?php

/**
 * The Settings Event Dispatcher
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: HelpEventDispatcher.class.php 809 2009-11-03 08:45:09Z richard $
 * @link       NA
 * @since      NA
*/

include_once('EventDispatcher.class.php');

/**
 * Collects user commands and calls the set event functions for a page
 */
class HelpEventDispatcher extends EventDispatcher
{
	// Member dynamic functions
	private $mf_onEnterPageHandler;

	/* ** Accessors ** */
	
	public function setOnEnterPageHandler($handlerFunction) {
		$this->setHandler($this->mf_onEnterPageHandler, $handlerFunction);
	}

	/* ** Public methods ** */
	
	/**
	 * Call this when you've set all the event handlers
	 * @return 
	 */
	public function DispatchEvents()
	{
		$this->m_page = new SimplePage("Help");
		$this->m_page->setName('Help');
		$this->m_page->getJavaScriptVariables()->addVariable('phpTabPlace', 0);
		
		// Call parent function and stop if it raises any events
		if( parent::DispatchEvents() ) return true;

		// Enter page event - OnEnterHomePage
		if( isset($this->mf_onEnterPageHandler) ) {
			$helpItem = Safe::getWithDefault('section', 0);

			// Call handler
			call_user_func($this->mf_onEnterPageHandler, $this->m_page, $helpItem);
			
			// No other events to be called
			return true;
		}

		// No events were called
		return false;
	}
}