<?php

/**
 * Group admin screen
 *
 * @author     Elvir Leonard
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: bo-group.php 818 2009-11-09 12:00:12Z richard $
 */

include_once('../system/model/Group.class.php');

//controller
if($a=="edit" || $a=="add" || $a=="delete") {
	showForm();
}
else {
 	showGrid();
}

/**
 * Show a grid of groups
 * @global <type> $do
 * @global <type> $adminUser
 * @global <type> $jsFramework
 */
function showGrid(){
	global $do, $adminUser, $jsFramework;
	$jsFramework->jqueryTableSorter();
	?>
	<script type="text/javascript" src="/admin/_scripts/bo-group-grid.js"></script>
	<form action="." method="get">
	<input type="hidden" name="do" value="<?php print $do; ?>" />

	<?php
	// Admin users get to select institution they're acting on
	$institution = null;
	if( $adminUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN) ) {
		// Get id of institute to view (if one has been chosen, otherwise use default)
		$rinst = Safe::request('inst');
        $chosenInstituteId = (isset($rinst)) ?
			$rinst :
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

	<table class="dataGridTable" class="tablesorter" cellspacing="1" id="groupTable">			
		<thead>
		<tr>
			<th class="noTd"></th>
			<th>Group</th>
			<th>Size</th>
			<th>Owner</th>
			<th class="actionTd"><a href="?do=<?php print $do ?>&a=add&inst=<?php print $chosenInstituteId; ?>">Create</a></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$groups = Group::RetrieveGroups($adminUser, $institution);
		$i=0;
		foreach($groups as $group){
			$i++;
			?>
			<tr class="<?php echo ($i%2==1)?"even":"odd"?>" id="g<?php print $group->getId(); ?>">
				<td class="noTd">
					<input type="checkbox" name="groupId[]" value="<?php print $group->getId(); ?>" id="assetId<?php print $group->getId(); ?>" />
				</td>
				<td><a href="?do=<?php print $do; ?>&amp;a=edit&amp;id=<?php print $group->getId(); ?>&amp;inst=<?php print $chosenInstituteId; ?>"><?php print $group->getTitle(); ?></a></td>
				<td><?php print $group->getSize(); ?></td>
				<td><?php print $group->getCreatedBy()->getUsername(); ?></td>
				<td class="actionTd">
					[&nbsp;
					<a href="?do=<?php print $do; ?>&amp;a=edit&amp;id=<?php print $group->getId(); ?>&amp;inst=<?php print $chosenInstituteId; ?>">Edit</a>
					<a onclick="showDeleteMsg(<?php print $group->getId(); ?>);" onkeyup="showDeleteMsg(<?php print $group->getId(); ?>);">Delete</a>
					&nbsp;]
				</td>
			</tr>			
			<?php
		}
		?>
		</tbody>
	</table>
	<div id="pager" class="pager">
		<img src="<?php echo DIR_WS_JAVASCRIPT?>jquery/tableSorter/blue/first.png" class="first" alt="first"/>
		<img src="<?php echo DIR_WS_JAVASCRIPT?>jquery/tableSorter/blue/prev.png" class="prev" alt="previous"/>
		<input type="text" class="pagedisplay"/>
		<img src="<?php echo DIR_WS_JAVASCRIPT?>jquery/tableSorter/blue/next.png" class="next" alt="next"/>
		<img src="<?php echo DIR_WS_JAVASCRIPT?>jquery/tableSorter/blue/last.png" class="last" alt="last"/>
		<select class="pagesize">
			<option selected="selected"  value="10">10</option>
			<option value="20">20</option>
			<option value="30">30</option>
			<option  value="40">40</option>
		</select>
	</div>			
	</form>

	<div dojoType="dijit.Dialog" id="dialogDeleteConfirmation" title="Delete group">
		Are you sure you want to delete this group?<br /><br />
		<button dojoType="dijit.form.Button" type="submit" onclick="deleteGroup()">Yes</button>
		<button dojoType="dijit.form.Button" type="submit" onclick="hideDeleteMsg()">No</button>
	</div>		
	<?php
}

/**
 * Shows a detailed view of one group
 * @global <type> $do
 * @global <type> $adminUser
 * @global <type> $jsFramework
 */
function showForm()
{
	global $do, $adminUser, $jsFramework;
    $gid = Safe::get('id');
    $ginst = Safe::get('inst', PARAM_INT);
	if(isset($gid)) {
		$group = Group::RetrieveGroupById($gid);
	}
	else {
		// Check for institute
		if(isset($ginst) && is_numeric($ginst) ) {
			$instField = '<input type="hidden" name="inst" id="inst" value="' . $ginst . '" />';
		}
	}
	?>
	<script type="text/javascript" src="/admin/_scripts/bo-group-form.js"></script>
	<form method="post" name="form" id="form" action=".">
	<input type="hidden" name="id" id="id"<?php if(isset($group)) print ' value="' . $group->getId() . '"'; ?> />
	<input type="hidden" name="do" id="do" value="<?php print SECTION_GROUP; ?>" />
	<input type="hidden" name="operation" id="operation" value="<?php echo ((isset($group)) ? "update":"insert") ?>" />	
	<input type="hidden" name="a" id="a" value="Group" />
	<?php if(isset($instField)) print $instField; ?>
	<input type="hidden" name="stupid" value="ye sthis is" />
	<div class="tundra" id="code1" style="background-color:#f5f5f5">
	<div class="formContainer" id="tabFormContainer" dojoType="dijit.layout.TabContainer" style="width:95%; min-height:600px;">
	<div dojoType="dijit.layout.ContentPane" title="Group detail" id="tabGroupDetail" jsid="tabGroupDetail">
		<div dojoType="dijit.Toolbar" style="clear:both;">
			<div dojoType="dijit.form.Button" onclick="doSave" showLabel="true">Save</div>
			<div dojoType="dijit.form.Button" id="btnDelete1" onclick="deleteGroup()" showLabel="true"
			<?php if(!isset($group)){ print 'style="display:none;"'; } ?>
			>Delete</div>
			<div dojoType="dijit.form.Button" onclick="doCancel" showLabel="true">Cancel</div>
		</div>		
		<table class="dataForm">
			<tr>
				<td colspan="2"><div id="inlineNotification"></div></td>
			</tr>
			<tr>
				<td class="captionLabel">Group</td>
				<td><input dojoType="dijit.form.ValidationTextBox" type="text" name="title" id="title" value="<?php if (isset($group)) print $group->getTitle(); ?>" required="true" invalidMessage="Group title cannot be empty" /></td>
			</tr>
			<tr>
				<td class="captionLabel">Description</td>
				<td><textarea id="description" dojoType="dijit.form.Textarea" name="description" class="dijitTextarea" style="height:100px;"><?php if (isset($group)) print $group->getDescription(); ?></textarea></td>
			</tr>
		</table>			
	</div>
	
	<div dojoType="dijit.layout.ContentPane" title="Member" id="tabMember"<?php if(!isset($group)) print ' style="display:none;"'; ?>>
		<div dojoType="dijit.Toolbar" style="clear:both;">
			<div dojoType="dijit.form.Button" onclick="doSave" showLabel="true">Save</div>
			<div dojoType="dijit.form.Button" id="btnDelete2" onclick="deleteGroup()" showLabel="true"
			<?php if(!isset($group)){ print 'style="display:none;"'; } ?>
			>Delete</div>
			<div dojoType="dijit.form.Button" onclick="doCancel" showLabel="true">Cancel</div>
		</div>		

		<table class="dataForm" width="100%">
			<tr>
				<td>Search<br /><input type="text" id="searchKeyword" onkeyup="updateSourceByKeyword(this.value)" /><br /><br /></td>
				<td><span id="update"></span></td>
			</tr>
			<tr>
				<td valign="top" width="47%">
					Student List
					<select multiple="multiple" id="sourceSelect" style="width:100%;" size="7">
						<?php
							$fromInstitution = (isset($ginst)) ?
									Institution::RetrieveById($ginst) :
									null;

							$users = (isset($group)) ?
								User::RetrieveUsersNotInGroup($group, $adminUser, $fromInstitution) :
								User::RetrieveUsers($adminUser, $fromInstitution);

							foreach($users as $user) {
								?><option id="optionSourceSelect<?php print $user->getId(); ?>" value="<?php print $user->getId(); ?>" ondblclick="doAddUserToGroup(this)"><?php print $user->getUsername() . ' ( ' . $user->getFirstName() . " " . $user->getLastName() . ' )'; ?></option><?
							}
						?>
					</select>
				</td>
				<td valign="middle" align="center">
					<div dojoType="dijit.form.Button" onclick="doAddUsersToGroup" showLabel="true">&gt;</div><br />
					<div dojoType="dijit.form.Button" onclick="doRemoveUsersFromGroup" showLabel="true">&lt;</div>
				</td>
				<td valign="top" width="47%">
					Group members
					<select id="targetSelect" name="targetSelect[]" multiple="multiple" style="width:100%;" size="7">
						<?php if(isset($group)) {
							$users = User::RetrieveUsersInGroup($group, $adminUser);

							foreach($users as $user) {
								?><option id="target<?php print $user->getId(); ?>" value="<?php print $user->getId(); ?>" ondblclick="doRemoveUserFromGroup(this)"><?php print $user->getUsername() . ' ( ' . $user->getFirstName() . " " . $user->getLastName() . ' )'; ?></option><?php
							}
						} ?>						
					</select>
				</td>
			</tr>
		</table>
		<p>Double click or use arrows to move users.</p>
	</div>	
		
	<div dojoType="dijit.Dialog" id="dialogDeleteConfirmation" title="Delete group">
		Are you sure you want to delete this group?<br /><br />
		<button dojoType="dijit.form.Button" type="submit" onclick="deleteGroup()">Yes</button>
		<button dojoType="dijit.form.Button" type="submit" onclick="hideDeleteMsg()">No</button>
	</div>		
</div></div>	
</form>
<?php
}