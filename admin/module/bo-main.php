<?php

switch($do){
	case SECTION_TEMPLATE:
		$template = 'template';
		break;
	case SECTION_USER:
		$template = 'user';
		break;
	case SECTION_GROUP:
		$template = 'group';
		break;
	case SECTION_LOG_VIEWER:
		$template = 'log-viewer';
		break;
	case SECTION_ASSET:
		$template = 'asset';
		break;									
	case SECTION_INSTITUTION:
		$template = 'institution';
		break;
	case SECTION_UPLOAD_MANAGER:
		$template = 'upload-manager';
		break;
	default:
		$template = 'index';
		break;
}
include("sections/bo-{$template}.php");

print '<hr style="clear: both;" />';
Debugger::debugPrint();