<?php

/**
 * The Menu Class
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: Menu.class.php 481 2009-04-16 09:03:46Z richard $
 * @link       NA
 * @since      NA
*/

class Menu
{
	/* ** Private member data ** */
	private $m_links;
	private $m_htmlClass;
	private $m_htmlId;
	private $m_title;

	/**
	 * The constructor
	 * @param Array $linkArray An array of link objects
	 * @param string $htmlClass A class to apply to this menu
	 */
	public function __construct($linkArray = null, $htmlId=null, $title=null)
	{
		$this->m_links = $linkArray;
		$this->m_htmlId = $htmlId;
		$this->m_title = $title;
		
		if(!isset($this->m_links)) {
			$this->m_links = array();
		}
	}

	public function addLink($link)
	{
		array_unshift($this->m_links, $link);
	}

	/**
	 * Looks for the passed link and sets it as active if it exists.
	 * Only finds first match if there are several
	 * @return 
	 * @param $url Object
	 */
	public function setAsActiveLink($sectionName)
	{
		foreach($this->m_links as $link) {
			$link->checkActive($sectionName);
		}
	}
	public function setClass($class)
	{
		$this->m_htmlClass = $class;
	}
	
	/**
	 * Makes the HTML for this menu
	 * @return String The HTML for this menu
	 */
	public function Html()
	{
		// Start list
		$html = '<ul';
		// Id
		if( isset($this->m_htmlId) ){
			$html .= ' id="'. $this->m_htmlId .'"';
		}
		// Class
		if( isset($this->m_htmlClass) ){
			$html .= ' class="'. $this->m_htmlClass .'"';
		}
		$html .= '>';

		
		// Add title if there is one
		if( isset($this->m_title) ) {
			$html .= "<li class=\"title\">{$this->m_title}</li>";
		}

		// Loop through all links
		$numLinks = count($this->m_links);
		for ($i=0; $i < $numLinks; $i++) {
			$classes = array();
			
			// Add last class for last link
			if($i == $numLinks - 1) $classes[] = 'last';
			
			// Add active class for active links
			if( $this->m_links[$i]->isActive() ) $classes[] = 'active';

			// Add any classes to list item
			if(count($classes) > 0) {
				$html .= '<li class="'. implode(' ', $classes) .'">';
			}
			else {
				$html .= '<li>';
			}
			
			// Get HTML for link
			$html .= $this->m_links[$i]->Html() .'</li>';
		}
		$html .= '</ul>';
		return $html;
	}
}