<?php

/**
 * bo.php - Back Office page
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Elvir Leonard
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: bo.php 838 2009-12-29 15:31:07Z richard $
 * @link       NA
 * @since      NA
*/

include_once("../system/initialiseBackOffice.php");
include_once('../system/model/User.class.php');
include_once('../system/model/Institution.class.php');


define('UPLOAD_LIMIT', 4000000);

$do = isset($_REQUEST['do']) ? stripslashes($_REQUEST['do']) : "";
$a = isset($_REQUEST['a']) ? stripslashes($_REQUEST['a']) : "";


// Get user details and redirect them to login page if they're not logged in
//session_start();
if( isset($_SESSION) ) {
	$adminUser = User::RetrieveBySessionData($_SESSION);
	
	// Nullify user if they don't have permission
	if(isset($adminUser) && !$adminUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN) ) {
		$adminUser = null;
	}
}
if( !isset($adminUser) ) {
	header("Location: login/");
}

// Asset upload (needs to be here for redirect)
$pa = Safe::post('a');
if($do == SECTION_ASSET && isset($pa) && $pa == 'upload') {
	$uploader = new Uploader('Upload a new asset', UPLOAD_LIMIT);
	$uploadProblems = false;
	// Try to upload the file and show an error message if there is a problem.
	try {
		$assetId = $uploader->copyUpload('upload', $adminUser);
	}
	catch(Exception $e) {
		$uploadProblems = true;
		$pageMessage = $e->getMessage();

		echo($pageMessage);
	}

	$asset = Asset::RetrieveById($assetId, $adminUser);
	$asset->setPublic(true);
	$asset->Save($adminUser);

	// Redirect (stops refresh causing duplicate uploads)
	if(!$uploadProblems) header("Location: .?do=8");
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Techdis</title>
<? include('_includes/head.inc.php'); ?>
</head>
<body class="tundra">
	<div id="popupContainer" style="display:block;">
	</div>
	<div id="popupLoadingBox" style="display:block;">
		<div id="popupBoxHeader">&nbsp;</div>
		<div style="text-align:center; vertical-align:middle; height:100%;">
			<br /><br /><br />
			<img src="/_images/ajax-loader.gif" width="32" height="32" alt="Loading" />
			<h3>Loading</h3>
		</div>
	</div>
	<div id="mainContainer">
		<div id="header">
			<div id="topCurve">
				<div style="float:left; width:20px; height:32px; background-image:url(<?php echo DIR_WS_IMAGES?>curve-left.png);"></div>
				<div style="float:left; width:950px; height:32px; background-repeat:repeat-x; background-image:url(<?php echo DIR_WS_IMAGES?>curve-normal.png);"></div>
				<div style="float:right; width:20px; height:32px; background-image:url(<?php echo DIR_WS_IMAGES?>curve-right.png);"></div>
			</div>
			<div id="topHeader">
				<div id="bannerHeader">
					Techdis<br  />
					<span style="font-size:0.7em;">Administration Area</span>
				</div>
				<div id="moduleIcon">
					<? if($backoffice->getIconForCurrentModule()!=""){?>
						<img src="/_images/bo/icon/<? print $backoffice->getIconForCurrentModule(); ?>" width="55" height="55" />
					<? } ?>
				</div>
			</div>
			<div class="headerDivider">&nbsp;</div>
			<div id="subHeader">
				<div style="float:left;">
					You are logged in as <strong><?php print $adminUser->getUserName(); ?></strong> | <a href="logout/">Logout</a>
				</div>
				<div style="float:right;">
					<a href=".">Home</a> | <a href="/">Front</a>
				</div>
			</div>
			<div id="subSubHeader">
				<p id="breadcrumbContainer"><? print $breadcrumb->create()?></p>
				<div id="printContainer">
					<!-- <img src="<?php echo DIR_WS_IMAGES?>pdf.png" height="25" onclick="alert('not implemented yet')" />&nbsp;&nbsp; -->
					<img src="/_images/bo/print.png" height="25" onclick="window.print();" alt="Print" />
					<img src="/_images/bo/help.png" height="25" onclick="showHelpWindow(this.url);" alt="Help" />
				</div>
			</div>
		</div>
		<div id="middle">
			<div id="leftContainer">
				<div id="leftMenu">
			
					<? include_once("module/bo-left-menu.php");?>
				</div>
			</div>
			<div id="workspaceContainer">
				<? include_once("module/bo-main.php");?>
			</div>
		</div>
		<div id="footer">
			<div id="footerContent">
			<? include_once("module/bo-footer.php");?>
			</div>
			<div class="headerDivider">&nbsp;</div>
			<div id="bottomCurve">
				<div style="float:left; width:20px; height:32px; background-image:url(<?php echo DIR_WS_IMAGES?>curve-bottom-left.png);"></div>
				<div style="float:left; width:950px; height:32px; background-repeat:repeat-x; background-image:url(<?php echo DIR_WS_IMAGES?>curve-bottom-normal.png);"></div>
				<div style="float:right; width:20px; height:32px; background-image:url(<?php echo DIR_WS_IMAGES?>curve-bottom-right.png);"></div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		// Hide popup and loadning box
		document.getElementById("popupContainer").style.display="none";
		document.getElementById("popupLoadingBox").style.display="none";
		
	</script>
</body>
</html>