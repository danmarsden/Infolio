<?php
/**
 * leaplib.php - Creates a LEAP export of a user's infolio
 * See http://wiki.cetis.ac.uk/LEAP2A_specification
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Dan Marsden, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
*/
function leap_header($user, $export_time) {
    return "<? xml version=\"1.0\" encoding=\"utf-8\"?>
<feed xmlns=\"http://www.w3.org/2005/Atom\"
    xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"
    xmlns:leap=\"http://wiki.cetis.ac.uk/2009-03/LEAP2A_predicates#\"
    xmlns:leaptype=\"http://wiki.cetis.ac.uk/2009-03/LEAP2A_types#\"
    xmlns:categories=\"http://wiki.cetis.ac.uk/2009-03/LEAP2A_categories/\"
    xmlns:portfolio=\"".$_SERVER['SERVER_NAME']."/export/leap/".$user->getId()."/$export_time/\"
    xmlns:infolio=\"http://www.in-folio.org.uk/help/user_manager.html\"
>
    <id>".$_SERVER['SERVER_NAME']."export/".$user->getId()."/$export_time</id>
    <title>Infolio LEAP2A Export for ".$user->getFullName().", ".date("F j, Y, g:i a", $export_time)."</title>
    <updated>".date(DATE_RFC3339, $export_time)."</updated>
    <generator uri=\"http://www.in-folio.org.uk/\" version=\"2008122400\">Infolio</generator>".leap_author($user); 

}
function leap_footer() {
    return "
</feed>";
}
function leap_author($user) {
    return "
    <author>
        <name>".$user->getFullName()."</name>
        <email>".$user->getEmail()."</email>
        <uri>portfolio:artefactinternal</uri>
    </author>
    ";
}
function leap_entry($entry) {
    $output = "
    <entry>
        <title>$entry->title</title>
        <id>$entry->id</id>";
    $output .= (!empty($entry->author)) ? "
        <author>
            <name>$entry->author</name>
        </author>" : '';

    $output .= (!empty($entry->updated)) ? "
        <updated>$entry->updated</updated>" : '';
    $output .= (!empty($entry->created)) ? "
        <published>$entry->created</published>" : '';
    if (!empty($entry->summary)) {
        $output .= '
        <summary';
        if ($entry->summarytype != 'text') {
            $output .= "type=\"$entry->summarytype\"";
        }
        if ($entry->summarytype == 'xhtml') {
            $output .= "<div xmlns=\"http://www.w3.org/1999/xhtml\">".$entry->summary."</div>";
        } else {
            $output .= $entry->summary;
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
    $output .= "</content>
        <rdf:type rdf:resource=\"leaptype:$entry->leaptype\"/>";
    return $output;
}
function leap_blocks($page, $studentUser) {
    global $studentTheme;
    //TODO: add Infolio specific content here for blocks to allow correct restore.
    return '<div>'.$page->HtmlTitle().'<div id="blocks">'.
               $page->HtmlBlocks($studentTheme, $studentUser) . '</div></div>';
}
function leap_categories() {
    
}
function leap_entryfooter() {
    return '
    </entry>';
}
function leap_links() {
    
}
function leap_view() {
    
}


