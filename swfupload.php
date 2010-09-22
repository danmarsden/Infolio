<?php 
/**
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Stacey Walker, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
*/

include_once("system/initialise.php");
include_once("model/User.class.php");
include_once("function/shared.php");
include_once("function/core.php");

// Check user is logged in before letting them do stuff (except logging in)
$adminUser = require_admin();

// redirect back to correct page
header('Location: ' . $_POST['returnurl']);

?>
