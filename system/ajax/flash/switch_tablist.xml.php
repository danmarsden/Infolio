<?php
/**
 * Tab list XML for switch browser
 * The user must be logged in to get this data.
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2009 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: switch_tablist.xml.php 780 2009-08-26 12:21:16Z richard $
 * @link       NA
 * @since      NA
*/

/*
 * * Query input *
 *   No input required. User id is stored in session.
 */

include_once('../../initialise.php');
include_once('model/User.class.php');
include_once('model/Tab.class.php');

// Check user is logged in before letting them do stuff
$ignoreInstitutionUrl = true;
include_once('_includes/login.inc.php');


$tabs = Tab::RetrieveTabsByUser($studentUser);

// Print XML
print "<tablist>\n";
foreach($tabs as $tab) {
	if ($tab->getId() > 1) print $tab->SwitchXml();
}
print "</tablist>";
