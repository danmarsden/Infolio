<?php
/**
 * Converts text to speech
 * 
 * LICENSE: This is an Open Source Project
 * 
 * This class is handling date conversion
 * 
 * @author     	Richard Garside [www.richardsprojects.co.uk]
 * @copyright  	2009 Rix Centre
 * @license    	http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
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