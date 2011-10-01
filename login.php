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

include_once('login.inc.php');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Login</title>
	<?php include('_includes/head.inc.php'); ?>
</head>

<body id="home">
<?php include('_includes/header.inc.php'); ?>
<div id="wrap-main">
<div id="wrap-content">
<div id="wrap-content-inner">
	<h1>Log in to <?php print $institution->getName(); ?></h1>
	<p>If you're <a href="/where.php"><strong>not</strong> in <strong><?php print $institution->getName(); ?></strong> click here</a>.</p>
	<div class="box clear">
		<div class="box-head"><h2><img src="/_images/si/icons/set-password.gif" width="35" height="35" alt="" /> Login</h2></div>
		<div class="box-content">
			<?php print $message; ?>
			<form action="login.php" method="post" name="login">
			<input type="hidden" name="a" value="login" />
			<p><label for="username">Username: <input name="tUser" type="text" size="15" maxlength="55" id="username" title="username" /></label></p>
			<p><label for="password">Password: <input name="tPass" type="password" size="15" maxlength="55" id="password" title="password" /></label></p>
			<p><input type="submit" value="Login" /></p>
			</form>
			<p>Or</p>
			<ul class="inline-list">
				<li><a href="login2.php"><img src="/_images/si/icons/password-picture.gif" width="50" height="50" alt="" /> Picture login</a></li>
			</ul>
		</div>
	</div><!-- /.box -->

	<div class="box clear">
		<div class="box-head"><h2><img src="/_images/si/icons/lost-password.gif" width="35" height="35" alt="" />Lost your password?</h2></div>
		<div class="box-content">
			<p>Ask the person in charge for a new password, if you've lost it.</p>
		</div>
	</div><!-- /.box -->

</div><!-- /#wrap-content-inner -->
</div><!-- /#wrap-content -->	
</div><!-- /#wrap-main -->
<?php Debugger::debugPrint(); ?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="/_scripts/main.min.js"></script>
<?php include('_includes/tracking.inc.php'); ?>
</body>
</html>