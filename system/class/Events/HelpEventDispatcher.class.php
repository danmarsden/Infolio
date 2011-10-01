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
 * The Settings Event Dispatcher
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: HelpEventDispatcher.class.php 809 2009-11-03 08:45:09Z richard $
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