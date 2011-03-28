<?php
include_once('../system/model/Asset.class.php');

$uploader = new Uploader('Upload a new asset', UPLOAD_LIMIT);

?>
<form action="." method="get">
<input type="hidden" name="do" value="<?php print $do; ?>" />

<div style="width:25%; float:left;">
<?
	// Admin users get to select institution they're acting on
	$institution = null;
	$institutionQString = '';
	if( $adminUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN) ) {
		// Get id of institute to view (if one has been chosen, otherwise use default)
		$chosenInstituteId = (isset($_REQUEST['inst'])) ?
			$_REQUEST['inst'] :
			$adminUser->getInstitution()->getId();

		print '<select id="iInst" name="inst">';
		$institutions = Institution::RetrieveAll();
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
			$institutionQString = "&inst={$chosenInstituteId}";
		}

		print '<input type="submit" value="Show" />';
	}

	// Set institution for everyone else
	if(!isset($institution)) {
		$institution = $adminUser->getInstitution();
	}
?>
<br /><label for="iFilterTags">Tags:<br /><select id="iFilterTags" style="width:60%">
	<option value="">All tags</option>
		<?php // Get possible tags for this institute
		$tags = Tag::RetrieveByInstitution($institution);
		foreach($tags as $tag) {
		print "<option value=\"{$tag->getName()}\">{$tag->getName()}</option>";
	}
	?>
</select></label>
<div dojoType="dijit.form.Button" jsId="bAddTags" onclick="onFilterTags" showLabel="true">Filter</div>
<div class="formContainer" id="tabFormContainer" dojoType="dijit.layout.TabContainer" style="height:460px;">

	<div dojoType="dijit.layout.ContentPane" title="Users" id="tabUsers" jsid="tabUsers">
		<span dojoType="dojo.data.ItemFileReadStore" jsId="userData" url="/admin/ajax/users.list.php?z=z<?php print $institutionQString; ?>"></span>
		<span dojoType="widgets.DataDropList" jsId="userList" store="userData" type="'user'" onItemsDropped="onDrop" onItemClick="onUserClick"></span>
	</div>
	
	<div dojoType="dijit.layout.ContentPane" title="Groups" id="tabGroups" jsid="tabGroups">
		<span dojoType="dojo.data.ItemFileReadStore" jsId="groupData" url="/admin/ajax/groups.list.php?z=z<?php print $institutionQString; ?>"></span>
		<span dojoType="widgets.DataDropList" jsId="groupList" store="groupData" type="'group'" onItemsDropped="onDrop"  onItemClick="onGroupClick"></span>
	</div>
</div>
<div dojoType="dijit.Toolbar">
	<div dojoType="dijit.form.Button" id="bFilterMine" onclick="onFilterMine" showLabel="true">My assets</div><br />
	<div dojoType="dijit.form.Button" id="bFilterRecent" onclick="onFilterRecent" showLabel="true">Recent uploads</div><br />
	<div dojoType="dijit.form.Button" id="bFilterUnassigned" onclick="onFilterUnassigned" showLabel="true">Unassigned assets</div><br />
	<div dojoType="dijit.form.Button" id="bFilterAll" onclick="onFilterAll" showLabel="true">All</div>
</div>
</div>
<div style="width: 74%; float: right;">
	<div dojoType="dijit.Toolbar" style="clear:both;">
		<strong>Sort:</strong>
		<span dojoType="dijit.ToolbarSeparator"></span>
		<div dojoType="dijit.form.Button" onclick="onSortDate" showLabel="true">Date</div>
		<div dojoType="dijit.form.Button" onclick="onSortName" showLabel="true">Name</div>
	</div>
	<div style="height: 550px; overflow-y: scroll;">
	<span dojoType="dojo.data.ItemFileReadStore" jsId="assetData" url="/admin/ajax/assets.list.php?filter=mine<?php print $institutionQString; ?>"></span>
	<div dojoType="widgets.ImageList" jsId="assetThumbnails" store="assetData" imagePath="" onSelect="onAssetSelect"></div>
	</div>
</div>

<?php /* Properties box */

$emptyPicture = Image::GetPlaceHolder();

?>
<div style="border: 2px solid #666; background-color: #ccc; clear: both; padding: 3px;">
	<table id="propertyTable" width="100%">
	<tr>
		<td rowspan="4" width="110"><?php print $emptyPicture->Html(Image::SIZE_THUMBNAIL, null, "SelectedImage"); ?></td>
		<th scope="row" width="10%">Title</th>
		<td width="40%" id="title"></td>
		<th scope="row" width="10%">Date</th>
		<td id="date"></td>
	</tr>
	<tr>
		<th scope="row">Description</th>
		<td id="description"></td>
		<th scope="row">Owner</th>
		<td id="owner"></td>
	</tr>
	<tr>
		<th scope="row">Tags</th>
		<td id="tags"></td>
		<th scope="row">Public</th>
		<td><input type="checkbox" id="cbPublic" /></td>
	</tr>
	<tr>
		<td colspan="2"><a href="#tags" id="lnkAddTags">Add tags</a><br />
		<div jsid="paneTagEdit" dojoType="dijit.layout.ContentPane">
			<label for="iNewTags">New tags: <select jsid="iNewTags" id="iNewTags" dojoType="dijit.form.ComboBox">
			<option></option>
			<?php // Get possible tags for this institute
			foreach($tags as $tag) {
				print "<option>{$tag->getName()}</option>";
			}
			?>
			</select></label>
			<div dojoType="dijit.form.Button" jsId="bAddTags" onclick="onAddTags" showLabel="true">Add</div>
		</div>
		</td>
		<td colspan="2" rowspan="2">
			<div dojoType="dijit.form.Button" jsId="bWhoCan" onclick="onFindWhoCanSee" showLabel="true">Who can see this?</div>
			<div dojoType="dijit.form.Button" jsId="bRemove" onclick="onRemove" showLabel="true">Remove from <span id="userOrGroupName">User/Group</span></div>
		</td>
	</tr>
	</table>
</div>
</form>
<h2>Upload new asset</h2>
<form action="." method="post" enctype="multipart/form-data">
<input type="hidden" name="do" value="<?php print $do; ?>" />
<input type="hidden" value="upload" name="a" />
<?php print $uploader->HtmlUploadForm('upload', '.')?>
</form>

<?php // Delete older assets for super asset user
if( $adminUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN) )
{
	print "<h2>These assets have been removed and are more than 3 months old</h2><ul>";
	$removedAssets =Asset::RetrieveRemovedAndOlderThan3Months($institution, $adminUser);
	foreach($removedAssets as $rAsset) {
		print '<li><strong>' . $rAsset->getTitle() . '</strong> &ndash; ' . $rAsset->getHref() .'</li>';
	}
	print "</ul>";
}
?>

<script type="text/javascript" src="/admin/_scripts/bo-asset.js"></script>