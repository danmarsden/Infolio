<?php

/**
 * tab.php - A student's tab summary page
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: tab.php 843 2009-12-30 13:22:41Z richard $
 * @link       NA
 * @since      NA
*/

include_once('tab.inc.php');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<?php print $page->htmlHead(); ?>
	<?php include('_includes/head.inc.php'); ?>
</head>

<body class="<?php print $studentTheme->getBodyClass(); ?>" id="home">
<?php include('_includes/header.inc.php'); ?>
<div id="wrap-main">
	<?php include('_includes/header-profile.inc.php'); ?>
	<div id="wrap-content">
	<div id="wrap-content-inner">
		<?php print $studentTheme->HtmlMenu($editMenu, Theme::RIGHT); ?>
		<?php print $tab->HtmlTitle($page); ?>

		<?php // Picture choose
		if( isset($pictureChooseHtml) ) { print $studentTheme->SolidBox($pictureChooseHtml); } ?>

		<?php // Message
		print $studentTheme->BoxIf($tab->HtmlMessage($page, $studentTheme), $tab->HtmlMessageTitle()); ?>

		<?php if($tab->getNumPages() > 0) { ?>
		<div class="rb">
			<div class="bt"><div></div></div>
			<?php print $tab->HtmlPageListing($studentUser); ?>
			<div class="clear"></div>
			<?php print $studentTheme->HtmlMenu($sortMenu, Theme::LEFT); ?>
			<div class="bb"><div></div></div>
		</div><?php
		} ?>
		
		<?php include('_includes/footer.inc.php'); ?>
	</div><!-- /#wrap-content-inner -->
	</div><!-- /#wrap-content -->
</div><!-- /#wrap-main -->
<?php Debugger::debugPrint(); ?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="/_scripts/jq/scrollable.js"></script>
<?php print $page->getJavaScriptVariables()->HtmlJavaScriptBlock(); ?>
<script type="text/javascript" src="/_scripts/main.min.js"></script>
<?php include('_includes/tracking.inc.php'); ?>
</body>
</html>