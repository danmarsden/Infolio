<?php
/**
 *
 * The SimpleBlock class
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2009 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: SimpleBlock.class.php 809 2009-11-03 08:45:09Z richard $
 * @link       NA
 * @since      NA
*/


/**
 * Class SimpleBlock
 * A simple class to store some text and a header
 */
class SimpleBlock
{
	private $m_header;
	private $m_text;

	public function __construct($header, $text)
	{
		$this->m_header = $header;
		$this->m_text = $text;
	}

	public function getHeader()
	{
		return $this->m_header;
	}

	public function getText()
	{
		return $this->m_text;
	}
}