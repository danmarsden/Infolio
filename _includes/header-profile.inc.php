<?php

// This file is part of In-Folio - http://blog.in-folio.org.uk/blog/
//
// In-Folio is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// In-Folio is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with In-Folio.  If not, see <http://www.gnu.org/licenses/>.

/**
 * header-profile.inc.php
 * Produces the HTML for the profile header section of the page.
 *
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: header-profile.inc.php 712 2009-07-23 14:09:55Z richard $
*/


?><div id="wrap-profile">
<?php
// Profile summary for logged in user
if( isset($studentUser) ) {
	print $studentUser->getProfilePicture()->Html(Image::SIZE_TAB_ICON, 'header_pic');
	?>
	<p id="site-name"><?php print($studentUser->getFirstName()); ?>'s eFolio</p>
	<p id="site-subname"><?php print ($studentUser->getInstitution()->getName()) ?></p>
<?php }

// Page header for non-logged in person
else { ?>
	<h1>eFolio</h1>
<?php } ?>
</div><!-- /#wrap-profile -->
<a id="my-tabs" href="managetabs.php" title="My tabs" >My tabs</a>
<a id="my-collection" href="collection.php" title="My collection" >My collection</a>
<?php
if( isset($studentUser) ) {
    $sharing = User::userCanViewShares($studentUser);
    if ($sharing) {
?>
<a id="shared-tabs" href="sharedtabs.php" title="Shared tabs" >Shared tabs</a>
<?php
    }
    // Scrolling tabs
	print $studentTheme->ScrollingTabs( $tabsMenu->Html() );
}
