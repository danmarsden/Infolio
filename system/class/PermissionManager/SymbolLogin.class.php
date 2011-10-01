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
 * Symbol Login
 * 

 * 
 * This class is responsible to handle login function in backoffice
 * 
 * @author     	Richard Garside [www.richardsprojects.co.uk]
 * @copyright  	2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    	$Id: SymbolLogin.class.php 799 2009-09-01 21:19:41Z richard $
 * @link       	NA
 * @since      	NA
 * 
 */

include_once('model/Image.class.php');

class SymbolLogin
{
	private $m_shapeIndex;
	private $m_shapeImage;
	private $m_photoIndex;
	private $m_photoImage;
	private $m_enabled;

	const NUM_SHAPES = 6;
	const NUM_PHOTOS = 15;

	// Class data
	private static $m_shapeNumbersArray;
	private static $m_photoNumbersArray;

	public function SymbolLogin($shape, $photo, $enabled=false)
	{
		$this->setShape($shape);
		$this->setPhoto($photo);
		$this->m_enabled = $enabled;
	}

	public function getAllAsHashArray()
	{
		$hashArray = array();
		$hashArray['switch_shape'] = $this->m_shapeIndex;
		$hashArray['switch_photo'] = $this->m_photoIndex;
		$hashArray['switch_enabled'] = $this->m_enabled;
		return $hashArray;
	}

	public function isEnabled()
	{
		return $this->m_enabled;
	}

	public function setEnabled($value)
	{
		$this->m_enabled = $value;
	}

	public function getShapePhotoNumber($userId)
	{
		// Work out
		Debugger::debug($this->getShapeNumber() . ' * ' . $this->getPhotoNumber() .' * '. ($userId % 999), 'SymbolLogin::getShapePhotoNumber');

		return $this->getShapeNumber() *
				$this->getPhotoNumber() *
				($userId % 999);
	}

	private function getShapeNumber()
	{
		if(!isset(self::$m_shapeNumbersArray)) {
			self::setupNumbers();
		}
		return self::$m_shapeNumbersArray[$this->m_shapeIndex];
	}

	private function getPhotoNumber()
	{
		if(!isset(self::$m_photoNumbersArray)) {
			self::setupNumbers();
		}
		return self::$m_photoNumbersArray[$this->m_photoIndex];
	}

	/**
	 * Sets the shape number and the appropriate picture for that shape
	 * @param <type> $value The numeric value for the shape
	 */
	public function setShape($value)
	{
		// Check value is okay
		if(!is_numeric($value) || $value < -1 || $value > self::NUM_SHAPES)  {
			throw new Exception("Bad shape value ($value) for symbol login");
		}

		// -1 value needs placeholder, otherwise get correct shape image
		if($value == -1) {
			$this->m_shapeImage = Image::GetPlaceHolder();
			$this->m_shapeImage->setWidth(100);
			$this->m_shapeImage->setHeight(100);
		}
		else {
			$this->m_shapeImage = Image::CreateSystemImage('symbol_password/shape_' . ($value + 1) . '.gif', 'Shape', 100, 100);
		}

		$this->m_shapeIndex = $value;
	}
	
	public function setPhoto($value)
	{
		// Check value is okay
		if(!is_numeric($value) || $value < -1 || $value > self::NUM_PHOTOS)  {
			throw new Exception("Bad photo value ($value) for symbol login");
		}

		// -1 value needs placeholder, otherwise get correct photo image
		if($value == -1) {
			$this->m_photoImage = Image::GetPlaceHolder();
			$this->m_photoImage->setWidth(100);
			$this->m_photoImage->setHeight(100);
		}
		else {
			$this->m_photoImage = Image::CreateSystemImage('symbol_password/photo_' . ($value + 1) . '.jpg', 'Photo', 100, 100);
		}		

		$this->m_photoIndex = $value;
	}

	public function HtmlAdminShow()
	{
		$html = '<p>' . $this->m_shapeImage->Html(Image::SIZE_ORIGINAL, 'edit', 'passShape') .
				$this->m_photoImage->Html(Image::SIZE_ORIGINAL, 'edit', 'passPhoto') . '</p>';
		return $html;
	}

	public static function JsonPhotoImages()
	{
		$photos = array();
		for($i=1; $i<=16; $i++) {
			$photos[] = Image::CreateSystemImage('symbol_password/photo_' . $i . '.jpg', 'Shape', 100, 100);
		}

		return DatabaseObject::CreateJsonString($photos, true);
	}

	public static function JsonShapeImages()
	{
		$shapes = array();
		for($i=0; $i<6; $i++) {
			$shapes[] = Image::CreateSystemImage('symbol_password/shape_' . ($i+1) . '.gif', 'Shape', 100, 100);
		}

		return DatabaseObject::CreateJsonString($shapes, true);
	}

	private static function setupNumbers()
	{
		self::$m_shapeNumbersArray = array(
			453463,
			954877,
			654967,
			951433,
			603217,
			450887
		);

		self::$m_photoNumbersArray = array(
			450669,
			452579,
			450953,
			952867,
			451837,
			650983,
			454993,
			952621,
			600669,
			600829,
			300693,
			600011,
			302119,
			951073,
			385832
		);
	}
}