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
 * @version    $Id: SettingsEventDispatcher.class.php 808 2009-11-02 15:51:14Z richard $
*/

include_once('EventDispatcher.class.php');

/**
 * Collects user commands and calls the set event functions for a page
 */
class SettingsEventDispatcher extends EventDispatcher
{
	// Member dynamic functions
	private $mf_onChangeColourHandler;
	private $mf_onEnterPageHandler;
	private $mf_onSetPasswordHandler;

	/* ** Accessors ** */
	
	public function setOnChangeColourHandler($handlerFunction) {
		$this->setHandler($this->mf_onChangeColourHandler, $handlerFunction);	
	}
	
	public function setOnEnterPageHandler($handlerFunction) {
		$this->setHandler($this->mf_onEnterPageHandler, $handlerFunction);	
	}

	public function setOnSetPasswordHandler($handlerFunction) {
		$this->setHandler($this->mf_onSetPasswordHandler, $handlerFunction);
	}
	
	/* ** Public methods ** */
	
	/**
	 * Call this when you've set all the event handlers
	 * @return 
	 */
	public function DispatchEvents()
	{
		$this->m_page = new SimplePage($this->m_user->getFirstName() . "'s settings");
		$this->m_page->setName('Settings');
		$this->m_page->getJavaScriptVariables()->addVariable('phpTabPlace', 0);
		
		// Call parent function and stop if it raises any events
		if( parent::DispatchEvents() ) return true;

		$newColour = Safe::get('colour');
		$newPassword = Safe::request('passwd');
		$changedPassword = Safe::getWithDefault('changed', false);

		// Change colour event
		if( isset($newColour) && isset($this->mf_onChangeColourHandler) ) {
			// Call handler
			call_user_func($this->mf_onChangeColourHandler, $this->m_page, $newColour);
			
			// No other events to be called
			return true;
		}

		if ( isset($newPassword) ) {
			// Call handler
			call_user_func($this->mf_onSetPasswordHandler, $this->m_page, $newPassword);

			// No other events to be called
			return true;
		}
		
		// Enter page event - OnEnterHomePage
		if( isset($this->mf_onEnterPageHandler) ) {
			// Call handler
			call_user_func($this->mf_onEnterPageHandler, $this->m_page, $changedPassword);
			
			// No other events to be called
			return true;
		}

		// No events were called
		return false;
	}
}