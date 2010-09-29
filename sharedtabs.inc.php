<?

/**
 * sharedtabs.inc.php -
 *
 * @author     Dan Marsden, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
*/


//include_once('conf.php');
include_once("system/initialise.php");
include_once('_includes/ui.inc.php');
include_once('class/si/Link.class.php');
include_once('class/si/Menu.class.php');
include_once('class/si/Theme.class.php');
include_once('class/si/SimplePage.class.php');
include_once('model/User.class.php');
include_once('model/Tab.class.php');
include_once('class/Events/TabEventDispatcher.class.php');
include_once('class/Events/SharedTabsEventDispatcher.class.php');

// Make sure they're logged in
include('_includes/login.inc.php');

/* ** Event handlers ** */
include_once('_includes/accessibility-toolbar-button-event.functions.php');

function onEnterShowSharedTabs(SimplePage $_page)
{
    global $studentTheme, $studentUser, $page;
    $page = $_page;
}

// Set up events
$eventD = new SharedTabsEventDispatcher($_GET, $_POST);
$eventD->setUser($studentUser);
$eventD->setOnEnterShowSharedTabsHandler('onEnterShowSharedTabs');
$eventD->DispatchEvents();

//function to display a list of users with shared tabs
//allows pagination and limit of tabs to show.
function display_shared_tabs($page, $count, $tablimit) {
    //first get this users insitution share status
    global $studentUser, $db;
    $userarray = array();
    $inshare = $studentUser->m_institution->allowSharing();
    if (empty($inshare)) {
        error('This Institution doesn\'t allow Tab Sharing');
    } elseif ($inshare == '1') {
        $sql = "SELECT * FROM user WHERE share ='1' LIMIT ".$page.','.$count;
        //catch explicitly set users.
    } elseif ($inshare == '2') {
        //catch null and 1 settings
        $sql = "SELECT * FROM user WHERE share <>'0' LIMIT ".$page.','.$count;
    }
    $result = $db->query($sql);
    While($row = $db->fetchArray($result)) {
        $userarray[$row['ID']]->user = $row;
        //now get tabs
        $sql2 = "SELECT * FROM tab WHERE enabled=1 AND share=1 AND user_id=".$row['ID'].' LIMIT '.$tablimit;
        $result2 = $db->query($sql2);
        While($row2 = $db->fetchArray($result2)) {
            $userarray[$row['ID']]->tabs[$row2['ID']] = $row2;
        }
    }
    echo "<div>";
    foreach ($userarray as $usr) {
        echo $usr->user['firstName']. ' '. $usr->user['lastName'].' Tabs: ';
        foreach ($usr->tabs as $tb) {
            echo $tb['name']. " | ";
        }
        echo "<br/>";
    }
    echo "</div><br/><br/>";
}