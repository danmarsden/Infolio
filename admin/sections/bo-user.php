<?php

/**
 * User admin screen
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Elvir Leonard
 * @copyright  2008 Rix Centre
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
 * @version    $Id: bo-user.php 838 2009-12-29 15:31:07Z richard $
 * @link       NA
 * @since      NA
 */

//controller
if($a=="edit" || $a=="add" || $a=="delete") {
	showForm();
}
else {
	showGrid();
}

/**
 * Show a grid of users
 * @global <type> $do
 * @global <type> $adminUser
 * @global <type> $jsFramework
 */
function showGrid()
{
	global $do, $adminUser, $jsFramework;
	$jsFramework->jqueryTableSorter();
	?>
	<script type="text/javascript" src="/admin/_scripts/bo-user-grid.js"></script>
	<form action="." method="get">
	<input type="hidden" name="do" value="<? print $do; ?>" />

	<?
	// Admin users get to select institution they're acting on
	$institution = null;
	if( $adminUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN) ) {
		// Get id of institute to view (if one has been chosen, otherwise use default)
		$chosenInstituteId = (isset($_REQUEST['inst'])) ?
			$_REQUEST['inst'] :
			$adminUser->getInstitution()->getId();

		$institutions = Institution::RetrieveAll();
		print '<select name="inst">';
		foreach($institutions as $institution) {
			print "<option value=\"{$institution->getId()}\"";
			if(isset($chosenInstituteId) && $institution->getId() == $chosenInstituteId) print ' selected="selected"';
			print ">{$institution->getName()}</option>";
		}
		print '</select>';
		
		// Set institution from callbacks
		$institution = null;
		if(isset($chosenInstituteId) && isset($institutions[$chosenInstituteId])) {
			$institution = $institutions[$chosenInstituteId];
		}

		print '<input type="submit" value="Show" />';
	}
	?>

	<table class="dataGridTable tablesorter" cellspacing="1" id="dataGridTable">
		<thead>
			<tr>
				<th>Name</th>
				<th>Username</th>
				<th>Role</th>
				<th>Updated</th>
				<th class="actionTd"><a href=".?do=<?php echo $do?>&amp;a=add">Create</a></th>
			</tr>
		</thead>
		<tbody>
		<?

		/*
		 * Search function not yet available
		$where=array();
		$whereClause=array();
		if(isset($_GET["search"])) {
			$searchQuery =	explode(" ",$_GET["search"]);
			array_push($whereClause, 'firstName');
			array_push($whereClause, 'lastName');
			array_push($whereClause, 'address');
			$i=0;
			while($searchQuery[$i]!="") {
				for($j=0; $j<count($whereClause); $j++){
					array_push($where,$whereClause[$j]." like '%".$searchQuery[$i]."%'");
				}
				$i++;
			}	
		}

		if($where[0]!="") $whereStr=implode(" OR ",$where);
		if($whereStr!="") $whereStr="WHERE (".$whereStr.")";
		*/

		$users = User::RetrieveUsers($adminUser, $institution);

		$rowCount = 0;
		foreach ($users as $aUser) {
			$rowCount++;
			?>
			<tr class="<? print ($rowCount%2==1)?"even":"odd"?>" id="u<? print $aUser->getId(); ?>">
				<td><a href=".?do=<?php echo $do?>&amp;a=edit&amp;id=<? print( $aUser->getId() ); ?>"><? print "{$aUser->getFullName()}"; ?></a></td>
				<td><? print $aUser->getUsername(); ?></td>
				<td><? print ( ucfirst($aUser->getPermissionManager()->getUserType()) ); ?></td>
				<td><? print Date::formatForDatabase($aUser->getUpdatedTime()); ?></td>
				<td class="actionTd">
					[&nbsp;
					<a href=".?do=<?php echo $do?>&amp;a=edit&amp;id=<? print( $aUser->getId() ); ?>">Edit</a> 
					<a onclick="doDelete(<? print( $aUser->getId() ); ?>,'');" onkeyup="doDelete(<? print( $aUser->getId() ); ?>);" href="#">Delete</a>
					&nbsp;]
				</td>
			</tr>			
		<? } ?>
		</tbody>
	</table>
	<div id="pager" class="pager">
		<img src="<?php echo DIR_WS_JAVASCRIPT?>jquery/tableSorter/blue/first.png" class="first" alt="First" />
		<img src="<?php echo DIR_WS_JAVASCRIPT?>jquery/tableSorter/blue/prev.png" class="prev" alt="Previous" />
		<input type="text" class="pagedisplay"/>
		<img src="<?php echo DIR_WS_JAVASCRIPT?>jquery/tableSorter/blue/next.png" class="next" alt="Next" />
		<img src="<?php echo DIR_WS_JAVASCRIPT?>jquery/tableSorter/blue/last.png" class="last" alt="Last" />
		<select class="pagesize">
			<option selected="selected"  value="10">10</option>
			<option value="20">20</option>
			<option value="30">30</option>
			<option  value="40">40</option>
		</select>
	</div>
	</form>
<?
}

function showForm(){
	global $do, $adminUser, $userType;
	if(isset($_GET['id'])) {
		$user = User::RetrieveById($_GET['id'], $adminUser);
	}
	?>
	<style type="text/css">
	div#dlgPhotos {
		width: 650px;
	}
	</style>
	<script type="text/javascript" src="/admin/_scripts/bo-user-form.js"></script>
	<div id="dataFormContainer">
		
		<? /** User details main tab **/ ?>
		<div id="mainTabContainer" dojoType="dijit.layout.TabContainer" class="mainTabContainer" doLayout="true" style="width:95%; height:650px;">
			<div id="profileTab" dojoType="dijit.layout.ContentPane" class="tab" title="Profile">

				<form method="post" id="userform" action="." dojoType="dijit.form.Form">
				<input type="hidden" name="userid" id="userid" value="<? if(isset($user)) print $user->getId(); ?>" />
				<input type="hidden" name="do" id="do" value="<? echo $_GET["do"]?>" />
				<input type="hidden" name="a" id="a" value="User" />
				<input type="hidden" name="operation" id="operation" value="<? print ((isset($user)) ? 'Update' : 'Insert') ?>" />

				<div dojoType="dijit.Toolbar" style="clear:both;">
					<div dojoType="dijit.form.Button" onclick="doSave" showLabel="true">Save</div>
					<div dojoType="dijit.form.Button" id="btnDelete" showLabel="true"
						onclick="doDelete"
						<? if(!isset($_GET['id'])){?>style="display:none;"<? } ?>
						>Delete</div>
					<div dojoType="dijit.form.Button" onclick="doCancel" showLabel="true">Cancel</div>
				</div>		
				<table class="dataForm" id="profileTable">
					<tr>
						<td class="captionLabel">First &amp; middle name</td>
						<td><input 
								dojoType="dijit.form.ValidationTextBox" 
								type="text" 
								name="firstName" 
								id="firstName" 
								value="<? print (isset($user)) ? $user->getFirstName() : '';?>" 
								regExp="[\w\s]+" 
								required="true" 
								invalidMessage="First name cannot be empty<br />System only accept character and number" /> <? print TEXT_FIELD_REQUIRED; ?></td>
					</tr>
					<tr>
						<td class="captionLabel">Last name</td>
						<td><input 
								dojoType="dijit.form.ValidationTextBox" 
								type="text" 
								name="lastName" 
								id="lastName" 
								value="<? print (isset($user)) ? $user->getLastName() : '';?>" 
								regExp="[\w\s]+" 
								required="true" 
								invalidMessage="Last name cannot be empty<br />System only accept character and number" /> <? print TEXT_FIELD_REQUIRED; ?></td>
					</tr>
					<tr>
						<td class="captionLabel">Email</td>
						<td><input dojoType="dijit.form.ValidationTextBox" 
								type="text" 
								name="email" 
								id="email" 
								value="<? print (isset($user)) ? $user->getEmail() : ''; ?>" 
								regExp="^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$" 
								invalidMessage="Must be a valid email" /></td>
					</tr>
					<? if($adminUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN)): ?>
					<tr>
						<td class="captionLabel">Institution</td>
						<td>
							<? if (isset($user)):
								print $user->getInstitution()->getName();
							else: ?>
								<select dojoType="dijit.form.FilteringSelect" 
									name="institution_id" 
									id="institution_id" 
									required="true"
									validator="return this.getValue()!='';"
									invalidMessage="You must choose an institution">
									<option></option>
									<? 
										$institutions = Institution::RetrieveAll();
										foreach($institutions as $institution)
										{
											?><option value="<? print $institution->getId(); ?>"><? print $institution->getName(); ?></option><?
										}
									?>
								</select> <? print TEXT_FIELD_REQUIRED; ?>
							<? endif; ?>
						</td>
					</tr>
					<? endif; ?>

					<tr>
						<td class="captionLabel">Description</td>
						<td>
							<textarea 
								class="dijitTextarea" 
								dojoType="dijit.form.Textarea" 
								name="description" 
								id="description"><? print (isset($user)) ? $user->getDescription() : ''; ?></textarea>
						</td>
					</tr>
				</table>

				<h2>Login details</h2>
				<table class="dataForm" id="loginTable">
					<tr>
						<td class="captionLabel">Username</td>
						<td><input 
							dojoType="dijit.form.ValidationTextBox" 
							type="text"
							name="username"
							id="username"
							required="true"
							<? if(isset($_GET["id"])){?>disabled="disabled"<? } ?> 
							value="<? print (isset($user)) ? $user->getUsername() : ''; ?>" /> <? print TEXT_FIELD_REQUIRED; ?>
							<div id="inlineNotificationUsername" class="inlineNotification"></div></td>
					</tr>
					<?
					$passwordMessage = '';
					$passwordValue = '';
					$passwordRequired = '';
					if (isset($user)) {
						// Print password for existing students
						if( $user->getPermissionManager()->isMemberOf(PermissionManager::USER_STUDENT) ) {
							$passwordValue = 'value="' . $user->getPermissionManager()->getPassword() . '"';
						}
						else {
							$passwordMessage = '<tr><td colspan="2">Existing password is not shown for admin users.</td></tr>';
						}
					}
					else {
						// New user requires password
						$passwordRequired = 'required="true"';
					}
					
					?>
					<tr>
						<td class="captionLabel"><? if($_GET['a']=="edit") print 'Change '; ?>Password</td>
						<td><input 
							dojoType="dijit.form.ValidationTextBox" 
							type="text"
							name="password"
							id="password"
							<? print $passwordValue . $passwordRequired; ?>
							invalidMessage="Please type a password" /> <? print TEXT_FIELD_REQUIRED; ?>
							<div id="inlineNotificationPassword" class="inlineNotification"></div></td>
					</tr>
					<tr>
						<td class="captionLabel">Confirm password</td>
						<td><input 
							dojoType="dijit.form.ValidationTextBox"
							type="text"
							name="password2"
							id="password2"
							<? print $passwordValue . $passwordRequired;; ?>
							validator="return theSame(this, dijit.byId('password'));"
							invalidMessage="This password must match your first password" /> <? print TEXT_FIELD_REQUIRED; ?></td>
					</tr>
					<? print $passwordMessage; ?>
					<tr>
						<td class="captionLabel">User type</td>
						<td>
							<select 
								dojoType="dijit.form.FilteringSelect" 
								id="userType" 
								name="userType">
								<? print PermissionManager::HtmlSelectOptions($user); ?>
							</select> <?php echo TEXT_FIELD_REQUIRED?>
						</td>
					</tr>
				</table>

				<? if(isset($user)) { ?>
				<h2>Switch Login</h2>
				<span dojoType="dojo.data.ItemFileReadStore" jsId="loginShapeData" url="/system/ajax/flash/switch_loginShapes.json.php"></span>
				<span dojoType="dojo.data.ItemFileReadStore" jsId="loginPhotoData" url="/system/ajax/flash/switch_loginPhotos.json.php"></span>
				<p><label for="ppEnabled">Enabled: <input id="ppEnabled" name="ppEnabled" type="checkbox" value="yes"<? if($user->getPermissionManager()->getSymbolLogin()->isEnabled()) print 'checked="checked"'; ?> /></label></p>

				<div id="symbolpass" style="margin: 0 0 0 2em; border: 2px solid #ccc; padding: 0 20px 0 20px; width:250px;">
				<h3>Switch login symbol password</h3>
				<? print $user->getPermissionManager()->getSymbolLogin()->HtmlAdminShow(); ?>
				<ul>
				<li><a href="/admin/switch.inf.php?id=<? print $user->getId(); ?>">Download user inf file</a></li>
				<li><a href="/admin/switch.inf.php?id=<? print $user->getId(); ?>&includepass=y">Download user inf file with password included</a></li>
				</ul>
				</div>
				<? } ?>
				</form>
			</div>
			<? // Only show this tab if user has already been created
			if(isset($user)) { ?>
			<div id="activityTab" dojoType="dijit.layout.ContentPane" class="tab" title="Activity">
				<? print $user->HtmlLogActivity(); ?>
			</div>
			<div id="exportTab" dojoType="dijit.layout.ContentPane" class="tab" title="Export">
				<p>Export produces a zip file of this user's infolio. This includes their tabs, pages and all their assets.
				<strong>It can take a while to do the export (Up to a minute or possibly more).</strong></p>
				<p>Select the tabs for this user that you would like to include in the export.</p>
				<? print $user->HtmlExportProfileForm(); ?>
			</div>
			<div id="manageTabs" dojoType="dijit.layout.ContentPane" class="tab" title="Tabs">
				<p>Tabs allows an adminstrator to manage a users tabs.  The administrator can disable and enable tabs that the user will then see.</p>
				<? print $user->HtmlManageTabs(); ?>
			</div>
			<? } ?>
		</div>			
	</div>
<?
	}
