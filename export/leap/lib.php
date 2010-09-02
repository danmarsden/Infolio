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
                $entry->id = "portfolio:view".$aPage->getId();
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
        $res->contenttype = $asset->getType();
        $res->url = $asset->getHref();
        $res->id  = $asset->getId();
        $resource = leap_resource($res);
        $entry->content = '';
        $entry->title = $asset->getTitle();
        $entry->id = "portfolio:artefact".$asset->getId();
        $entry->contenttype = 'text';
        $entry->resource = $resource;
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
    
    send_temp_file($zipfilename, 'leapinfolio-export-leap-user'. $studentUser->getId() . '-' . $exporttime . '.zip');

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


/**
 * Handles the sending of temporary file to user, download is forced.
 * File is deleted after abort or succesful sending.
 * @param string $path path to file, preferably from moodledata/temp/something; or content of file itself
 * @param string $filename proposed file name when saving file
 * @param bool $path is content of file
 */
function send_temp_file($path, $filename, $pathisstring=false) {
    global $CFG;

    // close session - not needed anymore
    @session_write_close();

    if (!$pathisstring) {
        if (!file_exists($path)) {
            header('HTTP/1.0 404 not found');
            echo "ERROR:File not found!";
        }
        // executed after normal finish or abort
        @register_shutdown_function('send_temp_file_finished', $path);
    }

    //IE compatibiltiy HACK!
    if (ini_get('zlib.output_compression')) {
        ini_set('zlib.output_compression', 'Off');
    }

    // if user is using IE, urlencode the filename so that multibyte file name will show up correctly on popup
    if (check_browser_version('MSIE')) {
        $filename = urlencode($filename);
    }

    $filesize = $pathisstring ? strlen($path) : filesize($path);

    @header('Content-Disposition: attachment; filename='.$filename);
    @header('Content-Length: '.$filesize);
    @header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
    @header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
    @header('Pragma: no-cache');
    @header('Accept-Ranges: none'); // Do not allow byteserving

    while (@ob_end_flush()); //flush the buffers - save memory and disable sid rewrite
    if ($pathisstring) {
        echo $path;
    } else {
        readfile_chunked($path);
    }

    die; //no more chars to output
}
/**
 * Improves memory consumptions and works around buggy readfile() in PHP 5.0.4 (2MB readfile limit).
 */
function readfile_chunked($filename, $retbytes=true) {
    $chunksize = 1*(1024*1024); // 1MB chunks - must be less than 2MB!
    $buffer = '';
    $cnt =0;
    $handle = fopen($filename, 'rb');
    if ($handle === false) {
        return false;
    }

    while (!feof($handle)) {
        @set_time_limit(60*60); //reset time limit to 60 min - should be enough for 1 MB chunk
        $buffer = fread($handle, $chunksize);
        echo $buffer;
        flush();
        if ($retbytes) {
            $cnt += strlen($buffer);
        }
    }
    $status = fclose($handle);
    if ($retbytes && $status) {
        return $cnt; // return num. bytes delivered like readfile() does.
    }
    return $status;
}
/**
 * Checks to see if is a browser matches the specified
 * brand and is equal or better version.
 *
 * @uses $_SERVER
 * @param string $brand The browser identifier being tested
 * @param int $version The version of the browser
 * @return bool true if the given version is below that of the detected browser
 */
 function check_browser_version($brand='MSIE', $version=5.5) {
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }

    $agent = $_SERVER['HTTP_USER_AGENT'];

    switch ($brand) {

      case 'Camino':   /// Mozilla Firefox browsers

              if (preg_match("/Camino\/([0-9\.]+)/i", $agent, $match)) {
                  if (version_compare($match[1], $version) >= 0) {
                      return true;
                  }
              }
              break;


      case 'Firefox':   /// Mozilla Firefox browsers

          if (preg_match("/Firefox\/([0-9\.]+)/i", $agent, $match)) {
              if (version_compare($match[1], $version) >= 0) {
                  return true;
              }
          }
          break;


      case 'Gecko':   /// Gecko based browsers

          if (substr_count($agent, 'Camino')) {
              // MacOS X Camino support
              $version = 20041110;
          }

          // the proper string - Gecko/CCYYMMDD Vendor/Version
          // Faster version and work-a-round No IDN problem.
          if (preg_match("/Gecko\/([0-9]+)/i", $agent, $match)) {
              if ($match[1] > $version) {
                      return true;
                  }
              }
          break;


      case 'MSIE':   /// Internet Explorer

          if (strpos($agent, 'Opera')) {     // Reject Opera
              return false;
          }
          $string = explode(';', $agent);
          if (!isset($string[1])) {
              return false;
          }
          $string = explode(' ', trim($string[1]));
          if (!isset($string[0]) and !isset($string[1])) {
              return false;
          }
          if ($string[0] == $brand and (float)$string[1] >= $version ) {
              return true;
          }
          break;

      case 'Opera':  /// Opera

          if (preg_match("/Opera\/([0-9\.]+)/i", $agent, $match)) {
              if (version_compare($match[1], $version) >= 0) {
                  return true;
              }
          }
          break;

      case 'Safari':  /// Safari
          // Look for AppleWebKit, excluding strings with OmniWeb, Shiira and SimbianOS
          if (strpos($agent, 'OmniWeb')) { // Reject OmniWeb
              return false;
          } elseif (strpos($agent, 'Shiira')) { // Reject Shiira
              return false;
          } elseif (strpos($agent, 'SimbianOS')) { // Reject SimbianOS
              return false;
          }

          if (preg_match("/AppleWebKit\/([0-9]+)/i", $agent, $match)) {
              if (version_compare($match[1], $version) >= 0) {
                  return true;
              }
          }

          break;

    }

    return false;
}
