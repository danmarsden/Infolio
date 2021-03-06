<?php
$do = Safe::request('do');
$do = !isset($do) ? 0 : $do;

?><ul class="leftMenu">
	<li<?php if($do==0)print ' id="bo-active"'; ?>><a href=".">Home</a></li>
	<li<?php if($do==SECTION_USER)print ' id="bo-active"'; ?>><a href=".?do=<?php print SECTION_USER; ?>">User manager</a></li>
	<li<?php if($do==SECTION_GROUP)print ' id="bo-active"'; ?>><a href=".?do=<?php print SECTION_GROUP; ?>">Group manager</a></li>
	<li<?php if($do==SECTION_ASSET)print ' id="bo-active"'; ?>><a href=".?do=<?php print SECTION_ASSET; ?>">Asset manager</a></li>
	<li<?php if($do==SECTION_UPLOAD_MANAGER) print ' id="bo-active"'; ?>><a href=".?do=<?php print SECTION_UPLOAD_MANAGER; ?>">Upload manager</a></li>
	<li <?php if($do==SECTION_TEMPLATE) print ' id="bo-active"'; ?>><a href=".?do=<?php print SECTION_TEMPLATE; ?>">Template manager</a></li>
    <li <?php if($do==SECTION_LEAPIMPORT) print ' id="bo-active"'; ?>><a href=".?do=<?php print SECTION_LEAPIMPORT; ?>">User Import</a></li>
</ul>
<?php if($adminUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN)): ?>
	<br /><br />
	<ul class="leftMenu">
		<li <?php if($do==SECTION_INSTITUTION) print ' id="bo-active"'; ?>><a href=".?do=<?php print SECTION_INSTITUTION; ?>">Institution manager</a></li>
        <li <?php if($do==SECTION_SITEEXPORT) print ' id="bo-active"'; ?>><a href=".?do=<?php print SECTION_SITEEXPORT; ?>">Institution migration</a></li>
		<li <?php if($do==SECTION_LOG_VIEWER) print ' id="bo-active"'; ?>><a href=".?do=<?php print SECTION_LOG_VIEWER; ?>">System log</a></li>
	</ul>
<?php endif; ?>