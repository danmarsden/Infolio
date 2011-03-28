<?php

/**
 * tabs.php - The student's tab manager 
 *
 */

include_once('managetabs.inc.php');
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
		<?php print $page->HtmlTitle(); ?>
        <?php print $studentTheme->SolidBox(Tab::manageTabsContent($studentUser, $studentTheme, $page)); ?>

		<?php include('_includes/footer.inc.php'); ?>
	</div><!-- /#wrap-content-inner -->
	</div><!-- /#wrap-content -->
	<?php Debugger::debugPrint(); ?>
</div><!-- /#wrap-main -->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="/_scripts/jq/scrollable.js"></script>
<?php print $page->getJavaScriptVariables()->HtmlJavaScriptBlock(); ?>
<script type="text/javascript" src="/_scripts/main.min.js"></script>
<?php include('_includes/tracking.inc.php'); ?>
</body>
</html>
