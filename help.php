<?php

/**
 * page.php - Context sensitive help page
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id$
 * @link       NA
 * @since      NA
*/

include_once('help.inc.php');
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
	<? include('_includes/header-profile.inc.php'); ?>
	<div id="wrap-content">
	<div id="wrap-content-inner">
		<? print $page->HtmlTitle(); ?>

		<div class="help-items"><? // The help links on this page
		foreach($helpLinks as $helpLink){
			print $studentTheme->Box($helpLink->getText(), "<h2>{$helpLink->getHeader()}</h2>");
		} ?></div>

	</div><!-- /#wrap-content-inner -->
	</div><!-- /#wrap-content -->
	<? Debugger::debugPrint(); ?>
</div><!-- /#wrap-main -->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="/_scripts/jq/scrollable.js"></script>
<script type="text/javascript" src="/_scripts/jq/impromptu.js"></script>
<? print $page->getJavaScriptVariables()->HtmlJavaScriptBlock(); ?>
<script type="text/javascript" src="/_scripts/main.min.js"></script>
<? include('_includes/tracking.inc.php'); ?>
</body>
</html>