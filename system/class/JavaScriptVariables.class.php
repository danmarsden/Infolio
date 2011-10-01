<?php

/**
 * A class to store Java Variable pairs to be passed from server to clientside
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: JavaScriptVariables.class.php 288 2008-12-06 18:11:33Z richard $
 * @link       NA
 * @since      NA
*/

class JavaScriptVariables
{
	private $m_vars;
	
	public function __construct()
	{
		$this->m_vars = array();	
	}
	
	public function addVariable($name, $value)
	{
		$this->m_vars[$name] = $value;
	}
	
	/**
	 * Creates an HTML block with all the defined JS variables in it
	 * @return 
	 */
	public function HtmlJavaScriptBlock()
	{
		$html = '<script type="text/javascript">';
		
		// Add all variables
		foreach($this->m_vars as $varKey => $varValue) {
			$html .= "var {$varKey}={$varValue};";
		}
		$html .= '</script>';
		return $html;
	}
}