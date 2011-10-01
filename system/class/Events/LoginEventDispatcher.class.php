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
 * The Collection Event Dispatcher
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: LoginEventDispatcher.class.php 808 2009-11-02 15:51:14Z richard $
 * @link       NA
 * @since      NA
*/

include_once('EventDispatcher.class.php');

/**
 * Collects user commands and calls the set event functions for a page
 */
class LoginEventDispatcher extends EventDispatcher
{
	/* ** Constants ** */
	const ACTION_LOGIN = 'login';

	// Member dynamic functions
	private $mf_onEnterPageHandler;
	private $mf_onLoginHandler;

	/* ** Accessors ** */
	
	public function setOnEnterPageHandler($handlerFunction) {
		$this->setHandler($this->mf_onEnterPageHandler, $handlerFunction);	
	}
	
	public function setOnLoginHandler($handlerFunction) {
		$this->setHandler($this->mf_onLoginHandler, $handlerFunction);	
	}
	
	/* ** Public methods ** */
	
	/**
	 * Call this when you've set all the event handlers
	 * @return 
	 */
	public function DispatchEvents()
	{
        $ins = Safe::get('institution',PARAM_ALPHANUMEXT);
		if(!empty($ins)) {
			$institutionName = $ins;
		}
		else {
			// No institute means they must choose where they are from.
			header("Location: /where.php");
		}
		
		$this->m_page = new SimplePage('Login');
		$this->m_page->setName('Login');
		
		parent::DispatchEvents(); //(can't store size in user profile when not logged in)
		
		$action = Safe::post('a', null);
		
		// ** Block move events (only one)
		
		// Login event - onLogin
		if( $this->isAction(self::ACTION_LOGIN) && isset($this->mf_onLoginHandler) ) {
			// Collect user input
			$username = Safe::post('tUser');
			$password = Safe::post('tPass');
			
			// Call handler
			call_user_func($this->mf_onLoginHandler, $this->m_page, $username, $password, $institutionName);
			
			// No other events to be called
			return true;
		}
		
		// Enter home page event - OnEnterHomePage
		if( isset($this->mf_onEnterPageHandler) ) {
			// Call handler
			call_user_func($this->mf_onEnterPageHandler, $this->m_page, $institutionName);
			
			// No other events to be called
			return true;
		}

		// No events were called
		return false;
	}
}