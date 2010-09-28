<?php
/**
 * upgrade.php - handles db upgrade routines
 *
 * @author     Dan Marsden [danmarsden.com]
 * @copyright  2008 Rix Centre
 * @license

 /**
 *
 * @param int $oldversion
 * @return bool always true
 */
function db_main_upgrade($oldversion) {
    global $db;
    if ($oldversion < 2010092700) {
        //add new config table.
        $sql = "CREATE TABLE config (name varchar(20) NOT NULL, PRIMARY KEY(name), value varchar(50))";
        $db->query($sql);
        
        set_config('version', '2010092700');
    }
    if ($oldversion < 2010092701) {
        //add new config table.
        $sql = "alter TABLE institution add column share int";
        $db->query($sql);

        set_config('version', '2010092701');
    }
}