<?php

/**
 * sharedtabs.php -
 *
 * @author     Dan Marsden, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
*/

include_once('sharedtabs.inc.php');
$pagenum = (int)Safe::GetArrayIndexValueWithDefault($_GET, 'page', '');
$count = 10;
$tablimit = 10;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<? print $page->htmlHead(); ?>
	<? include('_includes/head.inc.php'); ?>
</head>

<body class="<? print $studentTheme->getBodyClass(); ?>" id="home">
<? include('_includes/header.inc.php'); ?>
<div id="wrap-main">
	<div id="wrap-content">
	<div id="wrap-content-inner">
		<? print $page->HtmlTitle(); ?>
		<? if(isset($pageMessage)) print "<p>{$pageMessage}</p>"; ?>
        <?php
            print display_shared_tabs($pagenum, $count, $tablimit);
        ?>

		<? include('_includes/footer.inc.php'); ?>
	</div><!-- /#wrap-content-inner -->
	</div><!-- /#wrap-content -->
	<? Debugger::debugPrint(); ?>
</div><!-- /#wrap-main -->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="/_scripts/jq/scrollable.js"></script>
<? print $page->getJavaScriptVariables()->HtmlJavaScriptBlock(); ?>
<script type="text/javascript" src="/_scripts/main.min.js"></script>
<? include('_includes/tracking.inc.php'); ?>
</body>
</html>
