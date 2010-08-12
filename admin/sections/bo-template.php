<?

include_once('model/Template.class.php');
include_once('model/Group.class.php');

//controller
if($a=="edit" || $a=="add" || $a=="delete"){
	showForm();
}
else {
 	showGrid();
}

	/**
	 * Shows a list of templates
	 */
	function showGrid()
	{
		global $adminUser;
		?>
		<script type="text/javascript" src="/admin/_scripts/bo-template-grid.js"></script>	
		<form action="." method="get">
		<input type="hidden" name="do" value="<? print SECTION_TEMPLATE; ?>" />
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
				if($institution->getId() == $chosenInstituteId) print ' selected="selected"';
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

		<table class="dataGridTable" cellspacing="1">
			<tr>
				<th>Name</th>
				<th class="actionTd"><a href="?do=1&a=add&inst=<? print $chosenInstituteId; ?>">Create</a></th>
			</tr>
			<?

			$templates = Template::RetrieveAll($adminUser, $institution);
			$i=0;
			foreach($templates as $template)
			{
				$i++;
				$editLink = '?do=' . SECTION_TEMPLATE . '&amp;a=edit&amp;id=' . $template->getId();
				?>
				<tr id="t<? print $template->getId(); ?>" class="<? print ($i%2==1) ? "even" : "odd"; ?>">
					<td><a href="<? print $editLink; ?>"><?php print $template->getTitle(); ?></a></td>
					<td class="actionTd">
						[&nbsp;
						<a href="<? print $editLink; ?>">Edit</a>
						<a onclick="doDelete(<? print $template->getId(); ?>);" onkeyup="doDelete(<?php print $template->getId(); ?>);">Delete</a>
						&nbsp;]
					</td>
				</tr>			
				<?
			}
			?>
		</table>
		</form>
		<?
	}

	/**
	 * Shows detail for chosen template
	 */
	function showForm()
	{
		global $adminUser;

		if(isset($_GET["id"])){
			$template = new Template($_GET["id"]);
			$institutionQString = "?inst={$template->getInstitution()->getId()}";;
		}
		else {
			// Check for institute
			if(isset($_GET['inst']) && is_numeric($_GET['inst']) ) {
				$instId = $_GET['inst'];
				$instField = '<input type="hidden" name="inst" id="inst" value="' . $instId . '" />';
				$institutionQString = "?inst={$instId}";
			}
			else {
				$institutionQString = '';
			}
		}
		?>
		<style type="text/css">
		div#dlgIcons {
			width: 80%;
			height: 80%;
			overflow: scroll;
		}
	</style>
		<script type="text/javascript" src="/admin/_scripts/bo-template-form.js"></script>
		
		<div class="formContainer" dojoType="dijit.layout.TabContainer" style="height:600px; width:95%;">
		<div id="dataFormContainer" dojoType="dijit.layout.ContentPane" title="Template">
			<form method="post" id="form" action=".">
			<input type="hidden" name="id" id="id" value="<?php echo (isset($template))? $template->getId() : '' ?>" />
			<input type="hidden" name="a" value="Template" />
			<input type="hidden" name="do" id="do" value="<?php print SECTION_TEMPLATE; ?>" />
			<input type="hidden" name="operation" value="<?php echo ((isset($_GET["id"])) ? 'update' : 'insert') ?>" />
			<? if(isset($instField)) print $instField; ?>
			<div dojoType="dijit.Toolbar" style="clear:both;">
				<div dojoType="dijit.form.Button" onclick="doSubmit" showLabel="true">Save</div>
				<? if(isset($_GET["id"])) { ?>
					<div dojoType="dijit.form.Button" onclick="doDelete(<? print $_GET["id"]; ?>)" showLabel="true">Delete</div>
				<? } ?>
				<div dojoType="dijit.form.Button" onclick="doCancel" showLabel="true">Cancel</div>
			</div>		
			<table class="dataForm">
				<tr>
					<td colspan="2"><div id="inlineNotification"></div></td>
				</tr>
				<tr>
					<td class="captionLabel"><label for="title">Name</label></td>
					<td><input dojoType="dijit.form.ValidationTextBox" type="text" name="title" id="title"<?php if(isset($template)) print " value=\"{$template->getTitle()}\""; ?> required="true" invalidMessage="Template title cannot be empty" /></td>
				</tr>
				<tr>
					<td class="captionLabel"><label for="description">Description</label></td>
					<td><textarea jsId="description" name="description" id="description" dojoType="dijit.form.Textarea" class="dijitTextarea" style="width:90%; height:100px;"><?php if(isset($template)) print $template->getDescription(); ?></textarea></td>
				</tr>
				
				<? if(isset($template)){ ?>
				<tr>
					<td class="captionLabel">Tab icon</td>
					<td style="padding: 2px;"><span dojoType="dojo.data.ItemFileReadStore" jsId="assetData" url="/admin/ajax/assets.list.php<? print $institutionQString; ?>"></span><?
						print $template->getTab()->getIcon()->Html(Image::SIZE_TAB_ICON, 'edit', 'tabIcon');
					?></td>
				</tr>
				<tr>
					<td class="captionLabel"><label for="locked">Locked</label></td>
					<td><input type="checkbox" name="locked" id="locked" /> A user can not add new pages to a locked template.</td>
				</tr>
				<tr>
					<td class="captionLabel">Structure</td>
					<td>						
						<div id="addItemBtn" jsid="addItemBtn" onclick="doAddPage" dojoType="dijit.form.Button" showLabel="true">Add page</div>
						<div dojoType="dijit.form.Button" id="updateItemBtn" onclick="doEditPageOrTab" jsid="updateItemBtn" showLabel="true">Edit</div>
						<div dojoType="dijit.form.Button" id="deleteItemBtn" onClick="doDeletePage" jsid="deleteItemBtn" showLabel="true">Delete</div>
						<div 
							dojoType="dojo.data.ItemFileWriteStore" 
							jsId="templateDataStore" 
							id="templateDataStore" 
							url="<?php print AJAX_DISPATCHER; ?>?a=Tab&amp;operation=view&amp;templateId=<?php print $template->getId(); ?>"></div>
						<div 
							dojoType="dijit.tree.ForestStoreModel" 
							jsId="templateModel" 
							id="templateModel" 
							store="templateDataStore" 
							rootLabel="Tab: <? print $template->getTab()->getName(); ?>"
							labelAttr="title"
							childrenAttrs="items"></div>
						<div
							dojoType="dijit.Tree"
							jsId="templateTree"
							id="templateTree"
							model="templateModel"
							onclick="doClickTemplateTree"
							labelAttr="name">
						</div>
					</td>
				</tr>
				<? } ?>
			</table>			
		</form>
		<div id="code1">
		<!-- ADD NEW TAB DIALOG -->
		<div dojoType="dijit.Dialog" title="New page" jdId="addDialog" id="addDialog">
			<div>
				<label for="itemTitle">Title: </label>
				<span dojoType="dijit.form.TextBox" required="true" trim="true" id="itemTitle" style="width:100%;" jsId="itemTitle"></span>
			</div>
			<div style="text-align:right; margin-top:1em;">
				<div dojoType="dijit.form.Button" onclick="doAddPageConfirm" showLabel="true">Save</div>
				<div dojoType="dijit.form.Button" onclick="doAddPageCancel" showLabel="true">Cancel</div>
			</div>
		</div>
		
		<!-- UPDATE TAB DIALOG -->
		<div dojoType="dijit.Dialog" title="Enter a new title" jdId="updateDialog" id="updateDialog"> 
			<div>
				<span ><label for="itemTitle">Title: </label></span>
				<span dojoType="dijit.form.TextBox" id="itemTitle2" style="width:100%;" jsId="itemTitle2"></span>
			</div>
			<div style="text-align:right; margin-top:1em;">
				<div dojoType="dijit.form.Button" onclick="doUpdatePageOrTab" showLabel="true">Update</div>
				<div dojoType="dijit.form.Button" onclick="doCancelUpdatePage" showLabel="true">Cancel</div>
			</div>
		</div>			
		
		<!-- DELETE TAB DIALOG -->
		<div dojoType="dijit.Dialog" title="Confirmation" jdId="deleteDialog" id="deleteDialog">
			<div>
				Are you sure you want to delete this page?
			</div>
			<div dojoType="dijit.form.Button" onclick="doDeletePageConfirm" showLabel="true">Yes</div>
			<div dojoType="dijit.form.Button" onclick="doDeletePageCancel" showLabel="true">No</div>
		</div>

		</div>			

		</div>
		<? if(isset($template)): ?>
		<div id="viewersFormContainer" dojoType="dijit.layout.ContentPane" title="Viewers">	
			<table><tr>
				<td valign="top" width="47%">
					Students and groups
					<select multiple="multiple" id="sourceSelect" style="width:100%;" size="7">
						<optgroup label="Students">
						<? 
							$users = User::RetrieveUsersWhoCantSeeTemplate($template, $adminUser);

							foreach($users as $user) {
								?><option id="optionSourceSelect<? print $user->getId(); ?>" value="u<? print $user->getId(); ?>" ondblclick="doAddUserToGroup(this)"><? print $user->getUsername() . ' ( ' . $user->getFirstName() . " " . $user->getLastName() . ' )'; ?></option><?
							}
						?>
						</optgroup>
						<optgroup label="Groups">
						<?
							$groups = Group::RetrieveWhoCantSeeTemplate($template, $adminUser);

							foreach($groups as $group) {
								?><option id="optionSourceSelect<? print $group->getId(); ?>" value="g<? print $group->getId(); ?>" ondblclick="doAddUserToGroup(this)"><? print $group->getTitle(); ?></option><?
							}
						?>
						</optgroup>
					</select>
				</td>
				<td valign="middle" align="center">
					<div dojoType="dijit.form.Button" onclick="doAddManyToTemplate" showLabel="true">&gt;</div><br />
					<div dojoType="dijit.form.Button" onclick="doRemoveManyFromTemplate" showLabel="true">&lt;</div>
				</td>
				<td valign="top" width="47%">
					Template viewers
					<select multiple="multiple" id="targetSelect" style="width:100%;" size="7">
						<optgroup label="Students">
						<?
							$users = User::RetrieveUsersWhoCanSeeTemplate($template, $adminUser);

							foreach($users as $user) {
								?><option id="optionSourceSelect<? print $user->getId(); ?>" value="u<? print $user->getId(); ?>" ondblclick="doAddUserToGroup(this)"><? print $user->getUsername() . ' ( ' . $user->getFirstName() . " " . $user->getLastName() . ' )'; ?></option><?
							}
						?>
						</optgroup>
						<optgroup label="Groups">
						<?
							$groups = Group::RetrieveWhoCanSeeTemplate($template, $adminUser);

							foreach($groups as $group) {
								?><option id="optionSourceSelect<? print $group->getId(); ?>" value="g<? print $group->getId(); ?>" ondblclick="doAddUserToGroup(this)"><? print $group->getTitle(); ?></option><?
							}
						?>
						</optgroup>
					</select>
				</td>
			</tr></table>	
		</div>
		<? endif; ?>
		</div>
		<?
	}
?>