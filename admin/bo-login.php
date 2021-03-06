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
 * bo-login.php - Login and logout page
 *
 *
 * @author     Elvir Leonard
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: bo-login.php 640 2009-06-23 09:12:06Z richard $
*/

include_once('../system/initialiseBackOffice.php');
include_once('class/PermissionManager/PermissionManager.class.php');

$do = Safe::request('do');
$do = !isset($do) ? '': $do;
$a = Safe::request('a');
$a = !isset($a) ? '': $a;

if($a == 'logout') {
	PermissionManager::Logout();
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Techdis Administration Area Login Page</title>
<?php include('_includes/head.inc.php'); ?>
<script type="text/javascript" src="/admin/_scripts/login.js"></script>
</head>
<body>
	<div id="mainContainer">
		<div id="header">
			<div id="topHeader">
				<div style="float:left; width:20px; height:32px; background-image:url(/_images/bo/curve-left.png);"></div>
				<div style="float:right; width:20px; height:32px; background-image:url(/_images/bo/curve-right.png);"></div>
				<div id="bannerHeader">
					In folio<br  />
					<span style="font-size:0.7em;">Administration Area</span>
				</div>

			</div>
		</div>

		<div id="popupLoginBox">
			<div id="popupBoxHeader">
			<?php
			switch($a) {
				case 'fp':
					print 'Retrieve your login details';
					break;
				case 'logout':
					print 'Logout';
					break;
				default:
					print 'Login';
					break;
			}
			?></div>
			<div id="loginBox">
			<?php
			switch($a) {
				case 'fp':
					?>Please enter your email address

					<p>Email<br />
					<input type="text" value="" size="20" name="email" style="width:80%;" maxlength="20" /><br />
					<br />
					<input type="button" onclick="sendPassword();" value="Send" name="a" style="float:right;" />
					<input type="button" onclick="loginCancelButton();" value="Cancel" name="a" style="float:right;" />
					</p><?php
					break;
				case 'logout':
					?>
					<ul>
						<li><a href="../login/">Click here to login</a></li>
						<li><a href="../../">Click here to visit techdis</a></li>
					</ul><?php
					break;
				default:
					?>
					<form onsubmit="doLogin(); return false;" method="get">
						<input type="hidden" name="institution" id="institution" value="<?php print Safe::get('institution',PARAM_ALPHANUMEXT); ?>" />

							<p>Please enter username and password</p>
							<div id="errorNotificationContainer"></div>
							<p><label for="">Username
							<input type="text" size="20" name="username" id="usernameInput" style="width:80%;" maxlength="20" /></label></p>
							<p><label for="">Password
							<input type="password" style="width:80%;" id="passwordInput" size="20" name="password" /></label></p>
							<p><a href="?a=fp">Forgotten password</a></p>
							<p style="text-align:right;"><input type="submit" value="Login" name="a" /></p>

					</form><?php
					break;
			}
			?></div>
		</div>

		
		<div style="clear:both; background-color:#FFFFFF; min-height:500px;">
		</div>
		<div id="footer">
			<div id="footerContent">
			<?php include("module/bo-footer.php");?>
			</div>
			<div id="bottomCurve">
				<div style="float:left; width:20px; height:32px; background-image:url(/_images/bo/curve-bottom-left.png);"></div>
				<div style="float:right; width:20px; height:32px; background-image:url(/_images/bo/curve-bottom-right.png);"></div>
			</div>
		</div>
	</div>
</body>
</html>