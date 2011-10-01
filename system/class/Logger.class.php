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
 * Logs messages to the database
 * 

 * 
 * @author     	Elvir Leonard <elvir.leonard@rixcentre.org>
 * @copyright  	2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    	$Id: Logger.class.php 687 2009-07-08 14:25:35Z richard $
 * @link       	NA
 * @since      	NA
 * 
 */

include_once('Database.php');

class Logger
{
	// Logging message type constants
	const TYPE_DELETE = 'delete';
	const TYPE_ERROR = 'error';
	const TYPE_INFO = 'info';
	const TYPE_USER_MISTAKE = 'mistake';
	const TYPE_WARNING = 'warning';
	/**
	 * This will clear all logs in database
	 * @return 
	 */
	public static function ClearAll()
	{
		$db = Database::getInstance();
		$sql = "DELETE FROM system_log";
		$db->query($sql);
	}
	
	public static function HtmlTable($tableClass, $tableId)
	{
		// Start of table
		$html = '<table class="' . $tableClass . '" id="' . $tableId . '"><thead>' .
			'<tr><th>Message type</th><th>Time</th><th>User id / User name</th><th>IP address</th><th>Message</th></tr></thead>' .
			'<tbody>';

		// Get logging data from DB
		$db = Database::getInstance();
		$sql = "SELECT * FROM system_log ORDER BY created_time DESC";
		$result = $db->query($sql);
		
		// make the table body with results
		while($row = $db->fetchArray($result)) {
			$html .= '<tr>' .
				"<td>{$row['message_type']}</td>" .
				"<td>{$row['created_time']}</td>" .
				"<td>{$row['user_id']} / {$row['username']}</td>" .
				"<td>{$row['ip']}</td>" .
				"<td>{$row['message']}</td>" .
				'</tr>';
		}
		
		// Close table
		$html .= '</tbody></table>';
		
		return $html;
	}
	
	/**
	 * Public function to write to flat text file for logging
	 * @return 
	 * @param $message Object
	 */
	public static function Write($message, $messageType, User $user = null, $username = null, $institution = null)
	{
		// Set user id
		$userId = (isset($user)) ? $user->getId() : null;
		
		// Set institution id
		if(isset($institution)) {
			$institutionId = $institution->getId();
		}
		elseif (isset($user)) {
			$institutionId = $user->getInstitution()->getId();
		}

		// Set username
		if(!isset($username) && isset($user)) {
			$username = $user->getUserName();
		}

		$db = Database::getInstance();
		$data = array(
			'ip' => $_SERVER['REMOTE_ADDR'],
			'created_time' => "now()",
			'message_type' => $messageType,
			'message' => $message
		);
		
		if(isset($userId)) $data['user_id'] = $userId;
		if(isset($institutionId)) $data['institution_id'] = $institutionId;
		if(isset($username)) $data['username'] = $username;
		
		$db->perform("system_log", $data);
	}
}