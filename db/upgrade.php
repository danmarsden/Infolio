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
        //add new share column to institution table
        $sql = "ALTER TABLE institution ADD column share int";
        $result = $db->query($sql);
        if ($result) {
            set_config('version', '2010092701');
        }
    }
    if ($result && $oldversion < 2010092702) {
        //add new share column to user table.
        $sql = "ALTER TABLE user ADD column share int";
        $db->query($sql);
        if ($result) {
            set_config('version', '2010092702');
        }
    }
    if ($result && $oldversion < 2010092703) {
        //add new share column to tab table.
        $sql = "ALTER TABLE tab ADD column share int";
        $db->query($sql);
        if ($result) {
            set_config('version', '2010092703');
        }
    }
    if ($result && $oldversion < 2010092704) {
        //add new sharehash column to user table.
        $sql = "ALTER TABLE user ADD column sharehash varchar(40)";
        $db->query($sql);
        if ($result) {
            set_config('version', '2010092704');
        }
    }
    if ($result && $oldversion < 2010092900) {
        // Update template tabs to have a weight of -1
        // This puts them between the About-me tab and the users tabs.
        // Template tabs are not sortable
        $sql = "UPDATE tab SET weight = '-1' WHERE template_id != 0";
        $db->query($sql);
        if ($result) {
            set_config('version', '2010092900');
        }
    }
}
