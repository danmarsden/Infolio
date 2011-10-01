<?php
/**
 * leaplib.php - Creates a LEAP export of a user's infolio
 * See http://wiki.cetis.ac.uk/LEAP2A_specification
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Dan Marsden, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
function leap_header($user, $export_time) {
    return "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<feed xmlns=\"http://www.w3.org/2005/Atom\"
    xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"
    xmlns:leap=\"http://wiki.cetis.ac.uk/2009-03/LEAP2A_predicates#\"
    xmlns:leaptype=\"http://wiki.cetis.ac.uk/2009-03/LEAP2A_types#\"
    xmlns:categories=\"http://wiki.cetis.ac.uk/2009-03/LEAP2A_categories/\"
    xmlns:portfolio=\"".$_SERVER['SERVER_NAME']."/export/leap/".$user->getId()."/$export_time/\"
    xmlns:infolio=\"http://www.in-folio.org.uk/help/user_manager.html\"
    xmlns:mahara=\"http://wiki.mahara.org/Developer_Area/Import%2F%2FExport/LEAP_Extensions#\"
>
    <id>".$_SERVER['SERVER_NAME']."export/".$user->getId()."/$export_time</id>
    <title>Infolio LEAP2A Export for ".cleanforxml($user->getFullName()).", ".date("F j, Y, g:i a", $export_time)."</title>
    <updated>".date(DATE_RFC3339, $export_time)."</updated>
    <generator uri=\"http://www.in-folio.org.uk/\" version=\"2008122400\">Infolio</generator>"; 

}
function leap_footer() {
    return "
</feed>";
}
function leap_author($user, $password=false) {

$db = Database::getInstance();

$output =  "
    <author>
        <name>".cleanforxml($user->getFullName())."</name>
        <email>".$user->getEmail()."</email>
        <uri>portfolio:artefactinternal</uri>
        <infolio:usertype>".cleanforxml($user->getPermissionManager()->getUserType())."</infolio:usertype>
        <infolio:username>".cleanforxml($user->getUserName())."</infolio:username>
        <infolio:userdesc>".cleanforxml($user->getDescription())."</infolio:userdesc>
        <infolio:institution>".cleanforxml($user->getInstitution()->getUrl())."</infolio:institution>
        <infolio:theme>".cleanforxml($user->getTheme()->getName())."</infolio:theme>
        <infolio:profilepic>".$user->getProfilePictureId()."</infolio:profilepic>
";
if ($password) {
    $output .= "        <infolio:password>".$user->getPermissionManager()->getPassword()."</infolio:password>
";
    //now add picture password stuff
    $sql = "SELECT * FROM graphical_passwords WHERE user_id ='".$user->getId()."'";
    $result = $db->query($sql);
    $row = mysql_fetch_assoc($result);
    if (!empty($row)) {
        $output .= "<infolio:gfxpass pic='".$row['picture_asset_id']."' accuracy='".$row['click_accuracy']."' clicknumber='".$row['click_number_of']."' >";
        $sql = "SELECT * FROM grapical_password_coords WHERE graphical_passwordss_id = '".$row['id']."'";
        $result2 = $db->query($sql);
        if ($result2) {
            while ($row2 = mysql_fetch_assoc($result2)) {
                $output .= $row['x'].":".$row['y'].",";
            }
        }
        $output .= "</infolio:gfxpass>
";
    }
}
//now add any group info
    $output .= "<infolio:groups>";
    $sql = "SELECT g.title FROM groups g, group_members gm WHERE g.id=gm.group_id AND user_id='".$user->getId()."'";
    $result = $db->query($sql);
    While ($row = mysql_fetch_assoc($result)) {
        $output .= cleanforxml((string)$row['title'].",");
    }
    $output .= "</infolio:groups>
";

    $output .= "    </author>";
    return $output;
}
function leap_entry($entry) {
    $output = "
    <entry>
        <title>".cleanforxml($entry->title)."</title>
        <id>$entry->id</id>";
    $output .= (!empty($entry->author)) ? "
        <author>
            <name>".cleanforxml($entry->author)."</name>
        </author>" : '';

    $output .= (!empty($entry->updated)) ? "
        <updated>".cleanforxml($entry->updated)."</updated>" : '';
    $output .= (!empty($entry->created)) ? "
        <published>".cleanforxml($entry->created)."</published>" : '';
    if (!empty($entry->summary)) {
        $output .= '
        <summary';
        if ($entry->summarytype != 'text') {
            $output .= 'type="'.cleanforxml($entry->summarytype). '"';
        }
        if ($entry->summarytype == 'xhtml') {
            $output .= "<div xmlns=\"http://www.w3.org/1999/xhtml\">".cleanforxml($entry->summary)."</div>";
        } else {
            $output .= cleanforxml($entry->summary);
        }
    }
    $output .= "
        <content";
    $output .= ($entry->contenttype != 'text') ? " type=\"$entry->contenttype\"" : '';
    $output .= (!empty($entry->contentsrc)) ? " src=\"$entry->contentsrc\"" : '';
    $output .= ">";
    $output .= ($entry->contenttype == 'xhtml') ? "<div xmlns=\"http://www.w3.org/1999/xhtml\">" : '';
    $output .= (!empty($entry->content)) ? $entry->content : '';
    $output .= ($entry->contenttype == 'xhtml') ? "</div>" : '';
    $output .= "</content>";
    if (!empty($entry->resource)) {
        $output .= $entry->resource;
    } else {
    $output .= "
        <rdf:type rdf:resource=\"leaptype:$entry->leaptype\"/>";
    }
    return $output;
}
function leap_blocks($page, $studentUser) {
    global $studentTheme;
    //TODO: add Infolio specific content here for blocks to allow correct restore.
    
    $output = '<div>'.$page->HtmlTitle().'<div id="blocks">';
    //$page->HtmlBlocks($studentTheme, $studentUser) 
    $sql = "SELECT * FROM block WHERE page_id='".$page->getId()."' AND user_id='". $studentUser->getId()."'";
    $db = Database::getInstance();
    $result = $db->query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $output .= "<p>".cleanforxml($row['words0'])."</p>";
        if (!empty($row['picture0'])) {
            $output .= "<a rel=\"leap2:has_part\" href=\"portfolio:artefact".$row['picture0']."\"><img rel=\"leap2:has_part\" href=\"portfolio:artefact".$row['picture0']."\" /></a>";
        }
        if (!empty($row['picture1'])) {
            $output .= "<a rel=\"leap2:has_part\" href=\"portfolio:artefact".$row['picture1']."\"><img rel=\"leap2:has_part\" href=\"portfolio:artefact".$row['picture1']."\" /></a>";
        }
    }
    $output .='</div></div>';
    return $output;
}
function leap_categories() {
    
}
function leap_entryfooter() {
    return '
    </entry>';
}

function leap_resource($resource, $user) {
    $output = "
    <rdf:type rdf:resource=\"leap2:resource\" />
<category scheme=\"categories:resource_type#\" term=\"Offline\" />
<mahara:artefactplugin mahara:type=\"image\" mahara:plugin=\"file\"/>
<link rel=\"enclosure\" type=\"$resource->contenttype\" href=\"files/$resource->url\" />";
    
    //attach links to resources -db query against blocks to find all items with image 1 or 2 set to this id.
    $sql = "SELECT * FROM block WHERE picture0='". $resource->id."' OR picture1='". $resource->id."'";
    $db = Database::getInstance();
    $result = $db->query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $output .= "<link rel=\"leap2:is_part_of\" href=\"portfolio:view".$row['id']."\"/>";
    }
    //now attach tags to this resource
    $sql = "SELECT name FROM tags, tags_assets WHERE tags_assets.tag_id = tags.id AND tags_assets.asset_id='". $resource->id."'";
    $db = Database::getInstance();
    $result = $db->query($sql);
    if ($result) {
        $output .= "<infolio:tags>";
        while ($row = mysql_fetch_assoc($result)) {
            $output .= "<infolio:tag>".cleanforxml($row['name'])."</infolio:tag>";
        }
        $output .= "</infolio:tags>";
    }
    //now check for favorites
    $sql = "SELECT * FROM favourite_assets WHERE asset_id ='". $resource->id."' AND user_id='".$user->getId()."'";
    $db = Database::getInstance();
    $result = $db->query($sql);
    $row = mysql_fetch_assoc($result);
    if (!empty($row)) {
        $output .= "<infolio:favourite>true</infolio:favourite>";
    }
    return $output;
}



