<?php

/**
 * page.php - A student's page that shows their generated content
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: page.php 843 2009-12-30 13:22:41Z richard $
 * @link       NA
 * @since      NA
*/

include_once('page.inc.php');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<? print $page->htmlHead(); ?>
	<? include('_includes/head.inc.php'); ?>
    <? include('_includes/swfupload.inc.php'); ?>
</head>

<body class="<? print $studentTheme->getBodyClass(); ?>" id="home">

<? include('_includes/header.inc.php'); ?>
<div id="wrap-main" class="p<? print $page->getId(); ?>">
	<? include('_includes/header-profile.inc.php'); ?>
	<div id="wrap-content">
	<div id="wrap-content-inner">
		<? print $studentTheme->HtmlMenu($editMenu, Theme::RIGHT); ?>
		<? print $page->HtmlTitle(); ?>
		<?
		if( isset($blockLayoutsMenu) ) {
			print $studentTheme->SolidBox($blockLayoutsMenu->Html());
		}
		?>

		<div class="fix">&nbsp;</div>
		<div id="loader"><img src="/_images/ajax-loader.gif" width="32" height="32" alt="Loading" />
		<br /><p>Loading images</p></div>
		
		<? print $studentTheme->BoxIf($page->HtmlMessage($studentTheme), $page->HtmlMessageTitle()); ?>

		<? // Picture choose
		if( isset($pictureChooseHtml) ) { print $studentTheme->SolidBox($pictureChooseHtml); } ?>

		<? // The blocks in this page
		print '<div id="blocks">' .
				$page->HtmlBlocks($studentTheme, $studentUser) . '</div>'; ?>

		<? // This page's attachments
		print $studentTheme->SolidBox(
			'<h2 id="attachments">Attachments</h2>' .
			$page->HtmlAttachments() .
			$uploader->HtmlUploadForm('fAttach', $page->PathWithQueryString()) .
			'<img id="loaderImg" class="hideme" src="/_images/ajax-loader.gif" width="32" height="32" alt="Loading" style="float: right;" />'
		);
        //now display comments stuff if enabled.
        $comment = $studentUser->m_institution->getComment();
        $commentapi = $studentUser->m_institution->getCommentApi();
        if ($comment == '1' && !empty($commentapi)) { //if comments enabled
        ?>
 <script>
var idcomments_acct = '<? print $commentapi; ?>';
var idcomments_post_id = '<? print $page->getId(); ?>';
var idcomments_post_url;
</script>
<span id="IDCommentsPostTitle" style="display:none"></span>
<script type='text/javascript' src='http://www.intensedebate.com/js/genericCommentWrapperV2.js'></script>

		<?
        }
        // Paging menu (pages in the parent tab)
		print $studentTheme->HtmlMenu($pagingMenu, Theme::LEFT); ?>

		<? include('_includes/footer.inc.php'); ?>
	</div><!-- /#wrap-content-inner -->
	</div><!-- /#wrap-content -->
	<? Debugger::debugPrint(); ?>
</div><!-- /#wrap-main -->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="/_scripts/jq/scrollable.js"></script>
<script type="text/javascript" src="/_scripts/jq/impromptu.js"></script>
<script type="text/javascript" src="/_scripts/page.min.js"></script>
<? print $page->getJavaScriptVariables()->HtmlJavaScriptBlock(); ?>
<script type="text/javascript" src="/_scripts/main.min.js"></script>
<? include('_includes/tracking.inc.php'); ?>
</body>
</html>