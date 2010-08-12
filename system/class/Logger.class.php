<?php
/**
 * Logs messages to the database
 * 
 * LICENSE: This is an Open Source Project
 * 
 * @author     	Elvir Leonard <elvir.leonard@rixcentre.org>
 * @copyright  	2008 Rix Centre
 * @license    	http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
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