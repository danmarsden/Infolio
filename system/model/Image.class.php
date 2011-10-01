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
 * The Image Class
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: Image.class.php 757 2009-08-12 21:34:20Z richard $
 * @link       NA
 * @since      NA
*/

include_once('Asset.class.php');

class Image extends Asset
{
	protected $m_width;
	protected $m_height;
	protected $m_parameter;
	private $m_resizeWidth;
	private $m_resizeHeight;
	private $m_generateAndRedirectImage = true;
	
	const SIZE_ORIGINAL = 'size_original';
	const SIZE_TAB_ICON = 'size_tabicon';
	const SIZE_THUMBNAIL = 'size_thumbnail';
	const SIZE_SMALL_BOX = 'size_small_box';
	const SIZE_BOX = 'size_box';
	const SIZE_PHOTO_LOGIN = 'photo_login';

	// Compressed image quality n/100 (bigger = better)
	const IMAGE_QUALITY = 40;
	
	//TODO: Image is being rewritten to be a child of DatabaseObject.
	public function __construct($id=null)
	{
		$this->m_type = Asset::IMAGE;

		$m_resizeWidth = array();
		$m_resizeHeight = array();

		parent::__construct($id);
	}
	
	/* ** Accessor ** */

	public function getFilePath($size = self::SIZE_ORIGINAL)
	{
		$this->checkFilled();
		$path = $this->imageResizeForPreset($size);
		return $this->m_system_folder . $path;
	}

	public function getFullHref($size = self::SIZE_ORIGINAL, $absolute=false)
	{
		$this->checkFilled();

		$preHref = ($absolute) ?
						'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] :
						'';


		if($this->m_generateAndRedirectImage) {
			// Give URL of image resize script
			$id = (isset($this->m_id)) ? $this->m_id : 0;
			return $preHref . "/images/{$size}/{$id}/";
		}
		else {
			// Give direct URL to image
			return $preHref . $this->m_folder . $this->m_href;
		}
	}
	
	/**
	 * Tells you if this size setting is cropped or not
	 * @param <type> $value 
	 */
	public function getCrop($size)
	{
		switch($size) {
			case self::SIZE_THUMBNAIL:
			case self::SIZE_TAB_ICON:
			case self::SIZE_PHOTO_LOGIN:
			case self::SIZE_SMALL_BOX:
				return true;
				break;
			case self::SIZE_BOX:
			case self::SIZE_ORIGINAL:
				return false;
				break;
			default:
				throw new Exception("Techdis: '{$size}' is not a valid size type");
				break;
		}
	}
	
	public function setHeight($value){ $this->m_height = $value; }
	public function getBoundingHeight($size = self::SIZE_ORIGINAL)
	{
		$this->checkFilled();
		switch($size) {
			case self::SIZE_THUMBNAIL:
				return 100;
				break;
			case self::SIZE_TAB_ICON:
				return 55;
				break;
			case self::SIZE_BOX:
				return 350;
				break;
			case self::SIZE_SMALL_BOX:
				return 200;
				break;
			case self::SIZE_PHOTO_LOGIN:
				return 400;
				break;
			case self::SIZE_ORIGINAL:
				return $this->m_height;
				break;
			default:
				throw new Exception("Techdis: '{$size}' is not a valid size type");
				break;
		}
	}

	public function setWidth($value){ $this->m_width = $value; }
	public function getBoundingWidth($size = self::SIZE_ORIGINAL)
	{
		$this->checkFilled();
		switch($size) {
			case self::SIZE_THUMBNAIL:
				return 100;
				break;
			case self::SIZE_TAB_ICON:
				return 55;
				break;
			case self::SIZE_BOX:
				return 350;
				break;
			case self::SIZE_SMALL_BOX:
				return 200;
				break;
			case self::SIZE_PHOTO_LOGIN:
				return 700;
				break;
			case self::SIZE_ORIGINAL:
				return $this->m_width;
				break;
			default:
				throw new Exception("Techdis: '{$size}' is not a valid size type");
				break;
		}
	}
	
	public function setParameter($value){ $this->m_parameter = $value; }
	public function getParameter()
	{
		$this->checkFilled();
		return $this->m_parameter;
	}

	public function getHeight($size = Image::SIZE_ORIGINAL)
	{
		$this->calculateScaleDimensions($size);
		return $this->m_resizeHeight[$size];
	}

	public function getWidth($size = Image::SIZE_ORIGINAL)
	{
		$this->calculateScaleDimensions($size);
		return $this->m_resizeWidth[$size];
	}

	private function getAndSetSizes()
	{
		$src = $this->m_system_folder . $this->m_href;
		$cmd = "identify \"{$src}\"";

		exec($cmd, $output, $returnVal);
		preg_match("/[0-9]+x[0-9]+/", $output[0], $matches);
		$sizes = split  ('x', $matches[0], 2);

		$this->m_width = $sizes[0];
		$this->m_height = $sizes[1];
	}

	private function calculateScaleDimensions($size)
	{
		switch($size) {
			case self::SIZE_THUMBNAIL:
			case self::SIZE_TAB_ICON:
			case self::SIZE_PHOTO_LOGIN:
			case self::SIZE_SMALL_BOX:
				$this->m_resizeWidth[$size] = $this->getBoundingWidth($size);
				$this->m_resizeHeight[$size] = $this->getBoundingHeight($size);
				break;
			case self::SIZE_BOX:
				if (!isset($this->m_width) || $this->m_width <= 0) {
					$this->getAndSetSizes();
				}
				$ratio = $this->scaleRatio($size);
				$this->m_resizeWidth[$size] = round($this->m_width * $ratio);
				$this->m_resizeHeight[$size] = round($this->m_height * $ratio);
				break;
				default;
			case self::SIZE_ORIGINAL:
				if (!isset($this->m_width) || $this->m_width <= 0) {
					$this->getAndSetSizes();
				}
				$this->m_resizeWidth[$size] = $this->m_width;
				$this->m_resizeHeight[$size] = $this->m_height;
				break;
			default:
				throw new Exception("Techdis: '{$size}' is not a valid size type");
				break;
		}
	}

	/**
	 * Converts this image to the type provided and saves a new version of the image in that format to disk
	 * Must be saved to switch the reference to new file. Original file is not removed.
	 * @param <type> $fileExtension
	 */
	public function convertTo($fileExtension)
	{
		$filePath = $this->getFilePath();
		$cmd = 'convert ' . $filePath . ' ' .  $this->replaceFileExtension($this->getFilePath(), $fileExtension);
		exec($cmd, $output, $returnVal);

		$this->setHref( $this->replaceFileExtension($this->getHref(), $fileExtension) );
	}

	private function scaleRatio($size)
	{
		$xScaleRatio = $this->getBoundingWidth($size) / $this->m_width;
		$yScaleRatio = $this->getBoundingHeight($size) / $this->m_height;

		return ($xScaleRatio < $yScaleRatio) ?
					$xScaleRatio :
					$yScaleRatio;
	}

	/* ** Factory methods ** */

	/**
	 * Create a new image in the System folder
	 * @return 
	 * @param $href Object
	 * @param $title Object
	 * @param $width Object[optional]
	 * @param $height Object[optional]
	 */
	public static function CreateSystemImage($href, $title, $width=null, $height=null)
	{
		$image = new Image(null);
		$image->m_href = $href;
		$image->m_title = $title ;
		$image->m_width = $width;
		$image->m_height = $height;
		$image->m_folder = '/_images/si/';

		// System images will be linked to directly and don't need to be manipulated
		$image->m_generateAndRedirectImage = false;
		return $image;
	}
	
	public static function RetrieveByPage(Page $page, User $viewer, $limit=null)
	{
		$limitSql = ( isset($limit) )? " LIMIT {$limit}" : '';
		
		$db = Database::getInstance();
		$sql = "SELECT * FROM vassetswithcounts WHERE type='image' AND id IN (SELECT picture0 FROM block WHERE page_id={$page->getId()} AND user_id={$viewer->getId()}){$limitSql}";
		Debugger::debug("SQL: $sql", 'Image::RerieveByPage_1', Debugger::LEVEL_SQL);
		$result = $db->query($sql);
		
		return self::createArrayFromResultSet($result, $db);
	}

	public static function GetAudioPlaceHolder()
	{
		$image = new Image(null);
		$image->m_id = '-1';
		$image->m_href = 'audio-placeholder.png';
		$image->setFolders(null);
		$image->m_description = 'Audio';
		$image->m_width = 300;
		$image->m_height = 300;
		return $image;
	}

	public static function GetPlaceHolder()
	{
		$image = new Image(null);
		$image->m_href = 'placeholder.png';
		$image->setFolders(null);
		$image->m_description = 'No picture yet';
		$image->m_width = 300;
		$image->m_height = 300;
		return $image;
	}

	/* ** Display opperations ** */

	public function Html($size = self::SIZE_ORIGINAL, $extraClass=null, $id=null)
	{
		//$imageInfo = $this->imageResizeForPreset($size);

		$html = '<img src="' . $this->getFullHref($size) . '"' .
			' width="' . $this->getWidth($size) .'"' .
			' height="' . $this->getHeight($size) .'"';

		// Set classes
		$classes = $this->m_classes;
		if( isset($extraClass) ){
			if( !is_array($classes) ) {
				$classes = array();
			}
			$classes[] = $extraClass;
		}
		if( isset($classes) && count($classes) > 0 ) {
			$classList = implode(' ', $classes);
			$html .= ' class="' . $classList .'"';
		}

		// Set id
		if( isset($id) ) {
			$html .= ' id="' . $id . '"';
		}

		$html .= ' alt="' . $this->m_title . '" ' .
				' title="' . $this->m_title . '" ' .
				$this->m_parameter .
				' />';

		// Add link stuff
		if(isset($this->m_editLink)) {
			//Edit icon
			$editIcon = $this->m_editLink->getImage();
			$editIcon->addClass('icon');

			// Adding link HTML
			$html = '<a href="' . $this->m_editLink->getHref() . '"' .
					$this->m_editLink->HtmlPropertyString() .
					'>' . $editIcon->Html() .
					$html . '</a>';
		}

		return $html;
	}
	
	/**
	 * Resizes this image
	 * @return String The href to the resized image
	 * @param $sizeName The predefined name for this size of image
	 * @param $newWidth Int
	 * @param $newHeight Int
	 */
	private function imageResize($sizeName, $newWidth, $newHeight, $path=null, $crop=true)
	{
		// Work out destination path
		$dstPath = (isset($path)) ?
					$path . $sizeName . '/' . $this->m_href :
					$this->m_system_folder . $sizeName . '/' . $this->m_href;
		
		// Check file exists
		if( !file_exists($dstPath) ) {
			// Create resized image (if it doesn't exist)
			Debugger::debug("Creating {$sizeName} version of {$this->m_href}", "Image({$this->m_id})::imageResize_1", Debugger::LEVEL_INFO);
			
			try {
				// Work out source path
				$srcPath = (isset($path)) ? 
							$path . $this->m_href :
							$this->m_system_folder . $this->m_href;

				// Todo: stuf for none gif/png/jpg
				//$this->getFileExtension();

				// Cropping
				$cropParams = ($crop) ?
									IM_NO_BOUNDS . " -gravity center -extent {$newWidth}x{$newHeight}" :
									'';
				
				// Image Magick command
				// the '^' option requires at least version 6.3.8-2 of ImageMagick...
				$cmd = "convert \"{$srcPath}\" -resize {$newWidth}x{$newHeight}{$cropParams} -quality " .self::IMAGE_QUALITY. " \"{$dstPath}\"";

				exec($cmd, $output, $returnVal);

			}
			catch (Exception $e) {
				throw new Exception("Techdis: GD image resizing error with '{$dstPath}'\n\n----\n{$e->getMessage()}");
			}

		}

		$href = $sizeName . '/' . $this->m_href;
		return $href;
	}

	/**
	 * Rezise this image to the specified preset
	 * @param <type> $size
	 * @return <type>
	 */
	public function imageResizeForPreset($size, $dstPath=null)
	{
		$path = ($size != self::SIZE_ORIGINAL) ?
			$this->imageResize($size, $this->getBoundingWidth($size), $this->getBoundingHeight($size), $dstPath, $this->getCrop($size)) :
			$this->m_href;

		return $path;
	}
}