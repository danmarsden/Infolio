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
 * The Links Class
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: Link.class.php 826 2009-12-14 12:05:32Z richard $
*/

class Link
{
	/* ** Protected member data ** */
	
	private $m_active;
	private $m_href;
	private $m_htmlProperties;
	private $m_image;
	private $m_imageSize;
	private $m_sectionName;
	private $m_text;
	private $m_title;
	
	/**
	 * Constructor
	 * @param String $text
	 * @param String $href
	 * @param String $htmlClass
	 */
	private function __construct($text, $href, $htmlProperties=null)
	{
		$this->m_text = $text;
		$this->m_href = $href;
		$this->m_htmlProperties = $htmlProperties;
		
		$this->m_active = false;
	}
	
	/* ** Accessors ** */
	
	public function addHtmlProperty($propName, $propValue, $replaceExisting = false)
	{
		// Check array exists and set it up if not
		if(!isset($this->m_htmlProperties)) {
			$this->m_htmlProperties = array();
		}

		// Combine with previous value if required and it exists
		if (!$replaceExisting && isset($this->m_htmlProperties[$propName])){
			$propValue .= ' ' . $this->m_htmlProperties[$propName];
		}
		
		// Add property
		$this->m_htmlProperties[$propName] = $propValue;
	}
	
	/**
	 * An active link is where we are on that page or in it's section
	 * @return 
	 */
	public function checkActive($sectionName)
	{
		//echo $sectionName . ' == ' . $this->m_sectionName . '<br />';
		if(strtolower($this->m_sectionName) == strtolower($sectionName)) {
			$this->m_active = true;
		}
		else {
			$this->m_active = false;
		}
	}
	
	public function isActive()
	{
		return $this->m_active;
	}
	
	public function setActive($value)
	{
		$this->m_active = ($value == true);
	}
	
	/**
	 * Returns a HREF with &s encoded so it can be passed as a variable
	 */
	public function getParamEncodedHref()
	{
		$href = str_replace  ('&amp;', '||' ,$this->m_href);
		$href = str_replace  ('&', '||' ,$href);
		return $href;
	}
	
	public function getHref()
	{
		return $this->m_href;
	}

	public function getImage()
	{
		return $this->m_image;
	}

	public function setImage($value)
	{
		$this->m_image = $value;
	}

	public function setText($value)
	{
		$this->m_text = $value;
	}
	
	public function setTitle($value)
	{
		$this->m_title = $value;
	}

	/* ** Factory methods ** */

	public static function CreateIconLink($text, $href, Image $icon, $htmlProperties=null, $iconSize=Image::SIZE_ORIGINAL)
	{
		$link = new Link($text, $href, $htmlProperties);
		$link->m_image = $icon;
		$link->m_imageSize = $iconSize;
		$link->addHtmlProperty('class', 'icon');
		return $link;
	}

	public static function CreateImageLink(Asset $image, $href, $imageSize=Image::SIZE_ORIGINAL, $htmlProperies=null)
	{
		$link = new Link('', $href, $htmlProperies);
		$link->m_image = $image;
		$link->m_imageSize = $imageSize;
		return $link;
	}

	public static function CreateLink($text, $href, $htmlProperties=null)
	{
		$link = new Link($text, $href, $htmlProperties);
		return $link;
	}

	public static function CreateSectionIconLink($text, $href, $sectionName, $icon, $iconSize=Image::SIZE_ORIGINAL, $htmlProperties=null)
	{
		$link = new Link($text, $href, $htmlProperties);
		$link->m_image = $icon;
		$link->m_imageSize = $iconSize;
		$link->m_sectionName = $sectionName;
		return $link;
	}

	/* ** Display methods ** */
	
	/**
	 * Makes the HTML for this link
	 * @return String The HTML for this link
	 */
	public function Html()
	{
		$html = "<a href=\"{$this->m_href}\"";
		if(isset($this->m_title)) {
			$html .= ' title="' . $this->m_title . '"';
		}
		$html .= $this->HtmlPropertyString();
		$html .= ">";
		
		// Add image (if there is one)
		if( isset($this->m_image) ) {
			$html .= $this->m_image->Html($this->m_imageSize);
		}

		// Add text (if there is any)
		if( isset($this->m_text) ) {
			$html .= ' ' . $this->m_text;
		}
		
		$html .= '</a>';

		return $html;
	}

	public function HtmlPropertyString()
	{
		$html = '';
		if(is_array($this->m_htmlProperties)) {
			foreach($this->m_htmlProperties as $htmlProperty=>$htmlValue) {
				$html .= " {$htmlProperty}=\"{$htmlValue}\"";
			}
		}
		return $html;
	}
}