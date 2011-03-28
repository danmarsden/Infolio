<?
include_once('system/initialise.php');
include_once('class/si/SimplePage.class.php');
include_once('model/Image.class.php');
include_once('model/Institution.class.php');
include_once('model/User.class.php');
$page = new SimplePage('TechDis: Where are you from?');

// Get all institutions
$institutions = Institution::RetrieveAll()

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php print $page->getTitle(); ?></title>
	<?php include('_includes/head.inc.php'); ?>
</head>

<body id="home">
<div id="wrap-main">
	<div id="wrap-content">
	<div id="wrap-content-inner">
		<?
        $msg = Safe::get('msg');
		if(isset($msg)) {
			print "<p>{$msg}</p>";
		}
		?>
		<h1>Where are you from?</h1>
		<div class="rb">
			<div class="bt"><div></div></div>

			<?php // Display all institution link boxes
			foreach($institutions as $institution) {
				print $institution->HtmlLinkBox();
			}
			?>

			<div class="clear" />
			<div class="bb"><div></div></div>
		</div>

	</div><!-- /#wrap-content-inner -->
	</div><!-- /#wrap-content -->
</div><!-- /#wrap-main -->
<?php include('_includes/tracking.inc.php'); ?>
</body>
</html>