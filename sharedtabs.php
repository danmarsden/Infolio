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
 * sharedtabs.php -
 *
 * @author     Dan Marsden, Catalyst IT Ltd, (http://danmarsden.com)
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
*/

include_once('sharedtabs.inc.php');
$pagenum = Safe::getWithDefault('page', 0, PARAM_INT);
$count = 10;
$tablimit = 10;

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
        <?php
            print $studentTheme->SolidBox(display_shared_tabs($pagenum, $count, $tablimit));
        ?>

		<?php include('_includes/footer.inc.php'); ?>
	</div><!-- /#wrap-content-inner -->
	</div><!-- /#wrap-content -->
	<?php Debugger::debugPrint(); ?>
</div><!-- /#wrap-main -->
<script type="text/javascript" src="_scripts/main.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="/_scripts/jq/scrollable.js"></script>
<?php print $page->getJavaScriptVariables()->HtmlJavaScriptBlock(); ?>
<script type="text/javascript" src="/_scripts/main.min.js"></script>
<?php include('_includes/tracking.inc.php'); ?>
</body>
</html>
