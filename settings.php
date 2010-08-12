<?php

/**
 * settings.php - Change your settings
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: settings.php 843 2009-12-30 13:22:41Z richard $
 * @link       NA
 * @since      NA
*/

include_once('settings.inc.php');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<? print $page->htmlHead(); ?>
	<? include('_includes/head.inc.php'); ?>
	<link href="/_scripts/jq/scrollable.css" rel="stylesheet" type="text/css" />
</head>

<body class="<? print $studentTheme->getBodyClass(); ?>">
<? include('_includes/header.inc.php'); ?>
<div id="wrap-main">
	<? include('_includes/header-profile.inc.php'); ?>
	<div id="wrap-content">
	<div id="wrap-content-inner">
		<h1>Tools and settings</h1>
		<? print $studentTheme->Box($studentTheme->HtmlColourOptions(), '<h2><img src="/_images/si/icons/pallete.gif" width="35" height="35" alt="" /> Colour</h2>'); ?>
		
		<?
		// Password options
		print $studentTheme->Box($studentUser->HtmlPasswordOptions($passwordChanged), '<h2 id="password-sect"><img src="/_images/si/icons/set-password.gif" width="35" height="35" alt="" /> Password</h2>'); ?>

		<? print $studentTheme->BoxBegin('<h2 id="btnShowHide"><img src="/_images/si/icons/pic-pword.gif" width="35" height="35" alt="" /> Change picture password</h2>'); ?>
		
		<div class="hideBit">
		<div id="flvPassword">
			<p>Password picker loading</p>
		</div>
		</div>
		<? print $studentTheme->BoxEnd(); ?>

		<? include('_includes/footer.inc.php'); ?>
	</div></div>
	<? Debugger::debugPrint(); ?>
</div>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
<script type="text/javascript" src="/_scripts/jq/scrollable.js"></script>
<? print $page->getJavaScriptVariables()->HtmlJavaScriptBlock(); ?>
<script type="text/javascript" src="/_scripts/main.min.js"></script>
<script type="text/javascript" src="/_scripts/swfobject.js"></script>
<script type="text/javascript">
	$(document).ready( function() {
		var flashLoaded = false;
		// Creates generic show/hide code for marked up content
		$('.hideBit').hide();
		$('#btnShowHide').toggle( function(e) {
			if(!flashLoaded) {
				swfobject.embedSWF('/_flash/PicturePasswordPicker.swf', 'flvPassword', '700', '560', '9', false, {page:'user'});
				flashLoaded = true;
			}
			$(e.target.parentNode.parentNode).find('.hideBit').show();
		}, function(e) {
			$(e.target.parentNode.parentNode).find('.hideBit').hide();
		});
		$('#btnShowPassword').toggle( function(e) {
			$(e.target.parentNode.parentNode).find('.hideBit').show();
		}, function(e) {
			$(e.target.parentNode.parentNode).find('.hideBit').hide();
		});

		// Show password, if they've just changed it
		if($('#hShow').length > 0) {
			$('#btnShowPassword').click();
		}
	});
</script>

<? include('_includes/tracking.inc.php'); ?>
</body>
</html>
