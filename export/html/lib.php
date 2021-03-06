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
 * html/lib.php - Creates a static html version of a user's infolio
 *
 * @author     Richard Garside [www.richardsprojects.co.uk]
 * @copyright  2008 onwards JISC TechDis (http://www.jisctechdis.ac.uk/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

function export_portfolio($studentUser, $tabIds, $returnfile=false) {
    global $userTabs, $userAssetCollection, $userAssets, $tabId, $pageId;
    $includeOriginalAssets = false;

    $tab;
    $page;

    // Create folder for these files
    $userStaticDir = DIR_FS_ROOT . 'staticversion/user-' . $studentUser->getId();

    //printTimeElapsed("Set up folders");

    copyr(DIR_FS_ROOT . 'staticversion/user-template', $userStaticDir);
    $filepatharray = array('/audio',
                           '/file',
                           '/image/page_thumbnail',
                           '/image/photo_login',
                           '/image/size_box',
                           '/image/size_small_box',
                           '/image/size_tabicon',
                           '/image/size_thumbnail',
                           '/upload/size_thumbnail',
                           '/video/flv',
                           '/video/size_thumbnail');
    $old = umask(0);
    foreach ($filepatharray as $dir) {
        if (!is_dir($userStaticDir."/data".$dir)) {
            mkdir($userStaticDir."/data".$dir, 00777, true);
        }
    }
     umask($old);

    //printTimeElapsed('Copied base folders and assets');

    //printTimeElapsed("Got main assets");

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
    if ($returnfile) {
        return $filename;
    } else {
        send_temp_file($filename, 'html-export-user'. $studentUser->getId() . '-' . time() . '.zip');
    }
}



function copyForSize(Asset $asset, $size, $copyToDir)
{
    try
    {
        $srcPath = $asset->getFilePath($size);
    }
    catch(Exception $e)
    {
        Logger::Write("Export - Couldn't make '{$size}' version of '{$asset->getTitle()}' - {$e->getMessage()}", Logger::TYPE_WARNING, $studentUser);
        return;
    }
    $assetId = $asset->getId();
    if(!isset($assetId)) $assetId = 0;
    $fileExtension = ($asset->getType() == Asset::IMAGE) ? $asset->getFileExtension() : 'gif';
    $dstPath = "{$copyToDir}/{$asset->getType()}/$size/{$assetId}.{$fileExtension}";
    copy($srcPath, $dstPath);
}

function saveStaticPage($inputFile, $outputFile)
{
    global $page, $tab, $studentUser, $studentTheme, $tabId, $pageId;
    
    ob_start();
    $notSecured = true;
    include($inputFile);
    $staticPageContent = ob_get_contents();
    ob_end_clean();

    saveFile(fixLinks($staticPageContent), $outputFile);
}

function saveFile($content, $file)
{
    $fp = fopen($file,"w");
    fwrite($fp,$content);
    fclose($fp);
}

function fixLinks($content)
{
    global $userTabs, $userAssets;
    
    $content = str_replace('href="/_styles/', 'href="_styles/', $content); // Fix style sheets
    $content = str_replace('href="/_scripts/', 'href="_scripts/', $content); // Fix JavaScript css links
    $content = str_replace('src="/_scripts/', 'src="_scripts/', $content); // Fix JavaScript css links
    $content = str_replace('src="/_images/', 'src="_images/', $content); // Fix system images
    $content = preg_replace('/href="page-(\d+)"/', 'href="page-$1.html"', $content); // Fix page links
    $content = preg_replace('/<p id="new-tab">[^<]*?<\/p>/', '', $content); // remove new tab link
    $content = str_replace('<a href="admin/">Admin</a>', '', $content); // Admin link
    $content = preg_replace('/src="\/images\/([a-z_]+)\/(\d+)\/"/', 'src="data/image/$1/$2.__fileextension__"', $content); // Image srcs
    
    $content = preg_replace('/src="\/data\/.*?\-asset/', 'src="data', $content); // Image srcs

    $content = preg_replace('/src="\/videos\/([a-z_]+)\/(\d+)\/"/', 'src="data/video/size_thumbnail/$2.gif"', $content); // Video thumbnail sources
    $content = preg_replace('/href="\/data\/.*?\/file\/(.*?)"/', 'href="data/file/$1"', $content); // File attachment links

    $content = preg_replace('/\/_flash\/BigVideoPlayer.swf\?fvMoviePath=\/data\/.*?\//', 'BigVideoPlayer.swf?fvMoviePath=data/', $content); // Flash video player
    $content = str_replace('/_flash/listen.swf?mname=/data/rix-asset', 'listen.swf?mname=data', $content); // Flash sound player
    $content = preg_replace('/<p[^>]*?><a href=".*?" class="icon">.*?<\/a><\/p>/', '', $content); // Remove icons
    $content = preg_replace('/<p id="new-tab">.*?<\/p>/', '', $content);
    $content = str_replace('href="collection.php', 'href="collection.html"', $content);
    $content = str_replace('overflow: hidden', 'overflow-x: hidden', $content); // ONly needed for content page
    $content = preg_replace('/<div class="playbutton2{0,1}">.*?<\/div>/', '', $content); // swf text to speech reader
    $content = preg_replace('/<div class="blank">.*?<\/div>/', '', $content);

    //remove shared tabs link:
    $content = str_replace('<a id="shared-tabs" href="sharedtabs.php" title="Shared tabs" >Shared tabs</a>','', $content);
    $content = str_replace('<a id="my-tabs" href="managetabs.php" title="My tabs" >My tabs</a>','', $content);


    // Fix all image extensions
    foreach($userAssets as $asset)
    {
        $assetId = ($asset->getId() == null) ? 0 : $asset->getId();
        $searchString = "/{$assetId}.__fileextension__";
        $replaceString = "/{$assetId}.{$asset->getFileExtension()}";
        $content = str_replace($searchString, $replaceString, $content);
    }

    // Fix tab links
    if(isset($userTabs))
    {
        foreach($userTabs as $tab)
        {
            $tabHrefSearch = 'href="' . $tab->getLink()->getHref();

            if($tab->getId() != 1)
            {
                $content = str_replace($tabHrefSearch,
                                    'href="tab-' . $tab->getId() . '.html',
                                    $content, $countTimes);
            }
            else
            {
                $content = str_replace($tabHrefSearch,
                                    'href="index.html',
                                    $content, $countTimes);
            }
        }
    }
    return $content;
}

/**
* Delete a directory and conents
*
* @param string $str Path to file or directory
*/
function delTree($dir)
{
    $directory = dir($dir);
    while (false !== $entry = $directory->read())
    {
        $dir = rtrim($dir, '/\\') . '/';
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        //print("Checking <strong>" . $dir.$entry . '</strong><br />');
        if( is_dir($dir.$entry) )
        {
            //print("Recursing on " . $dir.$entry ."<br />");
            delTree($dir.$entry);
        }
        else
        {
            //print("Deleting " . $dir.$entry ."<br />");
            unlink($dir.$entry);
        }   
    }
    $directory->close();
    rmdir($dir);
}


class Zipper extends ZipArchive
{
    public function addDirectory($path, $baseDirName="")
    {
        $this->addDir($path, $path, $baseDirName);
    }

    private function addDir($path, $rootPath, $baseDirName)
    {
        $zipPath = $baseDirName . str_replace($rootPath, '', $path);

        $this->addEmptyDir($zipPath);
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