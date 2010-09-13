<?

/**
 * import.php - import of Infolio Leap file.
 *
 * @author     Dan Marsden, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
*/

// This file can take a long time to run, so needed to expand the timeout
set_time_limit(360);

include_once("system/initialise.php");
include_once("model/User.class.php");
include_once("function/shared.php");
include_once("function/core.php");

// Check user is logged in before letting them do stuff (except logging in)
$adminUser = require_admin();



