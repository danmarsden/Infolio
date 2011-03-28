<?php
include_once('login2.inc.php');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Picture Login</title>
	<?php include('_includes/head.inc.php'); ?>
</head>

<body>
<?php include('_includes/header.inc.php'); ?>
<div id="wrap-main">
	<div id="wrap-content">
	<div id="wrap-content-inner">
		<h1>Login</h1>

		<div class="box clear">
			<div class="box-head"><h2>Picture password</h2></div>
			<div class="box-content">
				<?php print $message; ?>
				<object type="application/x-shockwave-flash" data="/_flash/login.swf?inst=<?php print $institution->getId(); ?>" width="800" height="600">
				<param name="movie" value="/_flash/login.swf?inst=<?php print $institution->getId(); ?>" />
				</object>
			</div>
		</div><!-- /.box -->


		<div class="box clear">
			<div class="box-head"><h2>Lost your password?</h2></div>
			<div class="box-content">
				<p>Ask the person in charge for a new password, if you've lost it.</p>
			</div>
		</div><!-- /.box -->
		
	</div></div>
	<?php Debugger::debugPrint(); ?>
</div>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="/_scripts/main.min.js"></script>
<?php include('_includes/tracking.inc.php'); ?>
</body>
</html>