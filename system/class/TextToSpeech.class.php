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
 * Converts text to speech
 * 

 * 
 * This class is handling date conversion
 * 
 * @author     	Richard Garside [www.richardsprojects.co.uk]
 * @copyright  	2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    	$Id: TextToSpeech.class.php 722 2009-07-27 10:23:02Z richard $
 * @link       	NA
 * @since      	NA
 * 
 */

class TextToSpeech
{
	private $m_text;
	private $m_filePath;

	public function TextToSpeech($text, $fileName, $fileDir)
	{
		$this->m_text = $text;
		$this->m_filePath = $fileDir . $fileName . '.mp3';
		$wavDstFile = $fileDir . $fileName . '.wav';

		// Create text speech file, if it doesn't exist already
		if( !file_exists($this->m_filePath) ) {
			// Convert text to a wav file
			$cmd = 'echo "' . $this->m_text .'" | text2wave -o ' . $wavDstFile;
			exec($cmd, $output, $returnVal);
			//Debugger::debug(print_r($output, true) . ' = ' . $returnVal, 'PageBlock::TextToSpeech_1');

			// Convert wav file to mp3
			$lameCmd = "lame {$wavDstFile} {$this->m_filePath} --resample 22.05";
			exec($lameCmd, $output, $returnVal);
			//Debugger::debug(print_r($output, true) . ' = ' . $returnVal, 'PageBlock::TextToSpeech_2');

			// Delete wav file
			unlink($wavDstFile);
		}
	}

	public function getFilePath()
	{
		return $this->m_filePath;
	}
}