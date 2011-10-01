<?php
/**
 * Tab list XML for switch browser
 * The user must be logged in to get this data.
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: switch_user.xml.php 776 2009-08-25 13:11:50Z richard $
 * @link       NA
 * @since      NA
*/

/*
 * * Query input *
 *   No input required. User id is stored in session.
 */

include_once('../../initialise.php');

// Check user is logged in before letting them do stuff
$ignoreInstitutionUrl = true;
include_once('_includes/login.inc.php');

// Print XML
print "<userinfo>\n" .
		"<fullname>{$studentUser->getFirstName()} {$studentUser->getLastName()}</fullname>\n" .
		"<institution>{$studentUser->getInstitution()->getName()}</institution>\n" .
		"<userimage>{$studentUser->getProfilePicture()->getFullHref(Image::SIZE_BOX, true)}</userimage>\n" .
		"<description>{$studentUser->getDescription()}</description>\n" .
	"</userinfo>";
