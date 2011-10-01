<?php

/**
 * The Event Dispatcher
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: EventDispatcher.class.php 486 2009-04-17 16:24:31Z richard $
 * @link       NA
 * @since      NA
*/

/**
 * Collects user commands and calls the set event functions
 */
abstract class EventDispatcher
{
	// Action constants
	const ACTION_COLOUR_SWAP = 'swap';
	const ACTION_DELETE = 'delete';
	const ACTION_NEW_TAB = 'new-tab';
	const ACTION_SAVE = 'save';
	const ACTION_UPLOAD = 'upload';
	const ACTION_MOVE_UP = 'move-up';
	const ACTION_MOVE_DOWN = 'move-down';

	/* ** Private member data ** */

	// Member dynamic functions
	private $mf_onColourSwapHandler;
	private $mf_onSizeChangeHandler;

	// Member variables
	protected $m_user;
	private $m_action;
	protected $m_page;

	/**
	 * The constructor
	 * @param Array $queryStringArray The array for the page
	 * @param Arrays $htmlClass A class to apply to this menu
	 */
	public function __construct()
	{
		/* ** Get the action from user input ** */
		$this->m_action = Safe::request('a');
	}
	
	/* ** Accessors ** */
	
	protected function isAction($action)
	{
		Debugger::debug('Current action: ' . $this->m_action, 'EventDispatcher::isAction');
		return $this->m_action == $action;	
	}
	
	public function setOnColourSwapHandler($handlerFunction) {
		$this->setHandler($this->mf_onColourSwapHandler, $handlerFunction);	
	}
	
	public function setOnSizeChangeHandler($handlerFunction) {
		$this->setHandler($this->mf_onSizeChangeHandler, $handlerFunction);	
	}
	
	public function setUser(User $user)
	{
		$this->m_user = $user;
	}
	
	/* ** Public methods ** */
	
	/**
	 * Call this when you've set all the event handlers
	 * @return 
	 */
	public function DispatchEvents()
	{
		// None action events
		
		// Size Change event - onSizeChange
        $fs = Safe::get('fs');
		if( !empty($fs) && isset($this->mf_onSizeChangeHandler) ) {
			// Collect user input
			$fontSize = $fs;
			
			// Call handler
			call_user_func($this->mf_onSizeChangeHandler, $this->m_page, $fontSize);
			
			// No other events to be called
			return true;
		}
		
		// Colour swap event - onColourSwap
		if($this->isAction(self::ACTION_COLOUR_SWAP) && isset($this->mf_onColourSwapHandler) )
		{
			// Call handler
			call_user_func($this->mf_onColourSwapHandler, $this->m_page);
			
			// No other events to be called
			return true;
		}
	}
	
	/* ** Protected methods ** */
	
	protected function setHandler(&$handlerVar, $handlerFunction)
	{
		if ( is_callable($handlerFunction) ) {
			$handlerVar = $handlerFunction;
		}
		else {
			throw new Exception("TechDis: Event function '{$handlerFunction}' doesn't exist");
		}
	}
}
