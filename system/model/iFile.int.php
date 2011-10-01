<?php

/**
 * The iFile interface
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: iFile.int.php 487 2009-04-21 13:31:34Z richard $
 * @link       NA
 * @since      NA
*/

/**
 * An interface for objects that are also files.
 */
interface iFile
{
	public function getFileExtension();
	public function setHref($value);
	public function getSystemFolder();
	public function getType();
}