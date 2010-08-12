<?php

/**
 * Useful for testing ajax messages before service is written.
 */
include_once("../../system/initialise.php");
include_once('class/Logger.class.php');

foreach($_REQUEST as $requestKey=>$requestItem) {
	Logger::Write("{$requestKey} = {$requestItem}", Logger::TYPE_INFO);
}