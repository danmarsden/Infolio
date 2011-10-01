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
 * A class to store Java Variable pairs to be passed from server to clientside
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: JavaScriptVariables.class.php 288 2008-12-06 18:11:33Z richard $
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