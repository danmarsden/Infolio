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
 * lib.php - Creates a LEAP export of a user's infolio
 * See http://wiki.cetis.ac.uk/LEAP2A_specification
 *

 *
 * @author     Dan Marsden, Catalyst IT Ltd
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/


function export_portfolio($studentUser, $tabIds, $returnfile=false, $password=false) {
    global $userTabs, $userAssetCollection, $userAssets, $tabId, $pageId;
    $includeOriginalAssets = true;
    $exporttime = time();
    $exportdir =  DIR_FS_ROOT . 'data/export/leap/tmp/user-' . $studentUser->getId();
    
    if (!check_dir_exists($exportdir."/files")) {
            echo "Failed to create directories";
            die;
    }
    $zipfilename = 'data/export/leapinfolio-export-leap-user'. $studentUser->getId() . '-' . $exporttime . '.zip';
    
    //get content for leap xml
    $leapxml = leap_header($studentUser, $exporttime);
    $leapxml .= leap_author($studentUser, $password);
    foreach ($userTabs as $aTab)  {
        $tabId = $aTab->getId();
        $aTab->setViewer($studentUser);
        if($tabId != 1 && (!isset($tabIds) || in_array($tabId, $tabIds))) {
            $templatetabid = '';
            $entry = new stdClass();
            $entry->title = $aTab->getName();
            $entry->contenttype = 'html';
            $entry->id = "portfolio:collection".$aTab->getId();
            $entry->leaptype = 'selection';
            $leapxml .= leap_entry($entry);
            $tabPages = $aTab->getPages();
            $templatetab = $aTab->getTemplate();
            if (!empty($templatetab)) {
                $templatetabid = $templatetab->getId();
            }
            $icon = $aTab->getIcon();
            if (!empty($icon)) {
                $leapxml .= "<infolio:icon>".$icon->getId()."</infolio:icon>";
            }
            foreach($tabPages as $aPage) {
                $leapxml .= "<link rel=\"has_part\" href=\"portfolio:view".$aPage->getId()."\"/>";
                if (!empty($templatetabid)) {
                    $leapxml .= "<infolio:template>".$templatetabid."</infolio:template>";
                }

            }
            $leapxml .= leap_entryfooter();

            //now do actual pages.
            foreach($tabPages as $aPage) {
                //TODO: optimise leap_blocks and export_pages - they both use the same sql query
                $entry->title = $aPage->getTitle();
                $entry->id = "portfolio:view".$aPage->getId();
                $entry->contenttype = 'html';
                $entry->content = leap_blocks($aPage, $studentUser);
                $entry->leaptype = 'selection';
                $leapxml .= leap_entry($entry);
                $leapxml .= export_pages($aPage, $studentUser);
                $istemplate = $aPage->templateControlled();
                if ($istemplate) {
                    $leapxml .= "<infolio:templatepage>true</infolio:templatepage>";
                }

                $leapxml .= leap_entryfooter();
            }
        }
    }
// Copy all user's assets
    if (!empty($userAssets)) {
        foreach($userAssets as $asset) {
            $newname = str_replace('&','-',$asset->getHref());
            $srcPath = $asset->getSystemFolder() . $asset->getHref();
            $dstPath = "$exportdir/files/{$asset->getType()}_{$newname}";
            copy($srcPath, $dstPath);
            $res = new stdClass();
            $res->contenttype = $asset->getType();
            $res->url = $asset->getType().'_'.$newname;
            $res->id  = $asset->getId();
            $resource = leap_resource($res, $studentUser);
            $entry = new stdClass();
            $entry->content = '';
            $entry->title = $asset->getTitle();
            $entry->id = "portfolio:artefact".$asset->getId();
            $entry->contenttype = 'text';
            $entry->resource = $resource;
            $leapxml .= leap_entry($entry);
            $leapxml .= leap_entryfooter();
        }
    }
    
    $leapxml .= leap_footer();
    //now save leap xml
    $leapfilename = "leap2a.xml";
    $fp = fopen($exportdir."/".$leapfilename,"w");
    fwrite($fp,$leapxml);
    fclose($fp);

    //now zip tmp dir and create Leap object
    // Delete old zip file (stops files from last export being kept)
    if(file_exists($zipfilename)) {
        unlink($zipfilename);
    }

    // Add it all to a zip
    $zip = new Zipper();

    if ($zip->open($zipfilename, ZIPARCHIVE::CREATE)!==TRUE) {
        exit("cannot open <$zipfilename>\n");
    }
    $zip->addDirectory(DIR_FS_ROOT . 'data/export/leap/tmp/user-' . $studentUser->getId());
    $zip->close();
    
    if ($returnfile) {
        return $zipfilename;
    } else {
        send_temp_file($zipfilename, 'leapinfolio-export-leap-user'. $studentUser->getId() . '-' . $exporttime . '.zip');
    }
}
function export_institutions($institutionid) {
    if (!empty($institutionid)) {
        $sql = "SELECT * FROM institution WHERE id=".$institutionid;
    } else {
        $sql = "SELECT * FROM institution";
    }
    $db = Database::getInstance();
    $result = $db->query($sql);
    if (empty($result)) {
        return '';
    }
    $output = '<?xml version="1.0" encoding="UTF-8"?>
<institutions>
';
    while ($row = mysql_fetch_assoc($result)) {
        $output .= '<institution id="'.$row['id'].'">
    <name>'.cleanforxml($row['name']).'</name>
    <url>'.cleanforxml($row['url']).'</url>
    <asset_id>'.$row['asset_id'].'</asset_id>';
        $output .= '
 </institution>';
    }
    $output .='
</institutions>';
    return $output;
}

function export_groups($institutionid) {
    if (!empty($institutionid)) {
        $sql = "SELECT g.*, i.url FROM groups g, institution i WHERE g.institution_id=i.id AND i.id=".$institutionid;
    } else {
        $sql = "SELECT g.*, i.url FROM groups g, institution i WHERE g.institution_id=i.id";
    }
    $db = Database::getInstance();
    $result = $db->query($sql);
    if (empty($result)) {
        return '';
    }
    $output = '<?xml version="1.0" encoding="UTF-8"?>
<groups>
';
    while ($row = mysql_fetch_assoc($result)) {
        $output .= '<group id="'.$row['id'].'">
    <title>'.cleanforxml($row['title']).'</title>
    <description>'.cleanforxml($row['description']).'</description>
    <institution>'.cleanforxml($row['url']).'</institution>';
    //now get group users
    $sql = "SELECT u.email FROM group_members g, user u WHERE g.user_id=u.ID AND g.group_id=".$row['id'];
    $resultmembers = $db->query($sql);
    if (!empty($resultmembers)) {
        $output .= "<members>";
        While ($rowm = mysql_fetch_assoc($resultmembers)) {
            $output .= $rowm['email'].',';
        }
        $output .= "</members>";
    }
        $output .= '
 </group>';
    }
    $output .='
</groups>';
    return $output;
}

function export_templates($institutionid) {
    if (!empty($institutionid)) {
        $sql = "SELECT t.*, i.url FROM templates t, institution i WHERE t.institution_id=i.id AND t.enabled=1 AND i.id=".$institutionid;
    } else {
        $sql = "SELECT t.*, i.url FROM templates t, institution i WHERE t.institution_id=i.id AND t.enabled=1";
    }
    $db = Database::getInstance();
    $result = $db->query($sql);
    if (empty($result)) {
        return '';
    }
    $output = '<?xml version="1.0" encoding="UTF-8"?>
<templates>
';
    while ($row = mysql_fetch_assoc($result)) {
        $output .= '<template id="'.$row['id'].'">
    <title>'.cleanforxml($row['title']).'</title>
    <description>'.cleanforxml($row['description']).'</description>
    <institution>'.cleanforxml($row['url']).'</institution>
    <locked>'.$row['locked'].'</locked>';
        //now get group viewers assigned to this template
        $sql = "SELECT t.*, g.title FROM template_viewers t, groups g WHERE t.group_id= g.id AND t.template_id=".$row['id'];
        $resultviewers = $db->query($sql);
        if (!empty($resultviewers)) {
            $output .= "<viewergroups>";
            While ($rowgroup = mysql_fetch_assoc($resultviewers)) {
                $output .= cleanforxml($rowgroup['title']).',';
            }
            $output .= "</viewergroups>";

        }
        //now get user viewers assigned to this template.
        $sql = "SELECT u.email FROM template_viewers t, user u WHERE t.user_id= u.ID AND t.template_id=".$row['id'];
        $resultviewers = $db->query($sql);
        if (!empty($resultviewers)) {
            $output .= "<viewerusers>";
            While ($rowgroup = mysql_fetch_assoc($resultviewers)) {
                $output .= $rowgroup['email'].',';
            }
            $output .= "</viewerusers>";

        }

        //now get tab associated with this template
        $sql = "SELECT * from tab WHERE enabled=1 AND template_id=".$row['id'];
        $resulttab = $db->query($sql);
        if (!empty($resulttab)) {
            $output .= "<tabs>";
            While ($rowtab = mysql_fetch_assoc($resulttab)) {
                $output .= '<tab id="'.$rowtab['ID'].'">
                <name>'.cleanforxml($rowtab['name']).'</name>
                <description>'.cleanforxml($rowtab['description']).'</description>';
                //now get pages associated with this tab
                $sql = "SELECT * from page WHERE enabled=1 AND user_id IS NULL AND tab_id=".$rowtab['ID'];
                $result2 = $db->query($sql);
                if (!empty($result2)) {
                    $output .= "<pages>";
                    While ($row2 = mysql_fetch_assoc($result2)) {
                        $output .= '<page id="'.$row2['id'].'">
                        <title>'.cleanforxml($row2['title']).'</title>
                        </page>';
                    }
                    $output .= "</pages>";
                }

                $output .= '</tab>';

            }
            $output .= "</tabs>";
        }
        //now get pages associated with this template
        $output .= '
 </template>';
    }
    $output .='
</templates>';
    return $output;
}

function export_pages($page, $studentUser) {
    $output = '<infolio:view infolio:type="portfolio">'; 
    $sql = "SELECT * FROM block WHERE page_id='".$page->getId()."' AND user_id='". $studentUser->getId()."'";
    $db = Database::getInstance();
    $result = $db->query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $output .= '<infolio:blockinstance>';
        $output .= '<infolio:blocktitle>'.cleanforxml($row['title']).'</infolio:blocktitle>';
        $output .= '<infolio:words0>'.cleanforxml($row['words0']).'</infolio:words0>';
        $output .= '<infolio:words1>'.cleanforxml($row['words1']).'</infolio:words1>';
        $output .= '<infolio:picture0>'.$row['picture0'].'</infolio:picture0>';
        $output .= '<infolio:picture1>'.$row['picture1'].'</infolio:picture1>';
        $output .= '<infolio:layout>'.$row['block_layout_id'].'</infolio:layout>';
        $output .= '</infolio:blockinstance>';
    }
    $output .= "</infolio:view>";
    return $output;
}

/**
 * Function to check if a directory exists and optionally create it. (copied from Moodle)
 *
 * @param string absolute directory path
 * @param boolean create directory if does not exist
 * @param boolean create directory recursively
 *
 * @return boolean true if directory exists or created
 */
function check_dir_exists($dir, $create=true, $recursive=true) {

    $status = true;

    if(!is_dir($dir)) {
        if (!$create) {
            $status = false;
        } else {
            umask(0000);
            if ($recursive) {
            /// We are going to make it recursive under DIR_FS_ROOT only
            $dir = str_replace(DIR_FS_ROOT, '', $dir);
            /// (will help sites running open_basedir security and others)
            /// PHP 5.0 has recursive mkdir parameter, but 4.x does not :-(
                $dirs = explode('/', $dir); /// Extract path parts
            /// Iterate over each part with start point DIR_FS_ROOT
                $dir = DIR_FS_ROOT;
                foreach ($dirs as $part) {
                    if ($part == '') {
                        continue;
                    }
                    $dir .= $part.'/';
                    if (!is_dir($dir)) {
                        if (!mkdir($dir, 00777)) {
                            $status = false;
                            break;
                        }
                    }
                }
            } else {
                $status = mkdir($dir, 00777);
            }
        }
    }
    return $status;
}
class Zipper extends ZipArchive
{
    public function addDirectory($path, $baseDirName="")
    {
        $this->addDir($path, $path, $baseDirName);
    }

    private function addDir($path, $rootPath, $baseDirName)
    {
        ///$zipPath = '/' . $baseDirName . str_replace($rootPath, '', $path);

       // $this->addEmptyDir($zipPath);
        $nodes = glob($path . '/*');
        foreach ($nodes as $node)
        {
            if (is_dir($node))
            {
                $this->addDir($node, $rootPath, $baseDirName);
            }
            else if
            (is_file($node))  {
                $fileName = $baseDirName . str_replace($rootPath.'/', '', $node);
                $this->addFile($node, $fileName);
            }
        }
    }

}
function cleanforxml($string) {
    return htmlspecialchars($string);
}
