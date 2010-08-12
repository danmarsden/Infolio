<?php
// Get this user's institution
$institution = $adminUser->getInstitution(); ?>
<style>
div.dijitDialog {
	width: 80%;
	height: 90%;
	overflow: scroll;
}
</style>


<h1><? print $institution->getName(); ?> home page</h1>
<span dojoType="dojo.data.ItemFileReadStore" jsId="assetData" url="/admin/ajax/assets.list.php?user_id=<? print $adminUser->getId(); ?>">
<input type="hidden" id="institution_id" value="<? print $institution->getId(); ?>" />

</span>
<p style="float: left; padding: 0 1em 1em 0;"><? print $institution->getAsset()->Html(Image::SIZE_BOX); ?><br />
<a onclick="onClickChangePic();">Change institution picture</a></p>
<p><strong>Number of students:</strong> <? print $institution->getNumStudents(); ?><br />
<strong>Number of users:</strong> <? print $institution->getNumUsers(); ?><br />
<strong>Number of groups:</strong> <? print $institution->getNumGroups(); ?><br />
<strong>Number of assets:</strong> <? print $institution->getNumAssets(); ?></p>

<h2>Things you can do</h2>
<dl id="section-menu">
	<dt><a href=".?do=<? print SECTION_USER; ?>">User manager</a></dt>
	<dd>View your users, their activity and create new users.</dd>
	<dt><a href=".?do=<? print SECTION_GROUP; ?>">Group manager</a></dt>
	<dd>Create groups of users to make it easier to administer assets and templates that you want to be seen by more than one person.</dd>
	<dt><a href=".?do=<? print SECTION_ASSET; ?>">Asset manager</a></dt>
	<dd>View your assets and assign them to users and groups.</dd>
	<dt><a href=".?do=<? print SECTION_TEMPLATE; ?>">Template manager</a></dt>
	<dd>Create templates that consist of a tab and the pages in that tab. These can be assigned to users and groups.</dd>
</dl>
<script type="text/javascript" src="/admin/_scripts/bo-index.js"></script>