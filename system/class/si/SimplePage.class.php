<?php

/**
 * The SimplePage Class
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: SimplePage.class.php 757 2009-08-12 21:34:20Z richard $
 * @link       NA
 * @since      NA
*/

include_once('class/JavaScriptVariables.class.php');

/**
 * An HTML page and any related info about it.
 */
class SimplePage
{
	protected $m_title;
	protected $m_name;
	protected $m_href;
	protected $m_sortMethod;
	protected $m_mode;
	private $m_jsVariables;
	protected $m_warningMessages;
	
	// Constants
	const MODE_DELETE = 'delete';
	const MODE_EDIT = 'edit';
	const MODE_SHOW = 'show';
	
	public function __construct($title = 'Untitled page') {
		$this->m_title = $title;
		$this->m_mode = SimplePage::MODE_SHOW;
		$this->m_href = $_SERVER['REQUEST_URI'];
		$this->m_jsVariables = new JavaScriptVariables();

        $gname = Safe::get('name');
		if(isset($gname)) {
			$this->m_name = $gname;
		}
        $gsort = Safe::get('sort');
		if(isset($gsort)) {
			$this->m_sort = $gsort;
		}
	}
	
	/* ** Accessors ** */

	public function addWarningMessage($warning)
	{
		$this->m_warningMessages .= "<p>{$warning}</p>";
	}

	public function setHrefFromReferrer()
	{
		if(isset($_SERVER["HTTP_REFERER"])) {
			$this->m_href = $_SERVER["HTTP_REFERER"];
		}
	}

	public function getJavaScriptVariables()
	{
		return $this->m_jsVariables;
	}

	/**
	 * Get's the page's title
	 * @return String
	 */
	public function getTitle()
	{
		return $this->m_title;
	}

	/**
	 * Sets the page's title
	 * @param $value String The page's title
	 */
	public function setTitle($value)
	{
		$this->m_title = $value;
	}
	
	/**
	 * A page name is unique for a User.
	 * Only special pages (like Tab summary pages) have names
	 * @return 
	 */
	public function getName()
	{
		return $this->m_name;
	}
	
	public function setName($value)
	{
		$this->m_name = $value;
	}

	/**
	 * Gets the section this page belongs to
	 * @return 
	 */
	public function getSectionName()
	{
		return 'section-' . Safe::UrlQueryVarOutput($this->m_name);
	}
	
	/**
	 * Get the current mode of the page
	 * @return 
	 */
	public function getMode()
	{
		return $this->m_mode;
	}
	
	/**
	 * Set the mode for the page
	 * @return 
	 * @param $mode Object MODE_SHOW or MODE_EDIT
	 */
	public function setMode($mode)
	{
		switch($mode) {
			// All valid modes will be set
			case SimplePage::MODE_DELETE:
			case SimplePage::MODE_EDIT:
			case SimplePage::MODE_SHOW:
				
				$this->m_mode = $mode;
				break;
			// Otherwise throw an exception
			default:
				throw new Exception("TechDis: Invalid page mode set ({$mode})");
		}
	}

	/* ** Public methods ** */
	
	/**
	 * Produces a path to this page with an extra or replaced querystring parameter 
	 * @return String A path
	 * @param $key String
	 * @param $value String
	 * @param $hashAnchorName String A page anchor link
	 */
	public function PathWithQueryString($keyValues=null, $htmlLinkSafe=true, $hashAnchorName = null)
	{
		$amp = ($htmlLinkSafe==true) ? '&amp;' : '&';
		
		// Get rid of Query vars from query string
		$parts = explode('?', $this->m_href);
		$newUrl = $parts[0] . '?';
		
		// Check we have an array to work with
		if(!is_array($keyValues)){
			$keyValues = array();
		}
		
		// Add page name value (if required)
		/* Name now stored in URL
		 * if(isset($this->m_name)) {
			$keyValues['name'] = $this->m_name;
		}*/
		if(isset($this->m_sortMethod)) {
			$keyValues['sort'] = $this->m_sortMethod;
		}
		
		// Construct new query string
		$queryArray = array();
		
		foreach($keyValues as $getKey=>$getValue) {
			$queryArray[] = "{$getKey}={$getValue}";
		}
		$newUrl .= implode($amp, $queryArray);

		// Add hash anchor name if there is one
		if(isset($hashAnchorName)) {
			$newUrl .= '#' . $hashAnchorName;
		}

		return $newUrl;
	}
	
	/* ** Display methods ** */
	
	/**
	 * Creates HTML for the head section of the page, not including head tag
	 * @return String HTML for the head section
	 */
	public function HtmlHead()
	{
		$htmlString = "<title>{$this->m_title}</title>";
		return $htmlString;
	}
	
	public function HtmlTitle($headerLevel = 1)
	{
		return "<h{$headerLevel}>{$this->m_title}</h{$headerLevel}>";
	}
}