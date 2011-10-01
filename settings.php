<?php

/**
 * settings.php - Change your settings
 *

 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
	<?php print $page->htmlHead(); ?>
	<?php include('_includes/head.inc.php'); ?>
	<link href="/_scripts/jq/scrollable.css" rel="stylesheet" type="text/css" />
</head>

<body class="<?php print $studentTheme->getBodyClass(); ?>">
<?php include('_includes/header.inc.php'); ?>
<div id="wrap-main">
	<?php include('_includes/header-profile.inc.php'); ?>
	<div id="wrap-content">
	<div id="wrap-content-inner">
		<h1>Tools and settings</h1>
		<?php print $studentTheme->Box($studentTheme->HtmlColourOptions(), '<h2><img src="/_images/si/icons/pallete.gif" width="35" height="35" alt="" /> Colour</h2>'); ?>
		
		<?php
		// Password options
		print $studentTheme->Box($studentUser->HtmlPasswordOptions($passwordChanged), '<h2 id="password-sect"><img src="/_images/si/icons/set-password.gif" width="35" height="35" alt="" /> Password</h2>'); ?>

		<?php print $studentTheme->BoxBegin('<h2 id="btnShowHide"><img src="/_images/si/icons/pic-pword.gif" width="35" height="35" alt="" /> Change picture password</h2>'); ?>
		
		<div class="hideBit">
		<div id="flvPassword">
			<p>Password picker loading</p>
		</div>
		</div>
		<?php print $studentTheme->BoxEnd(); ?>
<?php
        $allowsharing = $studentUser->getShare();
        if (!empty($allowsharing)) {
            $sharedtabs = array();
            $sql = "SELECT * from tab_shared WHERE userid=".$studentUser->getId();
            $query = $db->query($sql);
            while ($row = $db->fetchArray($query)) {
                $sharedtabs[$row['tabid']] = $row['tabid'];
            }
            //check for submitted changes to tabs.
            $pst = Safe::post('sharedtabs');
            $ptc = Safe::post('tab_count', PARAM_INT);
            if (!empty($pst)) {
                $tabcount = $ptc;
                $i = 0;
                $selectedtabs = array();
                While($i < $tabcount) {
                    $tid = Safe::post('tab_id_'.$i, PARAM_INT);
                    if (isset($tid)) {
                        $selectedtabs[] = $tid;
                        if (!isset($sharedtabs[$tid])) {
                            //need to save this tab.
                            $sql = "INSERT INTO tab_shared VALUES (".$studentUser->getID().", ".$tid.") ";
                            $query = $db->query($sql);
                        }
                    }
                    $i++;
                }

                //now get list of all selected tabs for this user and reset any that aren't shared anymore
                foreach ($sharedtabs as $sharedtab) {
                    if (!in_array($sharedtab, $selectedtabs)) {
                        //need to remove this tab.
                        $sql = "DELETE FROM tab_shared WHERE userid =".$studentUser->getID()." AND tabid=".$sharedtab;
                        $query = $db->query($sql);
                    }
                }
                $studentUser->fetchAndSetTabs(); //used to reset user->m_tabs;
//TODO: add notification to UI about change.
            }
?>
<a name="sharing"/></a>
        <?php print $studentTheme->BoxBegin('<h2 id="btnShowHide"><img src="/_images/si/icons/sharing.gif" width="35" height="35" alt="" /> Sharing</h2>'); ?>
        <p>Select the tabs that you would like to share with other users on this site.</p>
       <form action="/<?php echo $studentUser->getInstitution()->getURL(); ?>/settings.php#sharing" method="post">
 <?php
        $html = '<input type="hidden" name="tab_count" value="' . (count($studentUser->m_tabs)) . '" />';
        $html .= '<ul style="list-style:none;">';
        $tabCount = 0;
        $pst = Safe::post('sharedtabs');
        foreach($studentUser->m_tabs as $aTab) {
                $tabid = $aTab->getID();

                $checked = '';

                if (!empty($pst)) {
                    //use $selectedtabs
                    if (in_array($tabid, $selectedtabs)) {
                        $checked = 'checked="checked"';
                    }
                } else {
                    if (isset($sharedtabs[$tabid])) {
                        $checked = 'checked="checked"';
                    }
                }
                $html .= '<li><label for="tab_id_' . $tabCount . '"><input type="checkbox" id="tab_id_' . $tabCount . '" name="tab_id_' . $tabCount . '" value="' .$aTab->getId(). '" '.$checked.' /> ' .$aTab->getName().  '</label></li>';
                $tabCount++;

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
            $postreset = Safe::post('resethash');
            if (empty($hash) or !empty($postreset)) {
                //need to create hash and save it.
                $hash = newsharehash();
                $studentUser->setShareHash($hash);
                $studentUser->save($studentUser);
                //TODO: add notification to UI about change.
            }
            $url = curURL().'/'.$studentUser->getInstitution()->getURL().'/public/'.$studentUser->getID().'/'.$hash;
                echo "<a href='$url'>$url</a></p>";
        ?>
        <form action="/<?php echo $studentUser->getInstitution()->getURL(); ?>/settings.php#sharing" method="post">
        <input type="hidden" name="resethash" value="true"/>
        <input type="submit" value="Reset URL" />
        </form>
        <p>Resetting this URL will prevent anyone using the old URL from accessing your shared tabs</p>
        <?php print $studentTheme->BoxEnd(); ?>
<?php
        } //end check for displaying sharing options
?>
        <?php print $studentTheme->BoxBegin('<h2 id="btnShowHide"><img src="/_images/si/icons/export.gif" width="35" height="35" alt="" /> Export</h2>'); ?>
        <p>Select the tabs that you would like to include in the export.</p>
        <form method="post" action="../export.php">
<?php
        $html = '<input type="hidden" name="tab_count" value="' . (count($studentUser->m_tabs)-1) . '" />';
        $html .= '<ul style="list-style:none;">';
        $tabCount = 0;
        foreach($studentUser->m_tabs as $aTab) {
            if($aTab->getId() != 1) {
                $html .= '<li><label for="tab_id' . $tabCount.'"><input type="checkbox" id="tab_id' . $tabCount . '" name="tab_id' . $tabCount . '" value="' .$aTab->getId(). '" checked="checked" /> ' .$aTab->getName().  '</label></li>';
                $tabCount++;
            }
        }
        $html .= "</ul><br>";
        $html .= '<input type="checkbox" name="unusedassets" value="1"/> Include unused Assets';
        echo $html;
?>
        <h3>Choose an export format</h3><div class="element"><div><input type="radio" class="radio" id="export_formate091" name="format" tabindex="1" value="html"  checked="checked"/>

        <label for="export_formate091">Standalone HTML Website</label><div class="radio-description">Creates a self-contained website with your portfolio data. You cannot import this again, but it's readable in a standard web browser.</div></div><div>
        <input type="radio" class="radio" id="export_format442a" name="format" tabindex="1" value="leap"/> <label for="export_format442a">LEAP2A</label><div class="radio-description">Gives you an export in the LEAP2A standard format. You can use this to import your data into other LEAP2A compliant systems, although the export is hard for humans to read.</div></div></div>
        <input type="hidden" name="user_id" value="<?php echo $studentUser->getId();?>" />
        <input type="submit" value="Generate new export file" />
        </form>
        <?php print $studentTheme->BoxEnd(); ?>
		<?php include('_includes/footer.inc.php'); ?>
	</div></div>
	<?php Debugger::debugPrint(); ?>
</div>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
<script type="text/javascript" src="/_scripts/jq/scrollable.js"></script>
<?php print $page->getJavaScriptVariables()->HtmlJavaScriptBlock(); ?>
<script type="text/javascript" src="/_scripts/main.min.js"></script>
<script type="text/javascript" src="/_scripts/swfobject.js"></script>
<script type="text/javascript">
	$(document).ready( function() {
		var flashLoaded = false;

                var params = {};
                params.scale = "noscale";
                params.wmode = "opaque";
                var attributes = {};

		// Creates generic show/hide code for marked up content
		$('.hideBit').hide();
		$('#btnShowHide').toggle( function(e) {
			if(!flashLoaded) {
				swfobject.embedSWF('/_flash/PicturePasswordPicker.swf', 'flvPassword', '700', '560', '9', false, {page:'user'}, params, attributes);
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

<?php include('_includes/tracking.inc.php'); ?>
</body>
</html>
