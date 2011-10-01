<?php
/**
 * The Video Class
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Elvir LEonard	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: Image.class.php 78 2008-07-31 15:47:15Z Elvir $
 * @link       NA
 * @since      NA
*/

include_once('Asset.class.php');

class Video extends Asset
{
	protected $m_width;
	protected $m_height;

	// Video View Type Constants
	const VIDEO_FLV_STANDARD = 'flv';
	const SIZE_FRAMEGRAB_X = 120;
	const SIZE_FRAMEGRAB_Y = 86;
	const PLAYER_WIDTH = 350;
	const PLAYER_HEIGHT = 262;

	const AUDIO_CODEC = 'libmp3lame';

	public function __construct($id=null)
	{
		$this->m_type = Asset::VIDEO;
		parent::__construct($id);
	}

	/* ** Accessor ** */

	/**
	 * Returns a simple object of this object. Is used for JSON making.
	 */
	protected function getAsObject($size=Image::SIZE_THUMBNAIL)
	{
		// Choose file type based on size
		switch($size) {
			case Image::SIZE_THUMBNAIL:
				$fileName = self::replaceFileExtension($this->m_href, 'gif');
				break;
			default:
				$fileName = self::replaceFileExtension($this->m_href, 'flv');
				break;
		}

		$data = (object) array(
			'id' => $this->getId(),
			'owner' => $this->getCreatedBy()->getFullName(),
			'name' => $this->getTitle(),
			'description' => $this->getDescription(),
			'href' => "/videos/{$size}/{$this->getId()}/",
			'date' => $this->getUpdatedTime(),
			'tags' => $this->getTags(),
			'use_count' => $this->m_timesUsed,
			'view_public' => $this->m_public
		);

		// Tag list
		$tags = $this->getTagList();
		if(isset($tags)) {
			$data->tags = $tags;
		}

		return $data;
	}

	public function getFilePath($size = Image::SIZE_ORIGINAL)
	{
		switch($size)
		{
			case Image::SIZE_THUMBNAIL:
			case Image::SIZE_TAB_ICON:
				return $this->generateThumbnail();
				break;
			default:
				return $this->generateFlv();
				break;
		}
	}

	public function getFullHref($size = null, $absolute = false)
	{
		$this->checkFilled();

		$preHref = ($absolute) ?
						'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] :
						'';

		return $preHref . $this->m_folder . 'flv/' . self::replaceFileExtension($this->m_href, 'flv');
	}

	public function setHeight($value){ $this->m_height = $value; }
	public function getHeight($size){
		switch($size) {
			case Image::SIZE_THUMBNAIL:
				return 100;
				break;
			case Image::SIZE_TAB_ICON:
				return 55;
				break;
			default:
				return $this->m_height;
				break;
		}
	}

	public function setWidth($value){ $this->m_width = $value; }
	public function getWidth($size)
	{
		switch($size) {
			case Image::SIZE_THUMBNAIL:
				return 100;
				break;
			case Image::SIZE_TAB_ICON:
				return 55;
				break;
			default:
				return $this->m_width;
				break;
		}
	}
	
	public function setParameter($value){ $this->$m_parameter = $value; }
	public function getParameter($value){ return $this->$m_parameter; }

	/* ** Display opperations ** */
	
	public function Html($size = Image::SIZE_BOX)
	{
		switch($size)
		{
			case Image::SIZE_THUMBNAIL:
			case Image::SIZE_TAB_ICON:
				//$thumbnailPath = $this->m_folder . Image::SIZE_THUMBNAIL . '/' . self::replaceFileExtension($this->m_href, 'gif');
				$html = "<img src=\"/videos/{$size}/{$this->getId()}/\" width=\"{$this->getWidth($size)}\" height=\"{$this->getHeight($size)}\" alt=\"Picture of video {$this->m_title}\" />";
				break;
			default:
				$editParams = (isset($this->m_editLink)) ?
						'&editIcon=' . $this->m_editLink->getImage()->getFullHref() . '&editLink=' . $this->m_editLink->getParamEncodedHref() :
						'';

				$videoPath = '/_flash/BigVideoPlayer.swf?fvMoviePath=' . $this->m_folder . 'flv/' . self::replaceFileExtension($this->m_href, 'flv') . $editParams;
				$html = '<object type="application/x-shockwave-flash" data="' . $videoPath . '" width="' .self::PLAYER_WIDTH. '" height="' .self::PLAYER_HEIGHT. '" wmode="transparent">' .
					'<param name="movie" value="' . $videoPath . '" />' .
					'<img src="needflash.gif" width="' .self::PLAYER_WIDTH. '" height="' .self::PLAYER_HEIGHT. '" alt="Need flash" />' .
					'</object>';
				break;
		}
		return $html;
	}

	protected function dbCreate()
	{
		$this->generateFlv();
		$this->generateThumbnail();
		parent::dbCreate();
	}

	// Private methods

	/**
	 * Convert video to FLV video
	 */
	private function generateFlv()
	{
		$originalFilePath = $this->m_system_folder . $this->m_href;
		$flvFileName = self::replaceFileExtension($this->m_href, 'flv');
		$flvFilePath = $this->m_system_folder . self::VIDEO_FLV_STANDARD . '/' . $flvFileName;
		
		// Only make flv if not already generated or was made badly
		if(!file_exists($flvFilePath) || filesize($flvFilePath) <= 0)
		{
			// Check original file exists
			if(file_exists($originalFilePath) && filesize($originalFilePath) > 0) {
				$cmd = 'ffmpeg -i "' . $originalFilePath . '" -qscale 7 -s ' .self::PLAYER_WIDTH. 'x' .self::PLAYER_HEIGHT. ' -f flv -ar 22050 -acodec ' .self::AUDIO_CODEC. ' -y "' . $flvFilePath . '"';

				Debugger::debug($cmd, 'Video::generateFlv_0');
				exec($cmd, $output, $returnVal);
				Debugger::debug(print_r($output, true) . ' = ' . $returnVal, 'Video::generateFlv_1');
			}
			else {
				throw new Exception("Video was not uploaded");
			}

			// Check all went well
			if(!file_exists($flvFilePath) || filesize($flvFilePath) <= 0) {
				throw new Exception("Could not convert the video");
			}
		}

		return $flvFilePath;
	}

	/**
	 * Generate an animated thumbnail of this video
	 */
	public function generateThumbnail($size=Array('x' => self::SIZE_FRAMEGRAB_X, 'y' => self::SIZE_FRAMEGRAB_Y), $path=null)
	{
		// Set default folder
		// Bulk upload stuff in different folder.
		if(is_null($path)) {
			$path = $this->m_system_folder;
		}

		$originalFilePath = $path . $this->m_href;
		$fileName = $this->getFileNamePart();
		$gifFileName = $path . Image::SIZE_THUMBNAIL . '/' . $fileName . '.' . 'gif';
		$fileName = $path . Image::SIZE_THUMBNAIL . '/' . $fileName;

		// Only make thumbnail if not already generated or was made badly
		if(!file_exists($gifFileName) || filesize($gifFileName) <= 0) {

			// Check file was uploaded okay
			if(file_exists($originalFilePath) && filesize($originalFilePath) > 0){
				$allOkay = true;
				for($i=0; $i<4; $i++) {
					if( ! $this->generateFrameStill($originalFilePath,
													$fileName,
													$size, $i) ) {
						$allOkay = false;
						break;
					}
				}

				// If all okay
				if($allOkay) {
					//$length = $this->getLength();

					$cmd = "convert -delay 500  -loop 0 {$fileName}___*.gif   {$fileName}.gif";
					exec($cmd, $output, $returnVal);

					// Delete the source frames
					for($i=0; $i<4; $i++) {
						unlink("{$fileName}___{$i}.gif");
					}

					/*if($returnVal != 0) {
						throw new Exception("Can't make thumbnail.");
					}*/

					// Check thumbnail was generated.
					if(!file_exists($gifFileName) || filesize($gifFileName) <= 0) {
						throw new Exception("Can't make '{$gifFileName}' thumbnail..");
					}
				}
				else {
					// May cause issue for short videos
					throw new Exception("Can't make thumbnail...");
				}
			}
			else {
				throw new Exception ("Video was not uploaded");
			}
		}

		return $gifFileName;

		
	}
	
	private function generateFrameStill($srcFile, $fileName, $size, $place, $secondGap = 3)
	{
		$timeIndex = $place * $secondGap;
		$cmd = 'ffmpeg -i "' . $srcFile . '" -an -qscale 3 -vframes 1 -s '.$size['x'].'x'.$size['y'].' -t 0.001 -ss ' . $timeIndex . ' -f mjpeg -y "' . $fileName . '___' . $place .  '.gif"';
		Debugger::debug($cmd, 'Video::generateThumbnail_0');
		exec($cmd, $output, $returnVal);

		//Debugger::debug(print_r($output, true) . ' = ' . $returnVal, 'Video::generateThumbnail_1');

		return ($returnVal == 0);
	}
}