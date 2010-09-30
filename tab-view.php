<?php

/**
 * tabview.php -user to display read-only version of tabs for public access
 *
 * @author     Dan Marsden, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
*/
include_once("system/initialise.php");
include_once("system/function/core.php");
include_once('_includes/ui.inc.php');
include_once('system/class/si/SimplePage.class.php');
include_once('system/model/User.class.php');
include_once('system/model/Tab.class.php');
include_once('system/model/Page.class.php');

$page = new SimplePage();
$userid = (int)Safe::GetArrayIndexValueWithDefault($_GET, 'user_id', '');
$institution = Safe::StringForDatabase(Safe::GetArrayIndexValueWithDefault($_GET, 'institution', ''));
$tabid = (int)Safe::GetArrayIndexValueWithDefault($_GET, 'tab', '');
$sharehash = Safe::StringForDatabase(Safe::GetArrayIndexValueWithDefault($_GET, 'sharehash', ''));
$pageviewid = Safe::StringForDatabase(Safe::GetArrayIndexValueWithDefault($_GET, 'page', ''));

if (empty($userid) or empty($institution)) {
    error("invalid request");
}
$sql = "SELECT * FROM user WHERE ID='$userid' ";
if (empty($sharehash)) {
    //check user is logged in.
    include('_includes/login.inc.php');
} else {
    $sql .= " AND sharehash='$sharehash'";
}

$result = $db->query($sql);
if (!$row = $db->fetchArray($result)) {
    error("no user found");
}
$tabUser  = User::RetrieveById($userid, array());
$studentTheme = $tabUser->getTheme();

//get all of this students shared tabs:
$tabs = array();
$sql2 = "SELECT * FROM tab WHERE enabled=1 AND share=1 AND user_id=".$userid;
$result2 = $db->query($sql2);
$tabidvalid = false;
While($row2 = $db->fetchArray($result2)) {
    $active = '';
    if ($row2['ID']==$tabid) {
        $active = 'class="active"';
    }
    if (empty($sharehash)) {
        $taburl = "/".$tabUser->m_institution->getUrl()."/viewtab/".$userid.'/'.$row2['ID']."/";
    } else {
        $taburl = "/".$tabUser->m_institution->getUrl()."/public/".$userid.'/'.$sharehash.'/'.$row2['ID']."/";
    }

    $tabs[] = "<li $active><a title=\"View tab\" href='$taburl'>".
              '<img src="/images/size_tabicon/0/" width="55" height="55" alt="Tab 2"  title="Tab 2"  />'.
              $row2['name']."</a></li>";
    if ($row2['ID']==$tabid) {
        $tabidvalid = true;
    }
}
if (empty($tabs)) {
    error("no shared tabs found");
}
if (!$tabidvalid) { //make sure a valid tabid is passed.
    $tabid = 0;
}
if (!empty($tabid)) {
    $tab =Tab::GetTabById($tabid);
    $tab->setViewer($tabUser);
}
if (!empty($pageviewid)) {
    $pageviewid = (int)str_replace('page-', '', $pageviewid, $count);
    if ($count != 1) {
        error("invalid Pageid passed");
    }
    $pageview = Page::GetPageById($pageviewid, $tabUser);
    if ($pageview->getTab()->m_id != $tabid) {
        error("this page doesn't belong to this tab");
    }
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<? print $page->htmlHead(); ?>
	<? include('_includes/head.inc.php'); ?>
    <style>#nav-tabs li {visibility: visible;}</style>
</head>

<body class="<? print $studentTheme->getBodyClass(); ?>" id="home">
<div id="wrap-main">
<div id="wrap-profile">
<?
	print $tabUser->getProfilePicture()->Html(Image::SIZE_TAB_ICON, 'header_pic');
?>
	<p id="site-name"><? print($tabUser->getFirstName()); ?>'s eFolio</p>
	<p id="site-subname"><? print ($tabUser->getInstitution()->getName()) ?></p>
</div>
<div id="nav-tabs" class="<?php print $studentTheme->getName(); ?>">
 <ul class="items">
        <?php
            foreach ($tabs as $tb) {
               echo $tb;
            }
        ?>
     </ul>
</div>
	<div id="wrap-content">

	<div id="wrap-content-inner">
<?php

    if (!empty($pageview)) { //print page stuff if needed.

		print '<div id="blocks">'.$pageview->HtmlBlocks($studentTheme, $tabUser).'</div>';


		print $studentTheme->SolidBox('<h2 id="attachments">Attachments</h2>'.
              $pageview->HtmlAttachments());

    } elseif (!empty($tab)) {    //print tab stuff
		 print $tab->HtmlTitle($page);

         // Picture choose
		 if( isset($pictureChooseHtml) ) { print $studentTheme->SolidBox($pictureChooseHtml); }

		 // Message
		 print $studentTheme->BoxIf($tab->HtmlMessage($page, $studentTheme), $tab->HtmlMessageTitle());
		 if($tab->getNumPages() > 0) { ?>
		<div class="rb">
			<div class="bt"><div></div></div>
			<? print $tab->HtmlPageListing($tabUser); ?>
			<div class="clear"></div>
			<div class="bb"><div></div></div>
		</div>
<?php
        }
    } //end printing of tab stuff

?>

		<? include('_includes/footer.inc.php'); ?>
	</div><!-- /#wrap-content-inner -->
	</div><!-- /#wrap-content -->
</div><!-- /#wrap-main -->
<? Debugger::debugPrint(); ?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="/_scripts/jq/scrollable.js"></script>
<? print $page->getJavaScriptVariables()->HtmlJavaScriptBlock(); ?>
<script type="text/javascript" src="/_scripts/main.min.js"></script>
<? include('_includes/tracking.inc.php'); ?>
</body>
</html>