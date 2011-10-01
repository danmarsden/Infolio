<?php

/**
 * collection.php - The student's assets viewer (static version)
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: collection.php 832 2009-12-22 11:09:19Z richard $
 * @link       NA
 * @since      NA
*/

include_once('staticversion/collection.inc.php');
if (empty($collection)) {
    $collection = $studentUser->getAssetCollection();
    $assetFilter = AssetCollection::FILTER_ALL;
}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<?php print $page->htmlHead(); ?>
	<?php include('_includes/head.inc.php'); ?>
	<style media="all" type="text/css">
	#collection-scroller { height: 380px; }
	#collection-scroller ul.items { height: 400px; overflow-y: visible; }
	</style>
</head>
<body class="<?php print $studentTheme->getBodyClass(); ?>" id="home">
<!--<?php include('_includes/header.inc.php'); ?>-->
<div id="wrap-main">
	<?php include('_includes/header-profile.inc.php'); ?>
	<div id="wrap-content">
	<div id="wrap-content-inner">
		<?php print $page->HtmlTitle(); ?>
		<?php print $studentTheme->SolidBox( $collection->HtmlThumbnails($page, $studentUser, $assetFilter, null, null, false, Image::SIZE_BOX)); ?>
		<?php include('_includes/footer.inc.php'); ?>
	</div><!-- /#wrap-content-inner -->
	</div><!-- /#wrap-content -->
</div><!-- /#wrap-main -->
<script type="text/javascript" src="_scripts/jq/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="_scripts/jq/scrollable.js"></script>
<?php print $page->getJavaScriptVariables()->HtmlJavaScriptBlock(); ?>
<script type="text/javascript" src="_scripts/main.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("ul.items").css("overflow-y", "visible");
	});
</script>
</body>
</html>