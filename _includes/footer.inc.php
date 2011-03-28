<div id="footer">
	<?php if(!empty($studentUser) && $studentUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_ALL_ADMIN)) echo('<a href="admin/">Admin</a>'); ?>
</div>