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
    $result = true;
    if ($result && $oldversion < 2010092700) {
        //add new config table.
        $sql = "CREATE TABLE config (name varchar(20) NOT NULL, PRIMARY KEY(name), value varchar(50))";
        $result = $db->query($sql);
        if ($result) {
            set_config('version', '2010092700');
        }
    }
    if ($result && $oldversion < 2010092701) {
        echo "get here!!!";
        //add new config table.
        $sql = "ALTER TABLE institution ADD column share int";
        $result = $db->query($sql);
        if ($result) {
            set_config('version', '2010092701');
        }
    }
    if ($result && $oldversion < 2010092702) {
        //add new config table.
        $sql = "ALTER TABLE user ADD column share int";
        $db->query($sql);
        if ($result) {
            set_config('version', '2010092702');
        }
    }
}