<?php
/**
 * Who can see an asset
 * Prints a list of user and group names in HTML.
 * A admin user must be logged in to get this data.
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: asset-who-can-see.php 383 2009-02-11 19:41:47Z richard $
*/

include_once('../../system/initialiseBackOffice.php');
include_once('model/Asset.class.php');

// Check user is logged in before letting them do stuff
$adminUser = BackOffice::RetrieveAndCheckAjaxAdminUser($_SESSION);
$gid = Safe::get('id');
if( isset($gid) ) {
	print Asset::getThoseWhoCanSeeMe($gid);
}