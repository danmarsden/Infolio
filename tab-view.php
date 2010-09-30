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

$page = new SimplePage();
$userid = (int)Safe::GetArrayIndexValueWithDefault($_GET, 'user_id', '');
$institution = Safe::StringForDatabase(Safe::GetArrayIndexValueWithDefault($_GET, 'institution', ''));
$tabid = (int)Safe::GetArrayIndexValueWithDefault($_GET, 'tab', '');
$sharehash = Safe::StringForDatabase(Safe::GetArrayIndexValueWithDefault($_GET, 'sharehash', ''));

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
    $tabs[] = "<a href='/".$tabUser->m_institution->getUrl()."/viewtab/".$userid.'/'.$row2['ID']."'>".$row2['name']."</a>";
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
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<? print $page->htmlHead(); ?>
	<? include('_includes/head.inc.php'); ?>
</head>

<body class="<? print $studentTheme->getBodyClass(); ?>" id="home">
<div id="wrap-main">
	<div id="wrap-content">
        <?php
            echo "<ul>";
            foreach ($tabs as $tb) {
               echo "<li>$tb</li>";
            }
            echo "</ul>";
        ?>
	<div id="wrap-content-inner">
<?php
        if (!empty($tab)) {
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
			<? print $studentTheme->HtmlMenu($sortMenu, Theme::LEFT); ?>
			<div class="bb"><div></div></div>
		</div>
<?php
            }
        } 
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