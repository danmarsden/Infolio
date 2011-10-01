<?php

/**
 * The Collection Event Dispatcher
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: HomePageEventDispatcher.class.php 767 2009-08-14 10:26:50Z richard $
 * @link       NA
 * @since      NA
*/

include_once('EventDispatcher.class.php');

/**
 * Collects user commands and calls the set event functions for a page
 */
class HomePageEventDispatcher extends EventDispatcher
{
	/* ** Constants ** */
	const ACTION_CHANGE_USER_DESCRIPTION = 'changeuserdesc';
	const ACTION_CHANGE_USER_IMAGE = 'changeuserpic';

	// Member dynamic functions
	private $mf_onChangeUserDescriptionHandler;
	private $mf_onChangeUserPictureHandler;
	private $mf_onEnterPageHandler;
	private $mf_onSetUserDescriptionHandler;
	private $mf_onSetUserPictureHandler;

	/* ** Accessors ** */

	public function setOnChangeUserDescriptionHandler($handlerFunction) {
		$this->setHandler($this->mf_onChangeUserDescriptionHandler, $handlerFunction);
	}

	public function setOnChangeUserPictureHandler($handlerFunction) {
		$this->setHandler($this->mf_onChangeUserPictureHandler, $handlerFunction);	
	}
	
	public function setOnEnterPageHandler($handlerFunction) {
		$this->setHandler($this->mf_onEnterPageHandler, $handlerFunction);	
	}

	public function setOnSetUserDescriptionHandler($handlerFunction) {
		$this->setHandler($this->mf_onSetUserDescriptionHandler, $handlerFunction);
	}

	public function setOnSetUserPictureHandler($handlerFunction) {
		$this->setHandler($this->mf_onSetUserPictureHandler, $handlerFunction);	
	}
	
	/* ** Public methods ** */
	
	/**
	 * Call this when you've set all the event handlers
	 * @return 
	 */
	public function DispatchEvents()
	{
		$this->m_page = new SimplePage($this->m_user->getFirstName() . "'s home page");
		$this->m_page->setName('About-me');
		$this->m_page->getJavaScriptVariables()->addVariable('phpTabPlace', 0);
		
		// Call parent function and stop if it raises any events
		if( parent::DispatchEvents() ) return true;
		
		$newPictureId = Safe::get('c', PARAM_INT);
		$newDescription = Safe::get('description');
		
		// Change user picture - OnChangeUserPicture
		if( $this->isAction(self::ACTION_CHANGE_USER_IMAGE) && isset($this->mf_onChangeUserPictureHandler)) {
			$pictureFilter = Safe::getWithDefault('f', AssetCollection::FILTER_ALL);
            $tag = Safe::get('tag');
			if(!empty($tag)) $pictureFilter = 'tag=' . $tag;

			// Call handler
			call_user_func($this->mf_onChangeUserPictureHandler, $this->m_page, $pictureFilter);
			
			// No other events to be called
			return true;
		}

		// Change user description - onChangeUserDescription
		if ( $this->isAction(self::ACTION_CHANGE_USER_DESCRIPTION) ) {
			// Call handler
			call_user_func($this->mf_onChangeUserDescriptionHandler, $this->m_page);

			// No other events to be called
			return true;
		}

		// Set user description - onSetUserDescription
		if( isset($newDescription) ) {
			// Call handler
			call_user_func($this->mf_onSetUserDescriptionHandler, $this->m_page, $newDescription);

			// No other events to be called
			return true;
		}

		// Set user picture - OnSetUserPicture
		if( isset($newPictureId) && isset($this->mf_onSetUserPictureHandler) ) {
			// Call handler
			call_user_func($this->mf_onSetUserPictureHandler, $this->m_page, $newPictureId);
			
			// No other events to be called
			return true;
		}
		
		// Enter home page event - OnEnterHomePage
		if( isset($this->mf_onEnterPageHandler) ) {
			// Call handler
			call_user_func($this->mf_onEnterPageHandler, $this->m_page);
			
			// No other events to be called
			return true;
		}

		// No events were called
		return false;
	}
}