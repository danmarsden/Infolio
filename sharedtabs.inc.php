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

    // user cannot view shares so shouldn't be on this page
    $sharing = User::userCanViewShares($studentUser);
    if (!$sharing) {
        return 'You do not have the right permissions to view shared Tabs';
    }

    //TODO: this could be optimised and less SQL used to obtain data.
    $page = (int)$page;
    $count = (int)$count;
    $tablimit = (int)$tablimit;
    $where = '';
    $userarray = array();
    $numusers = 0; //number of users in db with sharing enabled.

    $inshare = $studentUser->getInstitution()->allowSharing();
    if ($inshare == '1') {
        $where = "u.share ='1'";
        //catch explicitly set users.
    } elseif ($inshare == '2') {
        //catch null and 1 settings
        $where = "u.share <>'0'";
    }
    $where .= " AND u.institution_id='".$studentUser->m_institution->getId()."' AND u.enabled='1' ";

    //get count of users
    $sqlcount = "SELECT count(DISTINCT u.id)
                    FROM user u
                    LEFT JOIN tab_shared ts ON ts.userid = u.id
                    JOIN tab t ON t.id = ts.tabid
                    WHERE " .
                    $where .
                    "AND t.id = ts.tabid AND t.enabled = 1";
    $resultcount = $db->query($sqlcount);
    if ($rowc =  mysql_fetch_array($resultcount, MYSQL_NUM)) {
        if ((int)$rowc[0] === 0) {
            return "No shared tabs were found";
        }
        $numusers = (int)$rowc[0];
        if ($count > $numusers) {
            $page = 0; //reset page to display records if someone tries to change url.
        }
   }
    $sqlpage = 0;
    if (!empty($page)) {
        $sqlpage = $page -1;
    }
    $sqlpage = $sqlpage*$count;
    //TODO: need to optimise the following SQL - should be able to do this with one sql call instead of one per user.

    $sql = "SELECT DISTINCT u.ID, u.* FROM user u, tab t, tab_shared ts WHERE ".$where;
    $sql .= " AND t.ID=ts.tabid AND t.enabled=1 AND ts.userid=u.id";
    $sql .= " LIMIT ".$sqlpage.','.$count;
    $result = $db->query($sql);
    While($row = $db->fetchArray($result)) {
        $userarray[$row['ID']] = new stdClass();
        $userarray[$row['ID']]->user = $row;
        //now get tabs
        $sql2 = "SELECT * FROM tab t, tab_shared ts WHERE t.ID=ts.tabid AND t.enabled=1 AND ts.userid=".$row['ID'].' LIMIT '.$tablimit;
        $result2 = $db->query($sql2);
        $numrows = mysql_num_rows($result2);
        if (empty($numrows)) {
            unset($userarray[$row['ID']]);
        } else {
            While($row2 = $db->fetchArray($result2)) {
                $userarray[$row['ID']]->tabs[$row2['ID']] = $row2;
            }
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
        echo "<td class='sharedusers-image'><img src='$imageurl' class='sharedusericon'/></td><td class='sharedusers-name'>".$usr->user['firstName']. ' '. $usr->user['lastName'].'</td><td class="sharedusers-tabs"><ul>';
        foreach ($usr->tabs as $tb) {
            echo "<li><a href='/".$studentUser->m_institution->getUrl()."/viewtab/".$usr->user['ID'].'/'.$tb['ID']."/' target='_blank'>".$tb['name']."</a></li>";
        }
        echo '</ul></tr>';
    }
    echo "</table>";
    if ($numusers > $count) {
        //pagination required.
        echo "<div class='pagination'>";
        $i = 1;
        $lastpage = ceil($numusers/$count);
        while ($i <= $lastpage) {
            if ($page==$i) {
                echo $i;
            } else {
                echo "<a href='sharedtabs.php?page=".$i."'>".$i."</a>";
            }
            if ($i <> $lastpage) {
                echo " | ";
            }
            $i++;
        }
        echo "</div>";
    }
    echo "<br/><br/>";
}
