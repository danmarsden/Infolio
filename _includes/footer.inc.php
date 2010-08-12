<div id="footer">
	<? if($studentUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN)) echo('<a href="admin/">Admin</a>'); ?>
</div>