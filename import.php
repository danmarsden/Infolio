<?

/**
 * import.php - import of Infolio Leap file.
 *
 * @author     Dan Marsden, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
*/

// This file can take a long time to run, so needed to expand the timeout
set_time_limit(360);

include_once("system/initialise.php");
include_once("model/User.class.php");
include_once("function/shared.php");
include_once("function/core.php");

$uploaddir = 'data/import';

// Check user is logged in before letting them do stuff (except logging in)
$adminUser = require_admin();
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
if ($_POST['type'] =='site') {
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
                $data = array(
                              'asset_id' => '0',
                              'name' => (string)$institution->name[0],
                              'url' => $insurl,
                              'created_by' => $adminUser->getId(),
                              'updated_by' => $adminUser->getId(),
                              'created_time' => Date::formatForDatabase(time()),
                              'updated_time' => Date::formatForDatabase(time())
                          );

                // Write to DB
                $db = Database::getInstance();
                $db->perform('institution', $data);
            }
            
        }
    } else {
        add_info_msg('no institution to add');
    }
    //TODO: in future handle any other site level files here - like site groups or site data.
    
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
                    leap_restore_user($newdir);
                    
                    //Delete directory as no longer needed.
                    delete_dir_recursive($newdir);
                }
            } 
        }
    } else {
        add_info_msg('no users to add');
    }

} else if ($_POST['type'] === 'user'){
    $objects = scandir($uploaddir);
    if (!empty($objects)) {
        $newUser = $_POST; // new user details
        // trigger import of this folder
        leap_restore_user($uploaddir, $newUser);

        // Delete directory as no longer needed.
        delete_dir_recursive($uploaddir);
    }
} 


//now delete old directory.
delete_dir_recursive($uploaddir);

//function to restore an individual user leap export
function leap_restore_user($dir, $user = '') {
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
    
    if (isset($user['institution'])) {
        $institution = $user['institution'];
    } else {
        $insxml = $xml->author->xpath('infolio:institution');
        $institution = isset($insxml[0]) ? (string)$insxml[0] : '';
    }

    if (!empty($institution)) {
        //get institution id based on institution url above.
        $sqlUser = "SELECT * from institution WHERE url='$institution'";
        $result = $db->query($sqlUser);
        $row = mysql_fetch_assoc($result);
        if (empty($row)) {
            notify('couldn\'t find insitution for user - using default instead');
            $institutionId = 1;
        } else {
            $institutionId = $row['id'];
        }
    } else {
        $institutionId = 1;
    }
    $theme = $xml->author->xpath('infolio:theme');
    $theme = isset($theme[0]) ? (string)$theme[0] : '';

    $name = explode(', ',$xml->author->name[0]);

    $password = generatePassword(); //TODO: think about e-mailing the user their password?

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
             $xml->author->email[0],
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
             $link = $dir . (string)$artefact->link->attributes()->href;
             if (is_file($link)) {
                 $uploader = new Uploader();
                 $file['name'] = $title;
                 $file['infoliopath'] = $link;
                 $assetId = $uploader->doCopyUpload($file, $newUser);
                 $savedfiles[$arid] = $assetId;
             }
         }

         foreach ($tabs as $tabxml) {
             $tab = Tab::CreateNewTab((string)$tabxml->title[0], $newUser);
             $tab->Save($newUser);
             //TODO: create pages attached to this tab
             foreach ($tabxml->link as $link) {
                 $viewid = (string)$link->attributes()->href;
                 //TODO: create each view (page and each page block on each page)
                 $viewxml = $views[$viewid]->xpath('infolio:view');
                 $title = $views[$viewid]->title;
                 //create page now.
                 $page = new Page();
                 $page->setUser($newUser);
                 $page->setTab(new Tab($tab->getId()));
                 $page->setEnabled(true);
                 $page->setTitle($title);
                 $page->Save($newUser);

                 foreach ($viewxml as $i) {
                     $blocks = $i->xpath('infolio:blockinstance');
                     foreach ($blocks as $block) {
                         //create block now.
                         $newBlock = PageBlock::CreateNew($title, $page, $newUser);
                         $newBlock->setWeight($page->getNewBlockWeight());
                         
                         $templateid = $block->xpath('infolio:layout');
                         $newBlock->setLayoutTemplateId((string)$templateid[0]);
                         
                         //TODO: now get words to put in block
                         //$words = $block->xpath('infolio:words0');
                         //$newblock->setWordBlocks($wordBlocks);
                         
                         //TODO: now get pictures to put in block - use $savedfiles to get new id
                         
                         $newBlock->Save($newUser);
                     }
                 }
                 //remove from array as this has now been created.
                 //unset($views[$viewid]);
             }
         }
         //TODO: update user profile image

     } else {
         add_error_msg("The user '{$username}' at " . $newUser->getInstitution()->getName() .' already exists');
     }
}

// redirect back to correct page
header('Location: '. $returnurl);
