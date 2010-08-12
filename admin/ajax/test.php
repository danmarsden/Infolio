<?php

/**
 * Useful for testing ajax messages before service is written.
 */

?><html>
	<body>
	<ul>
	<?php
	foreach($_REQUEST as $requestKey=>$requestItem) {
		print "<li>{$requestKey} = {$requestItem}</li>";
	}
	?>
	</ul>
	</body>
</html>