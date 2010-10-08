<?php

/**
 * header-profile.inc.php
 * Produces the HTML for the profile header section of the page.
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: header-profile.inc.php 712 2009-07-23 14:09:55Z richard $
 * @link       NA
 * @since      NA
*/


?><div id="wrap-profile">
<?
// Profile summary for logged in user
if( isset($studentUser) ) {
	print $studentUser->getProfilePicture()->Html(Image::SIZE_TAB_ICON, 'header_pic');
	?>
	<p id="site-name"><? print($studentUser->getFirstName()); ?>'s eFolio</p>
	<p id="site-subname"><? print ($studentUser->getInstitution()->getName()) ?></p>
<? }

// Page header for non-logged in person
else { ?>
	<h1>eFolio</h1>
<? } ?>
</div><!-- /#wrap-profile -->
<a id="my-tabs" href="managetabs.php" title="My tabs" >My tabs</a>
<a id="my-collection" href="collection.php" title="My collection" >My collection</a>
<?php
$sharing = User::userCanShare($studentUser);
if ($sharing) {
?>
<a id="shared-tabs" href="sharedtabs.php" title="Shared tabs" >Shared tabs</a>
<?
}
// Scrolling tabs
if( isset($studentUser) ) {
	print $studentTheme->ScrollingTabs( $tabsMenu->Html() );
}
