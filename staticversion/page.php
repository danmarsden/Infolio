<?php

/**
 * page.php (static version) - A student's page that shows their generated content
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: page.php 832 2009-12-22 11:09:19Z richard $
 * @link       NA
 * @since      NA
*/

include('staticversion/page.inc.php');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<?php print $page->htmlHead(); ?>
	<?php include('_includes/head.inc.php'); ?>
</head>

<body class="<?php print $studentTheme->getBodyClass(); ?>" id="home">
<!--<?php include('_includes/header.inc.php'); // Keep commented, still need code to run (mainly to init $tabsMenu), but not display ?>-->
<div id="wrap-main" class="p<?php print $page->getId(); ?>">
	<?php include('_includes/header-profile.inc.php'); ?>
	<div id="wrap-content">
	<div id="wrap-content-inner">
		<?php print $page->HtmlTitle(); ?>

		<div class="fix">&nbsp;</div>
		
		<?php print $studentTheme->BoxIf($page->HtmlMessage($studentTheme), $page->HtmlMessageTitle()); ?>

		<?php // The blocks in this page
		print '<div id="blocks">' .
				$page->HtmlBlocks($studentTheme, $studentUser) . '</div>'; ?>

		<?php // This page's attachments
		print $studentTheme->SolidBox(
			'<h2 id="attachments">Attachments</h2>' .
			$page->HtmlAttachments()
		);?>

		<?php // Paging menu (pages in the parent tab)
		print $studentTheme->HtmlMenu($pagingMenu, Theme::LEFT); ?>

		<?php include('_includes/footer.inc.php'); ?>
	</div><!-- /#wrap-content-inner -->
	</div><!-- /#wrap-content -->
</div><!-- /#wrap-main -->
<script type="text/javascript" src="_scripts/jq/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="_scripts/jq/scrollable.js"></script>
<?php print $page->getJavaScriptVariables()->HtmlJavaScriptBlock(); ?>
<script type="text/javascript" src="_scripts/main.js"></script>
</body>
</html>