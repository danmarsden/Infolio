<?php

// This file is part of In-Folio - http://blog.in-folio.org.uk/blog/
//
// In-Folio is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// In-Folio is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with In-Folio.  If not, see <http://www.gnu.org/licenses/>.

/**
 * upgrade.php - handles db upgrade routines
 *
 * @author     Dan Marsden, Catalyst IT Ltd, (http://danmarsden.com)
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

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
    if ($result && $oldversion < 2010100100) {
        //add new share column to institution table
        $sql = "ALTER TABLE institution ADD column comment int";
        $result = $db->query($sql);
        if ($result) {
            set_config('version', '2010100100');
        }
    }
    if ($result && $oldversion < 2010100101) {
        //add new share column to institution table
        $sql = "ALTER TABLE institution ADD column commentapi varchar(100)";
        $result = $db->query($sql);
        if ($result) {
            set_config('version', '2010100101');
        }
    }
    if ($result && $oldversion < 2010100102) {
        //add new share column to institution table
        $sql = "CREATE TABLE tab_shared (userid int(11), tabid int(11), PRIMARY KEY (userid, tabid))";
        $result = $db->query($sql);
        if ($result) {
            set_config('version', '2010100102');
        }
    }
    if ($result && $oldversion < 2010120801) {
        //add new share column to institution table
        $sql = "ALTER TABLE institution ADD column limitshare int(11)";
        $result = $db->query($sql);
        if ($result) {
            set_config('version', '2010120801');
        }
    }
}
