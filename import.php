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
 * import.php - import of Infolio Leap file.
 *
 * @author     Dan Marsden, Catalyst IT Ltd, (http://danmarsden.com)
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

// This file can take a long time to run, so needed to expand the timeout
set_time_limit(360);

include_once("system/initialise.php");
include_once("model/User.class.php");
include_once("model/Group.class.php");
include_once("function/shared.php");
include_once("function/core.php");

$uploaddir = DIR_FS_ROOT.'data/import';

// Check user is logged in before letting them do stuff (except logging in)
$adminUser = require_admin();
$templateids = array();
$groupmembers = array();
$templateviewerusers = array();
$returnurl = $adminUser->getInstitution()->getUrl() . '/' . DIR_WS_ADMIN . '?do=' . SECTION_LEAPIMPORT;
if (empty($_FILES['leapimport'])) {
    error("please select a LEAP export file to submit", $returnurl);
}
$file = $_FILES['leapimport'];
//now check if the user has submitted a zip file.
if (substr(strrchr($file['name'], '.'), 1) !== 'zip') {
    error("Invalid LEAP export provided", $returnurl);
}
if (!is_dir($uploaddir)) {
    mkdir($uploaddir);
}

//now extract files.
$zip = new ZipArchive();
if (!$zip->open($file['tmp_name'])) {
    error("couldn't open zip", $returnurl);
}
$uploaddir .= '/'.time();
if (!is_dir($uploaddir)) {
    mkdir($uploaddir);
}
$zip->extractTo($uploaddir);

//if this is a site export, unzip each item into it's own dir and run import
$pt = Safe::post('type');
if ($pt =='site') {

    //handle Institutions xml first
    if (file_exists($uploaddir.'/institution.xml')) {
        $options =
            LIBXML_COMPACT |    // Reported to greatly speed XML parsing
            LIBXML_NONET        // Disable network access - security check
            ;
        if (!$xml = simplexml_load_file($uploaddir.'/institution.xml', 'SimpleXMLElement', $options)) {
            error("invalid institution xml", $returnurl);
        }
        foreach ($xml->institution as $institution) {
            $insurl = (string)$institution->url[0];
            $sql = "SELECT * from institution where url='$insurl'";
            $result = $db->query($sql);
            $row = mysql_fetch_assoc($result);
            if (empty($row)) {
                //need to create this institution
                $newInstitution = new Institution();
			    $newInstitution->setName((string)$institution->name[0]);
			    $newInstitution->m_url = $insurl;
                //$newInstitution->setSharing($requestData['share']);
                //$newInstitution->setComment($requestData['comment']);
                //$newInstitution->setCommentApi($requestData['commentapi']);
			    $newInstitution->CreateFolders();
			    $newInstitution->Save($adminUser);
            } else {
                add_error_msg("The institution {$institution->name[0]} already exists");
            }
        }
    } else {
        add_info_msg('no valid institution.xml to load institutions from');
    }

    if (file_exists($uploaddir.'/group.xml')) {
        $options =
            LIBXML_COMPACT |    // Reported to greatly speed XML parsing
            LIBXML_NONET        // Disable network access - security check
            ;
        if (!$xml = simplexml_load_file($uploaddir.'/group.xml', 'SimpleXMLElement', $options)) {
            error("invalid group xml", $returnurl);
        }
        foreach ($xml->group as $group) {
            $insurl = (string)$group->institution[0];
            //get Institution id
            //first get id for this institution
            $sql = "SELECT id from institution WHERE url='$insurl'";
            $result2 = $db->query($sql);
            $row2 = mysql_fetch_assoc($result2);
            $insid = $row2['id'];
            if (empty($insid)) {
                add_error_msg("The group {$group->title[0]} could not be created as the institution wasn't found");
                continue;
            }

            $sql = "SELECT g.*, i.url FROM groups g, institution i WHERE url='$insurl' AND i.id=g.institution_id AND g.title='".(string)$group->title[0]."'";
            $result = $db->query($sql);
            $row = mysql_fetch_assoc($result);
            if (empty($row)) {
                    $data = array(
                              'title' => (string)$group->title[0],
                              'description' => (string)$group->description[0],
                              'institution_id' => $insid,
                              'created_by' => $adminUser->getId(),
                              'updated_by' => $adminUser->getId(),
                              'created_time' => Date::formatForDatabase(time()),
                              'updated_time' => Date::formatForDatabase(time())
                          );
                    // Write to DB
                    $db = Database::getInstance();
                    $db->perform('groups', $data);

            } else {
                add_error_msg("The group {$group->title[0]} already exists");
            }
            //save member list for this group for later use:
            $groupmembers[$insid][(string)$group->title[0]] = (string)$group->members[0];
        }
    } else {
        add_info_msg('no valid group.xml to load groups from');
    }

    if (file_exists($uploaddir.'/template.xml')) {
        $options =
            LIBXML_COMPACT |    // Reported to greatly speed XML parsing
            LIBXML_NONET        // Disable network access - security check
            ;
        if (!$xml = simplexml_load_file($uploaddir.'/template.xml', 'SimpleXMLElement', $options)) {
            error("invalid group xml", $returnurl);
        }
        foreach ($xml->template as $template) {
            $insurl = (string)$template->institution[0];
            $sql = "SELECT t.*, i.url FROM templates t, institution i WHERE i.url='$insurl' AND i.id=t.institution_id AND t.title='".
                    (string)$template->title[0]."' AND t.description='".(string)$template->description[0]."'";
            $result = $db->query($sql);
            $row = mysql_fetch_assoc($result);
            if (empty($row)) {
                //need to create this template
                //first get id for this institution
                $sql = "SELECT id from institution WHERE url='$insurl'";
                $result2 = $db->query($sql);
                $row2 = mysql_fetch_assoc($result2);
                if (empty($row2)) {
                    add_error_msg("The template {$template->title[0]} could not be created");
                    continue;
                } else {
                    $institution = Institution::RetrieveById($row2['id']);
                    $templateobj = Template::CreateNew((string)$template->title[0], (string)$template->description[0], $institution);
                    $templateobj->setLocked((string)$template->locked[0]);

                    $templateobj->Save($adminUser);
                    //now get new ID for this template
                    $sql = "SELECT t.id, i.url FROM templates t, institution i WHERE i.url='$insurl' AND i.id=t.institution_id AND t.title='".
                             (string)$template->title[0]."' AND t.description='".(string)$template->description[0]."'";
                    $result3 = $db->query($sql);
                    if ($rowtm = mysql_fetch_assoc($result3)) {
                        $templateids[(int)$template->attributes()->id[0]] = $rowtm['id'];
                    }
                    //now process pages within this template
                    foreach ($template->xpath('//page') as $p) {
                        $page = new Page();
				        $page->setTitle((string)$p->title[0]);
			            $page->setTab( new Tab($templateobj->getTab()->getId()));

        				$page->Save($adminUser);
                    }
                    //now process template viewers
                    //process groups that should be added as viewers to this template
                    $viewergroups = explode(',',(string)$template->viewergroups[0]);
                    $groupstring = '';
                    foreach ($viewergroups as $vg) {
                        if (!empty($vg)) { //sanity check
                            $group = Group::RetrieveGroupByTitle($vg, $institution->getId());
                            if (!empty($groupstring)) {
                                $groupstring .= ',';
                            }
                            $groupstring .= 'g'.$group->getId();
                        }
                    }
                    $templateobj->AddViewersFromString($groupstring);

                    //can't process users as they may not have been generated yet - save for later.
                    $templateviewerusers[$templateobj->getTab()->getId()]  = explode(',',(string)$template->viewerusers[0]);
                }
            } else {
                $templateids[(int)$template->attributes()->id[0]] = $row['id'];
                add_error_msg("The template {$template->title[0]} already exists");
            }

        }
    } else {
        add_info_msg('no valid template.xml to load groups from');
    }
    
    $objects = scandir($uploaddir);
    if (!empty($objects)) {
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($uploaddir."/".$object) == "file" && substr(strrchr($object, '.'), 1) == 'zip') {
                    $zip = new ZipArchive();
                    if (!$zip->open($uploaddir."/".$object)) {
                        error("couldn't open zip", $returnurl);
                    }
                    $newdir = $uploaddir.'/'.substr($object, 0, -4);
                    if (!is_dir($newdir)) {
                        mkdir($newdir);
                    }
                    $zip->extractTo($newdir);
                    //delete original zip
                    unlink($uploaddir."/".$object);
                    //now trigger import of this folder
                    leap_restore_user($newdir, '', $templateids);
                    
                    //Delete directory as no longer needed.
                    delete_dir_recursive($newdir);
                }
            } 
        }
        //now update group members if needed
        if (!empty($groupmembers)) {
            foreach ($groupmembers as $insid => $groups) {
                $institution = Institution::RetrieveById($insid);
                foreach ($groups as $g => $members) {
                    $group = Group::RetrieveGroupByTitle($g, $institution->getId());
                    $members = explode(',', $members);
                    foreach ($members as $member) {
                        if (!empty($member)) {
                            $user = User::RetrieveByEmail($member, $institution);
                            $group->addMember($user);
                        }
                    }
                    $group->save($adminUser);
                }
            }
        }
        if (!empty($templateviewerusers)) {
            foreach ($templateviewerusers as $tid => $users) {
                $tab = Tab::GetTabById($tid);
                $template = $tab->getTemplate();
                $userstring = '';
                foreach ($users as $u) {
                    if (!empty($u)) {
                        if (!empty($userstring)) {
                            $userstring .= ",";
                        }
                        $user = User::RetrieveByEmail($u, $template->getInstitution());
                        $userstring .= $user->getID();
                    }
                    $template->AddViewersFromString($userstring);
                }
            }
        }

    } else {
        add_info_msg('no valid user files available in import zip');
    }

} else if ($pt === 'user'){
    $objects = scandir($uploaddir);
    if (!empty($objects)) {
        $newUser = array();
        $newUser['userType'] = Safe::post('usertype', PARAM_ALPHANUMEXT);
        $newUser['username'] = Safe::post('username', PARAM_ALPHANUMEXT);
        $newUser['institution_id'] = Safe::post('institution_id', PARAM_INT);
        $newUser['password'] = Safe::post('password');
        // trigger import of this folder
        leap_restore_user($uploaddir, $newUser);

        // Delete directory as no longer needed.
        delete_dir_recursive($uploaddir);
    }
} 


//now delete old directory.
delete_dir_recursive($uploaddir);

//function to restore an individual user leap export
function leap_restore_user($dir, $user = '', $templateids = array()) {
    global $adminUser;
    $db = Database::getInstance();
    //check if valid Leap export dir
    if (!file_exists($dir.'/leap2a.xml')) {
        notify('Invalid export: '.$dir. ' doesn\'t contain a valid leap export');
        return false;
    }
    //parse xml file
    $options =
        LIBXML_COMPACT |    // Reported to greatly speed XML parsing
        LIBXML_NONET        // Disable network access - security check
        ;
    if (!$xml = simplexml_load_file($dir.'/leap2a.xml', 'SimpleXMLElement', $options)) {
        error("invalid xml", $returnurl);
    }
    //TODO: clean vars to prevent injection.
    $usertype = isset($user['userType']) ? $user['userType'] : $xml->author->xpath('infolio:usertype');
    $usertype = is_array($usertype) ? (string)$usertype[0] : $usertype;
    if (empty($usertype)) {
        $usertype = 'student';
    }
    $username = isset($user['username']) ? $user['username'] : $xml->author->xpath('infolio:username');
    $username = is_array($username) ? (string)$username[0] : $user['username'];

    $description = $xml->author->xpath('infolio:userdesc');
    $description = isset($description[0]) ? (string)$description[0] : '';
    
    if (isset($user['institution_id'])) {
        $institutionId = $user['institution_id'];
    } else {
        $insxml = $xml->author->xpath('infolio:institution');
        $institution = isset($insxml[0]) ? (string)$insxml[0] : '';
        if (!empty($institution)) {
            //get institution id based on institution url above.
            $sqlUser = "SELECT * from institution WHERE url='$institution'";
            $result = $db->query($sqlUser);
            $row = mysql_fetch_assoc($result);
            if (empty($row)) {
                add_info_msg('couldn\'t find insitution for user - using default instead');
                $institutionId = 1;
            } else {
                $institutionId = $row['id'];
            }
        } else {
            $institutionId = 1;
        }
    }

    $theme = $xml->author->xpath('infolio:theme');
    $theme = isset($theme[0]) ? (string)$theme[0] : '';

    $name = explode(', ',$xml->author->name[0]);
    
    $passxml = $xml->author->xpath('infolio:password');
    if (isset($user['password'])) {
        $password = $user['password'];
    } elseif (!empty($passxml[0])) {
        $password = (string)$passxml[0];
    } else {
       $password = generatePassword(); //TODO: think about e-mailing the user their password?
    }

    //TODO: check for SQL injection here. (and in ajax.dispatcher where this is used)
     try {
         $permissionManager = PermissionManager::Create($username, $password, $usertype, $adminUser);
         }
         catch(Exception $e) {
             die($e->getMessage());
         }

     $newUser = User::CreateNewUser(
             $name[1],
             $name[0],
             (string)$xml->author->email[0],
             $description,
             $permissionManager,
             new Institution($institutionId));

     if($newUser->isUnique()) {
         $newUser->Save($adminUser);

         //now update theme:
         $sqlUser = "UPDATE user SET colour='$theme' WHERE ID={$newUser->getId()}";
         $result = $db->query($sqlUser);

         $tabs = array();
         $views = array();
         $artefacts = array();
         foreach ($xml->entry as $entry) {
             $entryid = (string) $entry->id[0];
             if (strpos($entryid, 'portfolio:collection')===0) {
                 $tabs[$entryid] = $entry;
             } elseif (strpos($entryid, 'portfolio:view')===0) {
                 $views[$entryid] = $entry;
             } elseif (strpos($entryid, 'portfolio:artefact')===0) {
                 $artefacts[$entryid] = $entry;
             }
         }
         //now upload files
         $savedfiles = array();
         foreach ($artefacts as $artefact) {
             $arid = (string)$artefact->id;
             $title = (string)$artefact->title;
             $link = (string)$artefact->link->attributes()->href;
             if (file_exists($dir . '/'.$link)) { //doesn't work for permission
                 $uploader = new Uploader();
                 $file['name'] = str_replace('files/', '',$link);
                 $file['infoliopath'] = $dir . '/'.$link;
                 $assetId = $uploader->doCopyUpload($file, $newUser);
                 $savedfiles[$arid] = $assetId;
                 //now update title
                 $asset = Asset::RetrieveById($assetId, $newUser);
	             $asset->setTitle($title);
	             $asset->Save($newUser);
	             //now check for tags
	             $tags = $artefact->xpath('infolio:tags');
	             $tags = $tags[0]->xpath('infolio:tag');
	             foreach ($tags as $tag) {
                     //create new tag or get it.
                     $t = Tag::CreateOrRetrieveByName((string)$tag, $newUser->getInstitution(), $adminUser);
                     //add tag to asset.
                     $asset->addTag($t, $newUser);
                 }
                 //now check for favourite
                 $fav = $artefact->xpath('infolio:favourite');
                 if (isset($fav[0]) && (string)$fav[0] == 'true') {
                     $asset->setFavourite(true, $newUser);
                 }
             }
         }

         foreach ($tabs as $tabxml) {
             $icon = $tabxml->xpath('infolio:icon');
             //check if this is a tempate tab first.
             $tem = $tabxml->xpath('infolio:template');
             if (isset($tem[0]) && isset($templateids[(int)$tem[0]])) {
                 //this is a template tab
                 $tabs = Tab::getTabsByTemplateId($templateids[(int)$tem[0]]);
                 $tab = $tabs[0]; //grab the first tab in the array returned
             } else {
                 $tab = Tab::CreateNewTab((string)$tabxml->title[0], $newUser);
                 $tab->setWeight();
                 if (!empty($icon)) {
                    $iconid = (int)$icon[0];
                     if (isset($savedfiles["portfolio:artefact".$iconid])) {
                         $tab->setIconById((int)$savedfiles["portfolio:artefact".$iconid]);
                     }
                 }
                 $tab->Save($newUser);                 
             }
             // create pages attached to this tab
             foreach ($tabxml->link as $link) {
                 $page = '';
                 $viewid = (string)$link->attributes()->href;
                 //create each view (page and each page block on each page)
                 $viewxml = $views[$viewid]->xpath('infolio:view');
                 $title = (string)$views[$viewid]->title[0];
                 //create page now.
                 //TODO: check if this is a template page
                 $temp = $views[$viewid]->xpath('infolio:templatepage');
                 if (isset($temp[0])) {
                     $pages = $tab->getPages();
                     foreach ($pages as $p) {
                         if ($p->getTitle() == $title) {
                             $page = $p;
                         }
                     }
                 }
                 if (empty($page)) { //template not found so create new page.
                     $page = new Page();
                     $page->setUser($newUser);
                     $page->setTab(new Tab($tab->getId()));
                     $page->setEnabled(true);
                     $page->setTitle($title);
                     $page->Save($newUser);
                 }
                 foreach ($viewxml as $i) {
                     $blocks = $i->xpath('infolio:blockinstance');
                     foreach ($blocks as $block) {
                         //create block now.
                         $blocktitle = $block->xpath('infolio:blocktitle');
                         $newBlock = PageBlock::CreateNew((string)$blocktitle[0], $page, $newUser);
                         $newBlock->setWeight($page->getNewBlockWeight());
                         
                         $templateid = $block->xpath('infolio:layout');
                         $newBlock->setLayoutTemplateId((string)$templateid[0]);
                         
                         //now get words to put in block
                         $words = array();
                         $word0 = $block->xpath('infolio:words0');
                         $words[0] = (string)$word0[0];
                         $word1 = $block->xpath('infolio:words1');
                         $words[1] = (string)$word1[0]; 

                         $newBlock->setWordBlocks($words);
                         //now get pictures to put in block - use $savedfiles to get new id
                         $pic0 = $block->xpath('infolio:picture0');
                         $picid = (int)$pic0[0];
                         if (isset($savedfiles["portfolio:artefact".$picid])) {
                             $newBlock->setPicture(0, (int)$savedfiles["portfolio:artefact".$picid]);
                         }

                         $pic1 = $block->xpath('infolio:picture1');
                         $picid = (int)$pic1[0];
                         if (isset($savedfiles["portfolio:artefact".$picid])) {
                             $newBlock->setPicture(1, (int)$savedfiles["portfolio:artefact".$picid]);
                         }
                         $newBlock->Save($newUser);
                     }
                 }
                 //remove from array as this has now been created.
                 //unset($views[$viewid]);
             }
         }
         //update user profile image
         $profilepic = $xml->author->xpath('infolio:profilepic');
         $profilepic = isset($profilepic[0]) ? (string)$profilepic[0] : '';
         if (isset($savedfiles["portfolio:artefact".$profilepic])) {
             $newUser->setProfilePictureId($savedfiles["portfolio:artefact".$profilepic]);
             $newUser->Save($adminUser);
             //stupid hack to enable user as the above Save occasionally glitches and changes the enabled value for the user.
             $sqlfunk = "UPDATE user set enabled=1 WHERE ID=".$newUser->getId();
             $resultf = $db->query($sqlfunk);
         }

         //update groups
         $groups = $xml->author->xpath('infolio:groups');
         $groups = isset($groups[0]) ? (string)$groups[0] : '';
         $groups = explode(',', $groups);

         foreach ($groups as $group) {
             if (!empty($group)) {
                 $groupobj = Group::RetrieveGroupByTitle($group, $institutionId);
                 if (!empty($groupobj)) {
                     $groupobj->addMember($newUser);
                     $groupobj->save($adminUser);
                 }
             }
         }

     } else {
         add_error_msg("The user '{$username}' at " . $newUser->getInstitution()->getName() .' already exists');
     }
}
add_ok_msg("import finished");
// redirect back to correct page
header('Location: '. $returnurl);
