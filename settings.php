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

if(!isset($studentUser->m_tabs)) {
    $studentUser->fetchAndSetTabs();
}

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
		<? print $studentTheme->Box($studentTheme->HtmlColourOptions(), '<h2><img src="/_images/si/icons/pallete.gif" width="35" height="35" alt="Colour" /> Colour</h2>'); ?>
		
		<?
		// Password options
		print $studentTheme->Box($studentUser->HtmlPasswordOptions($passwordChanged), '<h2 id="password-sect"><img src="/_images/si/icons/set-password.gif" width="35" height="35" alt="Password" /> Password</h2>'); ?>

		<? print $studentTheme->BoxBegin('<h2 id="btnShowHide"><img src="/_images/si/icons/pic-pword.gif" width="35" height="35" alt="Change picture password" /> Change picture password</h2>'); ?>
		
		<div class="hideBit">
		<div id="flvPassword">
			<p>Password picker loading</p>
		</div>
		</div>
		<? print $studentTheme->BoxEnd(); ?>
<?php
        $allowsharing = $studentUser->getShare();
        if (!empty($allowsharing)) {
            //check for submitted changes to tabs.
            if (!empty($_POST['sharedtabs'])) {
                $tabcount = (int)$_POST['tab_count'];
                $i = 0;
                $selectedtabs = array();
                While($i < $tabcount) {
                    if (isset($_POST["tab_id".$i])) {
                        $selectedtabs[] = (int)$_POST["tab_id".$i];
                        $tab = Tab::GetTabById((int)$_POST["tab_id".$i]);
                        $tab->setShare('1');
                        $tab->save($studentUser);
                    }
                    $i++;
                }
                //now get list of all selected tabs for this user and reset any that aren't shared anymore
                $sql = "SELECT ID FROM tab WHERE user_id='".$studentUser->getID()."' AND share=1";
                $query = $db->query($sql);
                while ($row = $db->fetchArray($query)) {
                    if (!in_array($row['ID'], $selectedtabs)) {
                        $tab = Tab::GetTabById($row['ID']);
                        $tab->setShare('0');
                        $tab->save($studentUser);
                    }
                }
                $studentUser->fetchAndSetTabs(); //used to reset user->m_tabs;
//TODO: add notification to UI about change.
            }
?>
<a name="sharing"/>
        <?php print $studentTheme->BoxBegin('<h2 id="btnShowHide"> Sharing</h2>'); ?>
        <p>Select the tabs that you would like to share with other users on this site.</p>
       <form action="/<?php echo $studentUser->getInstitution()->getURL(); ?>/settings.php#sharing" method="POST">
 <?php

        $html = '<input type="hidden" name="tab_count" value="' . (count($studentUser->m_tabs)-1) . '" />';
        $html .= '<ul style="list-style:none;">';
        $tabCount = 0;
        foreach($studentUser->m_tabs as $aTab) {
            if($aTab->getId() != 1) {
                $checked = '';
                if ($aTab->getShare() =='1') {
                    $checked = 'checked="checked"';
                }
                $html .= '<li><label for="tabids"><input type="checkbox" id="tabids" name="tab_id' . $tabCount . '" value="' .$aTab->getId(). '" '.$checked.' /> ' .$aTab->getName().  '</label></li>';
                $tabCount++;
            }
        }
        $html .= "</ul>";
        echo $html;
?>
        <input type="hidden" name="sharedtabs" value="true"/>
        <input type="submit" value="Save Shared Tabs" />
        </form>
        <h3>Public access URL</h3>
        <p>This URL allows public access to your shared tabs:<br/>
        <?php
            $hash = $studentUser->getShareHash();
            if (empty($hash) or !empty($_POST['resethash'])) {
                //need to create hash and save it.
                $hash = newsharehash();
                $studentUser->setShareHash($hash);
                $studentUser->save($studentUser);
                //TODO: add notification to UI about change.
            }
            $url = curURL().'/'.$studentUser->getInstitution()->getURL().'/public/'.$studentUser->getID().'/'.$hash;
                echo "<a href='$url'>$url</a></p>";
        ?>
        <form action="/<?php echo $studentUser->getInstitution()->getURL(); ?>/settings.php#sharing" method="POST">
        <input type="hidden" name="resethash" value="true"/>
        <input type="submit" value="Reset URL" />
        </form>
        <p>Resetting this URL will prevent anyone using the old URL from accessing your shared tabs</p>
        <?php print $studentTheme->BoxEnd(); ?>
<?php
        } //end check for displaying sharing options
?>
        <? print $studentTheme->BoxBegin('<h2 id="btnShowHide"> Export</h2>'); ?>
        <form method="post" action="../export.php"><ul style="list-style:none;"><p>Select the tabs that you would like to include in the export.</p>
         </ul>
<?php
        $html = '<input type="hidden" name="tab_count" value="' . (count($studentUser->m_tabs)-1) . '" />';
        $html .= '<ul style="list-style:none;">';
        $tabCount = 0;
        foreach($studentUser->m_tabs as $aTab) {
            if($aTab->getId() != 1) {
                $html .= '<li><label for="tabids"><input type="checkbox" id="tabids" name="tab_id' . $tabCount . '" value="' .$aTab->getId(). '" checked="checked" /> ' .$aTab->getName().  '</label></li>';
                $tabCount++;
            }
        }
        $html .= "</ul>";
        echo $html;
?>
        <h3>Choose an export format</h3><div class="element"><div><input type="radio" class="radio" id="export_formate091" name="format" tabindex="1" value="html"  checked="checked">

        <label for="export_formate091">Standalone HTML Website</label><div class="radio-description">Creates a self-contained website with your portfolio data. You cannot import this again, but it's readable in a standard web browser.</div></div><div>
        <input type="radio" class="radio" id="export_format442a" name="format" tabindex="1" value="leap"> <label for=\"export_format442a\">LEAP2A</label><div class=\"radio-description\">Gives you an export in the LEAP2A standard format. You can use this to import your data into other LEAP2A compliant systems, although the export is hard for humans to read.</div></div></div>
        <input type="hidden" name="user_id" value="<?php echo $studentUser->getId();?>" />
        <input type="submit" value="Generate new export file" />
        </form>
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
