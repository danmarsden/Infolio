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
 * The PageBlockLayout Class
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]	
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: PageBlockLayout.class.php 852 2010-01-18 09:22:27Z richard $
 * @link       NA
 * @since      NA
*/

include_once('Image.class.php');

/**
 * A class that stores the layout for a Block that is shown on a page.
 */
class PageBlockLayout
{
	private $m_id;
	private $m_description;
	private $m_templateHtml;
	
	/**
	 * Creates a new PageBlockLayout
	 * @return 
	 * @param $templateHtml String The HTML to use for this template. Templates must have <image#> and <words#> markers in them.
	 */
	public function __construct($id, $templateHtml, $description)
	{
		$this->m_id = $id;
		$this->m_description = $description;
		$this->m_templateHtml = $templateHtml;
	}
	
	/* ** Accessors ** */

	public function getDescription()
	{
		return $this->m_description;
	}

	public function getId() { return $this->m_id; }

	public function getLayoutClass()
	{
		return 'bl' . $this->getId();
	}
	
	/* ** Factory methods ** */
	
	/**
	 * Creates an array of PageBlockLayout objects from the layouts in the DB
	 * @return PageBlockLayout-Array
	 */
	public static function GetLayouts()
	{
		$db = Database::getInstance();
		$sql = "SELECT * FROM block_layout";
		$result = $db->query($sql);
		
		$layout = array();
		while ($row = $db->fetchArray($result, MYSQL_ASSOC)) {
			$layout[$row['id']] = new PageBlockLayout($row['id'], $row['html'], $row['description']);
		}
		return $layout;
	}
	
	/* ** Display methods ** */
	
	/**
	 * Create the HTML content for the inside of the box.
	 * @param <type> $blockContent 
	 */
	private function htmlInsideContent(PageBlock $blockContent, Theme $theme)
	{
		$html = $this->m_templateHtml;

		

		// Add word blocks to template
		$wordBlocks = $blockContent->getWordBlocks();
		$numWordBlocks = count($wordBlocks);
		for($i=0; $i<$numWordBlocks; $i++) {
			// Add carriage return to word block
			$wordBlock = str_replace("\n", '<br />', $wordBlocks[$i]);

			// Place word block in template
			$html = str_replace( '<words' . $i . '>', $wordBlock, $html);
		}

		// Add pictures to template
		$pictures = $blockContent->getPictures();
		$html = $this->putImagesInTemplate($html, $pictures, $theme);

                $blockcontentid = $blockContent->getId();

		// Add play button
                $html .= "
                    <script type=\"text/javascript\" src=\"/_scripts/swfobject.js\"></script>
                    <script type=\"text/javascript\">
                        var params = {};
                        params.scale = \"noscale\";
                        params.wmode = \"opaque\";
                        var attributes = {};
                        swfobject.embedSWF('/_flash/Play.swf?snd=/system/get.sound.php?blockid=$blockcontentid', 'playbutton', '50', '50', '9', false, false, params, attributes);
                    </script>
                    <div id=\"playbutton\">
                          <a href=\"http://www.adobe.com/go/getflashplayer\">
                            <img alt=\"Get Adobe Flash player\" src=\"http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif\"/>
                          </a>
                    </div>
";
		return $html;
	}

	public function Html(PageBlock $blockContent, Theme $theme)
	{
		$html = $this->htmlInsideContent($blockContent, $theme);
		return $theme->Box($html, '<h2>' . $blockContent->getTitle() . '</h2>', $this->getLayoutClass(), 'b'.$blockContent->getId());
	}
	
	/**
	 * Render the passed Block content using this template and add edit options
	 * @return String The HTML to use for the block
	 * @param $blockContent PageBlock
	 * @param $page Page
	 */
	public function HtmlWithEditOptions(PageBlock $blockContent, Page $page, $theme, $blockNumber, $blockPosition = PageBlock::MIDDLE)
	{
		// Get normal block HTML
		$html = $this->htmlInsideContent($blockContent, $theme);
		
		// Create an edit menu for this block
		$editMenu = $blockContent->EditMenu($page, $blockNumber, $theme);
		
		// Edit options - up and down links
		$upLink = Link::CreateIconLink('Up', $page->PathWithQueryString( array('blockup'=>$blockContent->getId()), true ), $theme->Icon('up-arrow'), array('class'=>'btnUp bl' . $blockContent->getId(), 'title'=>"Move block {$blockNumber} up"));
		$downLink = Link::CreateIconLink('Down', $page->PathWithQueryString( array('blockdown'=>$blockContent->getId()), true ), $theme->Icon('down-arrow'), array('class'=>'btnDown bl' . $blockContent->getId(), 'title'=>"Move block {$blockNumber} down"));

		// Work out which links to show based on position of block (ie. top can't go more up)
		// Elements hidden by CSS
		switch($blockPosition) {
			case PageBlock::BOTTOM:
				$downLink->addHtmlProperty('class', 'hidden');
				break;
			case PageBlock::TOP:
				$upLink->addHtmlProperty('class', 'hidden');
				break;
			case PageBlock::ALONE:
				// No links for a block that is by itself
				$downLink->addHtmlProperty('class', 'hidden');
				$upLink->addHtmlProperty('class', 'hidden');
				break;
			//case PageBlock::MIDDLE:
		}
		$editMenu->addLink($downLink);
		$editMenu->addLink($upLink);


		$topContent = '<h2>' . $blockContent->getTitle() . '</h2>';
		
		if( isset($editMenu) ) {
			$topContent = $topContent . '<div class="box-tools">' . $editMenu->Html() . '</div>';
		}

			return $theme->Box($html, $topContent, $this->getLayoutClass(), 'b'.$blockContent->getId());
	}
	
	public function HtmlEdit(PageBlock $blockContent, Theme $theme, Page $page=null)
	{
		$html = $this->m_templateHtml;
		
		if( isset($blockContent) ) {
			$wordBlocks = $blockContent->getWordBlocks();
			$pictures = $blockContent->getPictures();
		}
		
		// Add word blocks to template
		$wordBlockNums = $this->getWordBlockNums($html);
		foreach($wordBlockNums as $blockNum) {
			if( isset($wordBlocks[$blockNum]) ) {
			   $editValue = $wordBlocks[$blockNum];
                           $onfocus   = "";
			} else {
			   $editValue = "Type here";
                           $onfocus   = " onfocus=\"if(this.value==this.defaultValue)this.value=''\"";
                        }
			// Replace marker with form input
			$formInput = "<textarea name=\"wb{$blockNum}\" rows=\"8\" cols=\"40\"{$onfocus}>{$editValue}</textarea>";
			$html = str_replace( '<words' . $blockNum . '>', $formInput, $html);
		}
		
		// Add pictures to template
		$html = $this->putImagesInTemplate($html, $pictures, $theme, $page, array('blockedit'=>$blockContent->getId()));
		
		// Add hidden fields
		$html .= '<input type="hidden" name="a" value="' . PageEventDispatcher::ACTION_SAVE_BLOCK . '" />';
		$html .= "<input type=\"hidden\" name=\"template_id\" value=\"{$this->m_id}\" />";
		$html .= "<input type=\"hidden\" name=\"weight\" value=\"{$blockContent->getWeight()}\" />";
		if( isset($blockContent) && $blockContent->getId() != null ) {
			$html .= "<input type=\"hidden\" name=\"block_id\" value=\"{$blockContent->getId()}\" />";
		}
		
		// Add save button
		$html .= '<p class="block-com"><input type="image" src="/_images/si/icons/save.gif" value="Save"/></p>';

		// Add title to template
		$topContent = "<h2><input type=\"text\" name=\"title\" value=\"{$blockContent->getTitle()}\" /></h2>";

		// Create themed box
		$html = $theme->Box($html, $topContent, $this->getLayoutClass(), 'b'.$blockContent->getId());

		// Add form tags
		$html = "<form method=\"post\" action=\"{$page->PathWithQueryString()}\">{$html}</form>";
		
		return $html;
	}
	
	private function getWordBlockNums($templateHtml)
	{
		preg_match_all('<words(\d*)>', $templateHtml, $templateMatches, PREG_PATTERN_ORDER);
		return $templateMatches[1];
	}
	
	private function getImageBlockNums($templateHtml)
	{
		preg_match_all('<image(\d*)>', $templateHtml, $templateMatches, PREG_PATTERN_ORDER);
		return $templateMatches[1];
	}
	
	/**
	 * Takes the HTML with image template markers and inserts image HTML into them
	 * @return 
	 * @param $templateHtml Object
	 * @param $pictures Object
	 * @param $withEditLinks Object[optional]
	 */
	private function putImagesInTemplate($templateHtml, $pictures, Theme $theme, Page $pageForEditLinks = null, $otherLinks = null)
	{
		$imageBlockNums = $this->getImageBlockNums($templateHtml);
		
		foreach($imageBlockNums as $blockNum) {
			// Get picture or placeholder image
			$picture = (isset($pictures[$blockNum])) ? $pictures[$blockNum] : Image::GetPlaceHolder();
			
			// Add edit link if required
			if( isset($pageForEditLinks) ) {
				$linkParams = array_merge(array('imageedit'=>$blockNum), $otherLinks);
				$editIconLink = Link::CreateImageLink($theme->Icon('edit2'), $pageForEditLinks->PathWithQueryString($linkParams));
				$editIconLink->addHtmlProperty('class', "btnIEdit p{$blockNum}");

				$picture->setEditLink($editIconLink);
				$picture->addClass('edit');
			}

			$pictureHtml = $picture->Html(Image::SIZE_BOX);

			// Replace marker with image
			$templateHtml = str_replace( '<image' . $blockNum . '>', $pictureHtml, $templateHtml);
		}
		return $templateHtml;
	}
}
