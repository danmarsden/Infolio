<?php

/**
 * index.php - The student's homepage (About me)
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: index.php 843 2009-12-30 13:22:41Z richard $
 * @link       NA
 * @since      NA
*/

include_once('index.inc.php');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<?php print $page->htmlHead(); ?>
	<?php include('_includes/head.inc.php'); ?>
	<link href="/_scripts/jq/scrollable.css" rel="stylesheet" type="text/css" />
</head>

<body class="<?php print $studentTheme->getBodyClass(); ?>" id="home">
<?php include('_includes/header.inc.php'); ?>
<div id="wrap-main">
	<?php include('_includes/header-profile.inc.php'); ?>
	<div id="wrap-content">
	<div id="wrap-content-inner">
		<h1>About me</h1>
		<?php if( isset($pictureChooseHtml) ) { print $pictureChooseHtml; }
		print $studentTheme->Box($studentDetails, "<h2>{$studentUser->getFirstName()} {$studentUser->getLastName()}</h2>");
		
		include('_includes/footer.inc.php'); ?>
	</div><!-- /#wrap-content-inner -->
	</div><!-- /#wrap-content -->
</div>
<?php Debugger::debugPrint(); ?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="/_scripts/jq/scrollable.js"></script>
<?php print $page->getJavaScriptVariables()->HtmlJavaScriptBlock(); ?>
<script type="text/javascript" src="/_scripts/main.min.js"></script>

<?php include('_includes/tracking.inc.php'); ?>
</body>
</html>