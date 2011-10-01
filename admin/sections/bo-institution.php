<?php

/**
 * Institution admin section
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Elvir Leonard
 * @copyright  2008 Rix Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @version    $Id: bo-institution.php 818 2009-11-09 12:00:12Z richard $
 * @link       NA
 * @since      NA
 */

include_once('model/Institution.class.php');

//controller
switch($a) {
	case 'edit':
	case 'add':
	case 'delete':
		showForm();
		break;
	default:
	 	showGrid();
		break;
}

/**
 * Shows list of institutions
 */
function showGrid()
{
?>
	<script type="text/javascript" src="/admin/_scripts/bo-institution-grid.js"></script>
	<div dojoType="dijit.Dialog" id="dialogDeleteConfirmation" title="Delete tab_school">
		<p>Are you sure you want to delete this institution?</p>
		<button dojoType="dijit.form.Button" type="submit" onclick="onDeleteConfirm()">Yes</button>
		<button dojoType="dijit.form.Button" type="submit" onclick="onDeleteCancel()">No</button>
	</div>		
	<form action="." method="get">
	<table class="dataGridTable" cellspacing="1">
		<tr>
			<th>Name</th>
			<th class="actionTd"><a href="?do=<?php print SECTION_INSTITUTION; ?>&a=add">Create</a></th>
		</tr>
		<?php

		$institutions = Institution::RetrieveAll();
		$i = 0;
		foreach($institutions as $institution){
			$i++;
			?>
			<tr id="i<?php print($institution->getId()); ?>" class="<?php print($i%2==1)?"even":"odd"?>">
				<td><a href="?do=<?php print SECTION_INSTITUTION; ?>&amp;a=edit&amp;id=<?php print $institution->getid(); ?>"><?php print $institution->getName(); ?></a></td>
				<td class="actionTd">
					<a href="?do=<?php print SECTION_INSTITUTION; ?>&amp;a=edit&amp;id=<?php print $institution->getid(); ?>">Edit</a>,
					<a onclick="onDelete(<?php print $institution->getid(); ?>);" onkeyup="onDelete(<?php print $institution->getid(); ?>);">Delete</a>
				</td>
			</tr>			
			<?php
		}
		?>
	</table>
	</form>
	<?php
}

/**
 * Show edit/create form for institution
 */
function showForm()
{
    $gid = Safe::get('id', PARAM_INT);
	if(isset($gid)){
		$institution =	Institution::RetrieveById($gid);
	}
	?>
	<script type="text/javascript" src="/admin/_scripts/bo-institution-form.js"></script>


	<div class="formContainer" dojoType="dijit.layout.TabContainer" style="height:600px; width:95%;">
		<div id="dataFormContainer55" dojoType="dijit.layout.ContentPane" title="Institution">
			<form method="post" id="form" action="." dojoType="dijit.form.Form">
				<input type="hidden" name="id" id="id" value="<?php print (isset($institution)) ? $institution->getId():""?>" />
				<input type="hidden" name="do" id="do" value="<?php print SECTION_INSTITUTION; ?>" />
				<input type="hidden" name="a" id="a" value="<?php print ((isset($institution)) ? 'Update' : 'Insert' ) ?>" />
				<div dojoType="dijit.Toolbar" style="clear:both;">
					<div dojoType="dijit.form.Button" onclick="doSubmit" showLabel="true">Save</div>
					<?php if(isset($gid)){?>
					<div dojoType="dijit.form.Button" onclick="deleteRow(dojo.byId('id').value,'')" showLabel="true">Delete</div>
					<?php } ?>
					<div dojoType="dijit.form.Button" onclick="doCancel" showLabel="true">Cancel</div>
				</div>
				<table class="dataForm">
					<tr>
						<td colspan="2"><div id="inlineNotification"></div></td>
					</tr>
					<tr>
						<td class="captionLabel">Name</td>
						<td><input dojoType="dijit.form.ValidationTextBox" type="text" name="name" id="name" value="<?php if(isset($institution))print $institution->getName(); ?>" required="true" invalidMessage="Can't be empty" /></td>
					</tr>
					<tr>
						<td class="captionLabel">Short name for URL</td>
						<td><input dojoType="dijit.form.ValidationTextBox" type="text" name="url" id="url" value="<?php if(isset($institution))print $institution->getUrl(); ?>" required="true" regExp="[\w\d_\-]+" invalidMessage="Can't be empty and must only contain letters, numbers and _ or -" /></td>
					</tr>
                    <tr>
                        <td class="captionLabel">Tab Sharing</td>
                        <td><select name="share" id="share">
                            <option value="0">Disabled</option>
                            <option value="1" <?php if(isset($institution) && $institution->allowSharing()=='1')print 'selected' ?>>Enabled - Users disabled by default</option>
                            <option value="2" <?php if(isset($institution) && $institution->allowSharing()=='2')print 'selected' ?>>Enabled - Users enabled by default</option>
                        </select></td>
                    </tr>
                   <tr>
                        <td class="captionLabel">Limit Sharing</td>
                         <td><select name="limitshare" id="limitshare">
                             <option value="0" <?php if(isset($institution) && $institution->limitShare()=='0')print 'selected' ?>>Share with everyone in the institution</option>
                             <option value="1" <?php if(isset($institution) && $institution->limitShare()=='1')print 'selected' ?>>Only share with Teachers and Admins</option>
                          </select></td>
                    </tr>
                    <tr>
                        <td class="captionLabel">Allow Comments</td>
                        <td><select name="comment" id="comment">
                            <option value="0">Disabled</option>
                            <option value="1" <?php if(isset($institution) && $institution->getComment()=='1')print 'selected' ?>>Enabled</option>
                        </select></td>
                    </tr>
      				<tr>
						<td class="captionLabel">Comment/Intensedebate API Key</td>
						<td><input dojoType="dijit.form.ValidationTextBox" type="text" name="commentapi" id="commentapi" value="<?php if(isset($institution))print $institution->getCommentApi(); ?>" </td>
					</tr>
				</table>
			</form>
            <p>To enable comments you must set up an account at http://intensedebate.com/ (you can use the same API key for each institution or separate api keys to allow flexibility)</p>
		</div>		
	</div>
	<?php
}
