<?php
/**
 * The Audio Class
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Elvir LEonard	
 * @copyright  2008 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id$
 * @link       NA
 * @since      NA
 */

//include_once('model/Asset.class.php');


class Audio extends Asset
{
	protected $m_width;
	protected $m_height;
	private $m_previewImage;

	public function __construct($id=null)
	{
		$this->m_type = Asset::AUDIO;
		$this->m_previewImage = Image::GetAudioPlaceHolder();
		parent::__construct($id);
	}

	
	
	/* ** Accessor ** */
	public function setHeight($value){ $this->m_height = $value; }
	public function getHeight(){ return $this->m_height; }

	public function setWidth($value){ $this->m_width = $value; }
	public function getWidth(){ return $this->m_width; }
	
	public function setParameter($value){ $this->$m_parameter = $value; }
	public function getParameter(){ return $this->$m_parameter; }

	public function getFilePath()
	{
		$this->checkFilled();
		return $this->m_system_folder . $this->m_href;
	}
	
	/* ** Display opperations ** */
	
	public function Html($size = Image::SIZE_BOX)
	{
		$width = 50;
		$height = 50;

		switch($size) {
			case Image::SIZE_THUMBNAIL:
				$html = $this->m_previewImage->Html($size);
				break;
			default:
				$swfFile = "/_flash/listen.swf?mname=" . $this->m_folder . str_replace(".mp3","", $this->m_href);
				$html = '<object type="application/x-shockwave-flash" data="' . $swfFile . '" width="' . $width . '" height="' .$height. '" wmode="transparent">' .
						'<param name="movie" value="' . $swfFile . '" />' .
						'<img src="needflash.gif" width="' .$width. '" height="' .$height. '" alt="Need flash" />' .
						'</object>';
				break;
		}
		return $html;
	}
}