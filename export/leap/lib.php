<?php
/**
 * export.php - Creates a LEAP export of a user's infolio
 * See http://wiki.cetis.ac.uk/LEAP2A_specification
 *
 * LICENSE: This is an Open Source Project
 *
 * @author     Dan Marsden, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.0/uk/
*/


function export_portfolio($studentUser, $tabIds) {
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

    foreach ($userTabs as $aTab)  {
        $tabId = $aTab->getId();
        $aTab->setViewer($studentUser);
        if($tabId != 1 && (!isset($tabIds) || in_array($tabId, $tabIds))) {
            //TODO: group each page into the tabs?
            // Get pages for tab
            $tabPages = $aTab->getPages();
            foreach($tabPages as $aPage) {
                $entry->title = $aPage->getTitle();
                $entry->id = "portfolio:page".$aPage->getId();
                $entry->contenttype = 'html';
                $entry->content = leap_blocks($aPage, $studentUser);
                $entry->leaptype = 'selection';
               $leapxml .= leap_entry($entry);
               $leapxml .= leap_entryfooter();
            }
        }
    }
// Copy all user's assets
    foreach($userAssets as $asset) {
        $srcPath = $asset->getSystemFolder() . $asset->getHref();
        $dstPath = "$exportdir/files/{$asset->getType()}_{$asset->getHref()}";
        copy($srcPath, $dstPath);
        $entry->title = $asset->getTitle();
        $entry->id = "portfolio:artefact".$asset->getId();
        $entry->contenttype = $asset->getType();
        $entry->contentsrc  = "files/{$asset->getType()}_{$asset->getHref()}";
        $entry->leaptype = 'resource';
        $leapxml .= leap_entry($entry);
        $leapxml .= leap_entryfooter();
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

/*
    $tab;
    $page;


    // Get extra images including placeholder and tab icons that might not be in users asset
    $userAssets[0] = Image::GetPlaceHolder();
    foreach($userTabs as $aTab)
    {
        $tempAsset = $aTab->getIcon();
        $userAssets[$tempAsset->getId()] = $tempAsset;
    }

    //printTimeElapsed("Got all assets");

    // Save user's homepage and collection page
    saveStaticPage('staticversion/index.php', "{$userStaticDir}/index.html");
    saveStaticPage('staticversion/collection.php', "{$userStaticDir}/collection.html");
    // Save all their tabs
    foreach ($userTabs as $aTab)
    {
        $tabId = $aTab->getId();

        $aTab->setViewer($studentUser);
        if($tabId != 1 && (!isset($tabIds) || in_array($tabId, $tabIds)))
        {
            saveStaticPage('staticversion/tab.php', "{$userStaticDir}/tab-{$tabId}.html");

            // Get pages for tab
            $tabPages = $aTab->getPages();
            foreach($tabPages as $aPage)
            {
                $pageId = $aPage->getId();
                saveStaticPage('staticversion/page.php', "{$userStaticDir}/page-{$pageId}.html");

                // Get page's preview image for tab's list page
                $aPage->setViewer($studentUser);
                $previewImage = $aPage->PreviewImage();
                if(isset($previewImage))
                {
                    $previewImageSrcPath =  rtrim(DIR_FS_ROOT, "/") . $previewImage;
                    $previewImageDstPath = $userStaticDir . preg_replace('/data\/.*-asset/', 'data', $previewImage);
                    copy($previewImageSrcPath, $previewImageDstPath);
                }

                // Get page's attachments.
                $attachments = $aPage->getAttachments();
                foreach($attachments as $attachment)
                {
                    $srcFile = $attachment->getSystemFolder() . $attachment->getHref();
                    if(file_exists($srcFile)) {
                        copy($srcFile, $userStaticDir . '/data/file/' . $attachment->getHref());
                    }
                }
            }
        }
    }

    //printTimeElapsed("Created all pages");

    // Copy all user's assets
    foreach($userAssets as $asset)
    {
        // Copy master asset's
        if($includeOriginalAssets)
        {
            $srcPath = $asset->getSystemFolder() . $asset->getHref();
            $dstPath = "{$userStaticDir}/data/{$asset->getType()}/{$asset->getHref()}";
            copy($srcPath, $dstPath);
        }
        // Copy thumbnail version of assets
        switch($asset->getType())
        {
            case Asset::IMAGE:
                copyForSize($asset, Image::SIZE_BOX, "{$userStaticDir}/data");
                copyForSize($asset, Image::SIZE_SMALL_BOX, "{$userStaticDir}/data");
                copyForSize($asset, Image::SIZE_TAB_ICON, "{$userStaticDir}/data");
                copyForSize($asset, Image::SIZE_THUMBNAIL, "{$userStaticDir}/data");
                break;
            case Asset::VIDEO:
                // Copy video thumbnail
                copyForSize($asset, Image::SIZE_THUMBNAIL, "{$userStaticDir}/data");

                // Copy FLV file
                try
                {
                    $srcPath = $asset->getFilePath();

                    $dstPath = "{$userStaticDir}/data/video/flv/{$asset->getFileNamePart()}.flv";
                    copy($srcPath, $dstPath);
                }
                catch(Exception $e)
                {
                    Logger::Write("Export - Problem copying a video: {$e->getMessage()}", Logger::TYPE_WARNING, $studentUser);
                }
                break;
            case Asset::AUDIO:
                try
                {
                    $srcPath = $asset->getFilePath();
                    $dstPath = "{$userStaticDir}/data/audio/{$asset->getHref()}";
                    copy($srcPath, $dstPath);
                }
                catch(Exception $e)
                {
                    Logger::Write("Export - Problem copying a sound: {$e->getMessage()}", Logger::TYPE_WARNING, $studentUser);
                }
                break;
            // default:
            //Do nothing as no other files required
        }
    }

    //printTimeElapsed("Copied all assets");

    // Zipping
    $filename = "staticversion/user-{$studentUser->getId()}.zip";

    // Delete old zip file (stops files from last export being kept)
    if(file_exists($filename)) {
        unlink($filename);
    }

    // Add it all to a zip
    $zip = new Zipper();

    if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
        exit("cannot open <$filename>\n");
    }
    $zip->addDirectory($userStaticDir, $studentUser->getFirstName() . '-infolio');
    $zip->close();
    //printTimeElapsed("Zipped it");

    // Delete source files
    delTree($userStaticDir);

    //printTimeElapsed("Done");
    return $tabId;*/
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
                $fileName = $baseDirName . str_replace($rootPath, '', $node);
                
                $this->addFile($node, $fileName);
            }
        }
    }

}