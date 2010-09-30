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
    //first get this users institution share status
    global $studentUser, $db;
    //TODO: this could be optimised and less SQL used to obtain data.

    $userarray = array();
    $inshare = $studentUser->m_institution->allowSharing();
    if (empty($inshare)) {
        error('This Institution doesn\'t allow Tab Sharing');
    } elseif ($inshare == '1') {
        $sql = "SELECT * FROM user WHERE share ='1'";
        //catch explicitly set users.
    } elseif ($inshare == '2') {
        //catch null and 1 settings
        $sql = "SELECT * FROM user WHERE share <>'0'";
    }
    $sql .= "AND institution_id='".$studentUser->m_institution->getId()."' AND enabled='1' LIMIT ".$page.','.$count;

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

    echo "<table class='shareduserstable'>";
    foreach ($userarray as $usr) {
        echo "<tr>";
        if (empty($usr->user['profile_picture_id'])) {
            $imageurl = '/_images/bo/icon/student.png';
        } else {
            $imagesql = "SELECT title, href, type FROM assets WHERE id = ".$usr->user['profile_picture_id'];
            $imgresult = $db->query($imagesql);
            if ($imgrow = $db->fetchArray($imgresult)) {
                $imageurl = '/data/'.$studentUser->m_institution->getUrl()."-asset/".$imgrow['type']."/".$imgrow['href'];
            }
        }
        echo "<td><img src='$imageurl' class='sharedusericon'/></td><td>".$usr->user['firstName']. ' '. $usr->user['lastName'].'</td><td><ul>';
        foreach ($usr->tabs as $tb) {
            echo "<li><a href='/".$studentUser->m_institution->getUrl()."/viewtab/".$usr->user['ID'].'/'.$tb['ID']."/' target='_blank'>".$tb['name']."</a></li>";
        }
        echo '</ul></tr>';
    }
    echo "</table><br/><br/>";
}