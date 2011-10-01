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
 * The Theme Class
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: Theme.class.php 802 2009-10-29 14:29:02Z richard $
*/

/**
 * Class Theme
 * This class will style the website
 */
class Theme
{
	/* ** Constants for enums ** */
	const LEFT = 1;
	const RIGHT = 2;
	
	const SIZE_SMALL = 's-small';
	const SIZE_MEDIUM = null;
	const SIZE_BIG = 's-big';
	
	/* ** Other constants ** */
	// Pure theme names can't have hyphens in them
	const DEFAULT_THEME_NAME = 'red';
	const THEME_RED = 'red';
	const THEME_GREEN = 'green';
	const THEME_BLUE = 'blue';
	const THEME_DARK_BLUE = 'dark_blue';
	const THEME_GREY = 'grey';
	const THEME_YELLOW = 'yellow';
	const THEME_ORANGE = 'orange';
	
	const COLOUR_INVERTED_NAME = 'inv';

	/* ** Private member data ** */
	private $m_colour;
	private $m_colourInverted;
	private $m_size;
	private $m_colourOptions;

	/* ** Constructor ** */
	public function __construct($name = Theme::DEFAULT_THEME_NAME, $size = self::SIZE_MEDIUM)
	{
		$themeParts = explode(' ', $name, 2);

		$this->m_colour = $themeParts[0];
		$this->m_colourInverted = (count($themeParts)>1)? self::COLOUR_INVERTED_NAME : null;
		$this->m_size = $size;
		
		$this->m_colourOptions = array(self::THEME_RED,
										self::THEME_GREEN,
										self::THEME_BLUE,
										self::THEME_DARK_BLUE,
										self::THEME_GREY,
										self::THEME_YELLOW,
										self::THEME_ORANGE
									);
	}
	
	/* ** Accessors ** */
	
	/**
	 * Gets the class to use for the body tag to make this theme work
	 * @return String The name of this theme
	 */
	public function getBodyClass()
	{
		// The theme name and size to use
		return "{$this->getName()} {$this->m_size}";
	}
	
	/**
	 * Sets the theme colour and saves it to the DB for this user
	 * @param string $colour The colour (must be an existing theme colour)
	 * @param string $userId The user's id so it can save the colour
	 */
	public function setColour($colour)
	{
		// Check value is one of the potential colours
		if( $this->ColourExists($colour) ){
			$this->m_colour = $colour;
		}
	}
	
	/**
	 * Gets the name of this theme
	 * @return String The name of this theme
	 */
	public function getName()
	{
		// Theme name is the colour name and ' inv' if the colours are inverted
		return (isset($this->m_colourInverted))? "{$this->m_colour} {$this->m_colourInverted}" : $this->m_colour;
	}
	
	public function setSize($size)
	{
		$this->m_size = $size;
	}
	
	public function InvertColours()
	{
		// Switch between COLOUR_INVERTED_NAME and null
		$this->m_colourInverted = ( isset($this->m_colourInverted))? null : self::COLOUR_INVERTED_NAME;
	}
	
	/* ** Private methods ** */

	/**
	 * Are there theme settings for that colour option
	 * @return Bool 
	 * @param $colour String The colour name to check for
	 */
	private function colourExists($colour)
	{
		foreach ($this->m_colourOptions as $colourOption) {
			if($colour == $colourOption) {
				return true;
			}
		}
		return false;
	}
	
	/* ** Database operations ** */
	
	public function Save($user) 
	{
		$db = Database::getInstance();
		$sqlUser = "UPDATE user SET colour='{$this->getName()}', size='{$this->m_size}' WHERE ID={$user->getId()}";
		$result = $db->query($sqlUser);
	}
		
	/* ** Display opperations ** */
	
	public function HtmlColourOptions()
	{
		$htmlString = "<p>Your eFolio is $this->m_colour. Click another colour to change it.</p>" .
						'<ul id="colour_choice">';
		
		// Loop through all the colour options
		foreach ($this->m_colourOptions as $colourOption) {
			$htmlClass = $colourOption;
			if( $colourOption == $this->m_colour ) $htmlClass .= ' active';
			$htmlString .=	'<li class="' . $htmlClass . '"><a href="?colour='. $colourOption .'" alt="Colour choice '.str_replace('_' ,' ', $colourOption).'"><span>' . str_replace  ('_'  ,' ' , $colourOption) . '</span></a></li>';
		}
						
		$htmlString .=	'</ul>';
		return $htmlString;
	}
	
	/**
	 * Returns a HTML themed menu, if the menu is set
	 * @return 
	 * @param $menu Object
	 * @param $side Object
	 */
	public function HtmlMenu($menu, $side)
	{
		if( isset($menu) ) {
			return $this->MenuBox( $menu->Html(), $side );
		}
	}
	
	/* ** Theming methods ** */
	
	/**
	 * Turns a UL list into a set of tabs
	 * @return HTML for a row of tabs
	 * @param $contents String A UL list to add tab wrapper to
	 */
	public function ScrollingTabs($contents)
	{
		$htmlString = '<div id="nav-tabs" class="'. $this->getName() .'">' .
		'<p id="new-tab"><a href="tab?a=' . EventDispatcher::ACTION_NEW_TAB . '"><img alt="New tab" src="/_images/si/new-tab.gif"/></a></p>'.
		'<p id="prev-tab"><a id="prevBtn" class="prev" title="Previous tab"></a></p>' . $contents . '<p id="next-tab"><a id="nextBtn" class="next" title="Next tab"></a></p></div>';
		return $htmlString;
	}
	
	/**
	 * Produces HTML box wrapped round passed content
	 * @param String $content The content to wrap the box round
	 * @return String HTML of box with content
	 */
	public function Box($contents, $topSectionContent, $class=null, $id=null)
	{	
		// Put togther the HTML
		$htmlString = $this->BoxBegin($topSectionContent, $class, $id) .
						$contents .
						$this->BoxEnd();
		return $htmlString;
	}

	/**
	 * The start HTML for a box
	 * @param <type> $class
	 * @return <type>
	 */
	public function BoxBegin($topSectionContent, $class=null, $id=null)
	{
		// Work out class for box
		$boxClass = "box clear {$this->getName()}_border";
		if(isset($class)) {
			$boxClass .= " {$class}";
		}
		$divParamHtml =  ' class="' . $boxClass . '"';
		if(isset($id)) {
			$divParamHtml .= ' id="' . $id . '"';
		}

		return '<div' . $divParamHtml . '><div class="box-head">' .$topSectionContent. '</div><div class="box-content">';
	}

	public function BoxEnd()
	{
		return '</div></div>';
	}
	
	/**
	 * Box the content, if there is any content
	 * @return 
	 * @param $contents Object
	 * @param $class Object[optional]
	 */
	public function BoxIf($contents, $topSectionContent, $class=null)
	{
		if(isset($contents)) {
			return $this->Box($contents, $topSectionContent, $class);
		}
	}
	
	/**
	 * Get an icon image for this theme
	 * @param String $name The name of the icon
	 * @return Image An icon's image
	 */
	public function Icon($name, $title='') 
	{
		//Icons back to being one colour for now
		//return Image::CreateSystemImage('icons/'.  $this->getName() . '/'. $name .'.png', $title, 40);
		return Image::CreateSystemImage('icons/' . $name .'.gif', $title, 50, 50);
	}
	
	/**
	 * Produces HTML box wrapped round passed content
	 * @param String $content The content to wrap the box round
	 * @return String HTML of box with content
	 */
	public function MenuBox($contents, $side=null)
	{
		// What side to go to
		if( isset($side) ) {
			switch($side) {
				case Theme::LEFT:
					$htmlClass = ' left';
					break;
				case Theme::RIGHT:
					$htmlClass = ' right';
					break;
			}
		}
		else {
			$htmlClass = '';
		}
		
		$htmlString = '<div class="gb ' . $this->getName() . $htmlClass .'"><div class="grey-bi">' .
						$contents .
						'</div></div>';
		return $htmlString;
	}

	public function SolidBox($contents)
	{
		$html = '<div class="yb"><div class="bt"><div></div></div><div class="bi">' .
				$contents .
				'</div><div class="bb"><div></div></div></div>';
		return $html;
	}

}
