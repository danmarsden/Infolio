<?php
/**
 * core.php - useful functions borrowed from MOODLE (GPL CODE)
 *
 * @author     Dan Marsden, Catalyst IT Ltd
 * @copyright  2010 Catalyst IT Ltd
*/
/**
 * Handles the sending of temporary file to user, download is forced.
 * File is deleted after abort or succesful sending.
 * @param string $path path to file or content of file itself
 * @param string $filename proposed file name when saving file
 * @param bool $path is content of file
 */
function send_temp_file($path, $filename, $pathisstring=false) {

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
    $mimetype = mimeinfo('type', $filename);
    $filesize = $pathisstring ? strlen($path) : filesize($path);

    @header('Content-Disposition: attachment; filename='.$filename);
    @header('Content-Length: '.$filesize);
    @header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
    @header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
    @header('Pragma: no-cache');
    @header('Accept-Ranges: none'); // Do not allow byteserving
    @header('Content-Type: '.$mimetype);

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

/**
 * @return List of information about file types based on extensions.
 *   Associative array of extension (lower-case) to associative array
 *   from 'element name' to data. Current element names are 'type' and 'icon'.
 *   Unknown types should use the 'xxx' entry which includes defaults.
 */
function get_mimetypes_array() {
    return array (
        'xxx'  => array ('type'=>'document/unknown', 'icon'=>'unknown.gif'),
        '3gp'  => array ('type'=>'video/quicktime', 'icon'=>'video.gif'),
        'ai'   => array ('type'=>'application/postscript', 'icon'=>'image.gif'),
        'aif'  => array ('type'=>'audio/x-aiff', 'icon'=>'audio.gif'),
        'aiff' => array ('type'=>'audio/x-aiff', 'icon'=>'audio.gif'),
        'aifc' => array ('type'=>'audio/x-aiff', 'icon'=>'audio.gif'),
        'applescript'  => array ('type'=>'text/plain', 'icon'=>'text.gif'),
        'asc'  => array ('type'=>'text/plain', 'icon'=>'text.gif'),
        'asm'  => array ('type'=>'text/plain', 'icon'=>'text.gif'),
        'au'   => array ('type'=>'audio/au', 'icon'=>'audio.gif'),
        'avi'  => array ('type'=>'video/x-ms-wm', 'icon'=>'avi.gif'),
        'bmp'  => array ('type'=>'image/bmp', 'icon'=>'image.gif'),
        'c'    => array ('type'=>'text/plain', 'icon'=>'text.gif'),
        'cct'  => array ('type'=>'shockwave/director', 'icon'=>'flash.gif'),
        'cpp'  => array ('type'=>'text/plain', 'icon'=>'text.gif'),
        'cs'   => array ('type'=>'application/x-csh', 'icon'=>'text.gif'),
        'css'  => array ('type'=>'text/css', 'icon'=>'text.gif'),
        'csv'  => array ('type'=>'text/csv', 'icon'=>'excel.gif'),
        'dv'   => array ('type'=>'video/x-dv', 'icon'=>'video.gif'),
        'dmg'  => array ('type'=>'application/octet-stream', 'icon'=>'dmg.gif'),

        'doc'  => array ('type'=>'application/msword', 'icon'=>'word.gif'),
        'docx' => array ('type'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'icon'=>'docx.gif'),
        'docm' => array ('type'=>'application/vnd.ms-word.document.macroEnabled.12', 'icon'=>'docm.gif'),
        'dotx' => array ('type'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.template', 'icon'=>'dotx.gif'),
        'dotm' => array ('type'=>'application/vnd.ms-word.template.macroEnabled.12', 'icon'=>'dotm.gif'),

        'dcr'  => array ('type'=>'application/x-director', 'icon'=>'flash.gif'),
        'dif'  => array ('type'=>'video/x-dv', 'icon'=>'video.gif'),
        'dir'  => array ('type'=>'application/x-director', 'icon'=>'flash.gif'),
        'dxr'  => array ('type'=>'application/x-director', 'icon'=>'flash.gif'),
        'eps'  => array ('type'=>'application/postscript', 'icon'=>'pdf.gif'),
        'fdf'  => array ('type'=>'application/pdf', 'icon'=>'pdf.gif'),
        'flv'  => array ('type'=>'video/x-flv', 'icon'=>'video.gif'),
        'gif'  => array ('type'=>'image/gif', 'icon'=>'image.gif'),
        'gtar' => array ('type'=>'application/x-gtar', 'icon'=>'zip.gif'),
        'tgz'  => array ('type'=>'application/g-zip', 'icon'=>'zip.gif'),
        'gz'   => array ('type'=>'application/g-zip', 'icon'=>'zip.gif'),
        'gzip' => array ('type'=>'application/g-zip', 'icon'=>'zip.gif'),
        'h'    => array ('type'=>'text/plain', 'icon'=>'text.gif'),
        'hpp'  => array ('type'=>'text/plain', 'icon'=>'text.gif'),
        'hqx'  => array ('type'=>'application/mac-binhex40', 'icon'=>'zip.gif'),
        'htc'  => array ('type'=>'text/x-component', 'icon'=>'text.gif'),
        'html' => array ('type'=>'text/html', 'icon'=>'html.gif'),
        'xhtml'=> array ('type'=>'application/xhtml+xml', 'icon'=>'html.gif'),
        'htm'  => array ('type'=>'text/html', 'icon'=>'html.gif'),
        'ico'  => array ('type'=>'image/vnd.microsoft.icon', 'icon'=>'image.gif'),
        'ics'  => array ('type'=>'text/calendar', 'icon'=>'text.gif'),
        'isf'  => array ('type'=>'application/inspiration', 'icon'=>'isf.gif'),
        'ist'  => array ('type'=>'application/inspiration.template', 'icon'=>'isf.gif'),
        'java' => array ('type'=>'text/plain', 'icon'=>'text.gif'),
        'jcb'  => array ('type'=>'text/xml', 'icon'=>'jcb.gif'),
        'jcl'  => array ('type'=>'text/xml', 'icon'=>'jcl.gif'),
        'jcw'  => array ('type'=>'text/xml', 'icon'=>'jcw.gif'),
        'jmt'  => array ('type'=>'text/xml', 'icon'=>'jmt.gif'),
        'jmx'  => array ('type'=>'text/xml', 'icon'=>'jmx.gif'),
        'jpe'  => array ('type'=>'image/jpeg', 'icon'=>'image.gif'),
        'jpeg' => array ('type'=>'image/jpeg', 'icon'=>'image.gif'),
        'jpg'  => array ('type'=>'image/jpeg', 'icon'=>'image.gif'),
        'jqz'  => array ('type'=>'text/xml', 'icon'=>'jqz.gif'),
        'js'   => array ('type'=>'application/x-javascript', 'icon'=>'text.gif'),
        'latex'=> array ('type'=>'application/x-latex', 'icon'=>'text.gif'),
        'm'    => array ('type'=>'text/plain', 'icon'=>'text.gif'),
        'mov'  => array ('type'=>'video/quicktime', 'icon'=>'video.gif'),
        'movie'=> array ('type'=>'video/x-sgi-movie', 'icon'=>'video.gif'),
        'm3u'  => array ('type'=>'audio/x-mpegurl', 'icon'=>'audio.gif'),
        'mp3'  => array ('type'=>'audio/mp3', 'icon'=>'audio.gif'),
        'mp4'  => array ('type'=>'video/mp4', 'icon'=>'video.gif'),
        'm4v'  => array ('type'=>'video/mp4', 'icon'=>'video.gif'),
        'm4a'  => array ('type'=>'audio/mp4', 'icon'=>'audio.gif'),
        'mpeg' => array ('type'=>'video/mpeg', 'icon'=>'video.gif'),
        'mpe'  => array ('type'=>'video/mpeg', 'icon'=>'video.gif'),
        'mpg'  => array ('type'=>'video/mpeg', 'icon'=>'video.gif'),

        'odt'  => array ('type'=>'application/vnd.oasis.opendocument.text', 'icon'=>'odt.gif'),
        'ott'  => array ('type'=>'application/vnd.oasis.opendocument.text-template', 'icon'=>'odt.gif'),
        'oth'  => array ('type'=>'application/vnd.oasis.opendocument.text-web', 'icon'=>'odt.gif'),
        'odm'  => array ('type'=>'application/vnd.oasis.opendocument.text-master', 'icon'=>'odm.gif'),
        'odg'  => array ('type'=>'application/vnd.oasis.opendocument.graphics', 'icon'=>'odg.gif'),
        'otg'  => array ('type'=>'application/vnd.oasis.opendocument.graphics-template', 'icon'=>'odg.gif'),
        'odp'  => array ('type'=>'application/vnd.oasis.opendocument.presentation', 'icon'=>'odp.gif'),
        'otp'  => array ('type'=>'application/vnd.oasis.opendocument.presentation-template', 'icon'=>'odp.gif'),
        'ods'  => array ('type'=>'application/vnd.oasis.opendocument.spreadsheet', 'icon'=>'ods.gif'),
        'ots'  => array ('type'=>'application/vnd.oasis.opendocument.spreadsheet-template', 'icon'=>'ods.gif'),
        'odc'  => array ('type'=>'application/vnd.oasis.opendocument.chart', 'icon'=>'odc.gif'),
        'odf'  => array ('type'=>'application/vnd.oasis.opendocument.formula', 'icon'=>'odf.gif'),
        'odb'  => array ('type'=>'application/vnd.oasis.opendocument.database', 'icon'=>'odb.gif'),
        'odi'  => array ('type'=>'application/vnd.oasis.opendocument.image', 'icon'=>'odi.gif'),

        'pct'  => array ('type'=>'image/pict', 'icon'=>'image.gif'),
        'pdf'  => array ('type'=>'application/pdf', 'icon'=>'pdf.gif'),
        'php'  => array ('type'=>'text/plain', 'icon'=>'text.gif'),
        'pic'  => array ('type'=>'image/pict', 'icon'=>'image.gif'),
        'pict' => array ('type'=>'image/pict', 'icon'=>'image.gif'),
        'png'  => array ('type'=>'image/png', 'icon'=>'image.gif'),

        'pps'  => array ('type'=>'application/vnd.ms-powerpoint', 'icon'=>'powerpoint.gif'),
        'ppt'  => array ('type'=>'application/vnd.ms-powerpoint', 'icon'=>'powerpoint.gif'),
        'pptx' => array ('type'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'icon'=>'pptx.gif'),
        'pptm' => array ('type'=>'application/vnd.ms-powerpoint.presentation.macroEnabled.12', 'icon'=>'pptm.gif'),
        'potx' => array ('type'=>'application/vnd.openxmlformats-officedocument.presentationml.template', 'icon'=>'potx.gif'),
        'potm' => array ('type'=>'application/vnd.ms-powerpoint.template.macroEnabled.12', 'icon'=>'potm.gif'),
        'ppam' => array ('type'=>'application/vnd.ms-powerpoint.addin.macroEnabled.12', 'icon'=>'ppam.gif'),
        'ppsx' => array ('type'=>'application/vnd.openxmlformats-officedocument.presentationml.slideshow', 'icon'=>'ppsx.gif'),
        'ppsm' => array ('type'=>'application/vnd.ms-powerpoint.slideshow.macroEnabled.12', 'icon'=>'ppsm.gif'),

        'ps'   => array ('type'=>'application/postscript', 'icon'=>'pdf.gif'),
        'qt'   => array ('type'=>'video/quicktime', 'icon'=>'video.gif'),
        'ra'   => array ('type'=>'audio/x-realaudio-plugin', 'icon'=>'audio.gif'),
        'ram'  => array ('type'=>'audio/x-pn-realaudio-plugin', 'icon'=>'audio.gif'),
        'rhb'  => array ('type'=>'text/xml', 'icon'=>'xml.gif'),
        'rm'   => array ('type'=>'audio/x-pn-realaudio-plugin', 'icon'=>'audio.gif'),
        'rtf'  => array ('type'=>'text/rtf', 'icon'=>'text.gif'),
        'rtx'  => array ('type'=>'text/richtext', 'icon'=>'text.gif'),
        'sh'   => array ('type'=>'application/x-sh', 'icon'=>'text.gif'),
        'sit'  => array ('type'=>'application/x-stuffit', 'icon'=>'zip.gif'),
        'smi'  => array ('type'=>'application/smil', 'icon'=>'text.gif'),
        'smil' => array ('type'=>'application/smil', 'icon'=>'text.gif'),
        'sqt'  => array ('type'=>'text/xml', 'icon'=>'xml.gif'),
        'svg'  => array ('type'=>'image/svg+xml', 'icon'=>'image.gif'),
        'svgz' => array ('type'=>'image/svg+xml', 'icon'=>'image.gif'),
        'swa'  => array ('type'=>'application/x-director', 'icon'=>'flash.gif'),
        'swf'  => array ('type'=>'application/x-shockwave-flash', 'icon'=>'flash.gif'),
        'swfl' => array ('type'=>'application/x-shockwave-flash', 'icon'=>'flash.gif'),

        'sxw'  => array ('type'=>'application/vnd.sun.xml.writer', 'icon'=>'odt.gif'),
        'stw'  => array ('type'=>'application/vnd.sun.xml.writer.template', 'icon'=>'odt.gif'),
        'sxc'  => array ('type'=>'application/vnd.sun.xml.calc', 'icon'=>'odt.gif'),
        'stc'  => array ('type'=>'application/vnd.sun.xml.calc.template', 'icon'=>'odt.gif'),
        'sxd'  => array ('type'=>'application/vnd.sun.xml.draw', 'icon'=>'odt.gif'),
        'std'  => array ('type'=>'application/vnd.sun.xml.draw.template', 'icon'=>'odt.gif'),
        'sxi'  => array ('type'=>'application/vnd.sun.xml.impress', 'icon'=>'odt.gif'),
        'sti'  => array ('type'=>'application/vnd.sun.xml.impress.template', 'icon'=>'odt.gif'),
        'sxg'  => array ('type'=>'application/vnd.sun.xml.writer.global', 'icon'=>'odt.gif'),
        'sxm'  => array ('type'=>'application/vnd.sun.xml.math', 'icon'=>'odt.gif'),

        'tar'  => array ('type'=>'application/x-tar', 'icon'=>'zip.gif'),
        'tif'  => array ('type'=>'image/tiff', 'icon'=>'image.gif'),
        'tiff' => array ('type'=>'image/tiff', 'icon'=>'image.gif'),
        'tex'  => array ('type'=>'application/x-tex', 'icon'=>'text.gif'),
        'texi' => array ('type'=>'application/x-texinfo', 'icon'=>'text.gif'),
        'texinfo'  => array ('type'=>'application/x-texinfo', 'icon'=>'text.gif'),
        'tsv'  => array ('type'=>'text/tab-separated-values', 'icon'=>'text.gif'),
        'txt'  => array ('type'=>'text/plain', 'icon'=>'text.gif'),
        'wav'  => array ('type'=>'audio/wav', 'icon'=>'audio.gif'),
        'wmv'  => array ('type'=>'video/x-ms-wmv', 'icon'=>'avi.gif'),
        'asf'  => array ('type'=>'video/x-ms-asf', 'icon'=>'avi.gif'),
        'xdp'  => array ('type'=>'application/pdf', 'icon'=>'pdf.gif'),
        'xfd'  => array ('type'=>'application/pdf', 'icon'=>'pdf.gif'),
        'xfdf' => array ('type'=>'application/pdf', 'icon'=>'pdf.gif'),

        'xls'  => array ('type'=>'application/vnd.ms-excel', 'icon'=>'excel.gif'),
        'xlsx' => array ('type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'icon'=>'xlsx.gif'),
        'xlsm' => array ('type'=>'application/vnd.ms-excel.sheet.macroEnabled.12', 'icon'=>'xlsm.gif'),
        'xltx' => array ('type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.template', 'icon'=>'xltx.gif'),
        'xltm' => array ('type'=>'application/vnd.ms-excel.template.macroEnabled.12', 'icon'=>'xltm.gif'),
        'xlsb' => array ('type'=>'application/vnd.ms-excel.sheet.binary.macroEnabled.12', 'icon'=>'xlsb.gif'),
        'xlam' => array ('type'=>'application/vnd.ms-excel.addin.macroEnabled.12', 'icon'=>'xlam.gif'),

        'xml'  => array ('type'=>'application/xml', 'icon'=>'xml.gif'),
        'xsl'  => array ('type'=>'text/xml', 'icon'=>'xml.gif'),
        'zip'  => array ('type'=>'application/zip', 'icon'=>'zip.gif')
    );
}

/**
 * Obtains information about a filetype based on its extension. Will
 * use a default if no information is present about that particular
 * extension.
 * @param string $element Desired information (usually 'icon'
 *   for icon filename or 'type' for MIME type)
 * @param string $filename Filename we're looking up
 * @return string Requested piece of information from array
 */
function mimeinfo($element, $filename) {
    static $mimeinfo = null;
    if (is_null($mimeinfo)) {
        $mimeinfo = get_mimetypes_array();
    }

    if (preg_match('/\.([a-zA-Z0-9]+)$/', $filename, $match)) {
        if (isset($mimeinfo[strtolower($match[1])][$element])) {
            return $mimeinfo[strtolower($match[1])][$element];
        } else {
            return $mimeinfo['xxx'][$element];   // By default
        }
    } else {
        return $mimeinfo['xxx'][$element];   // By default
    }
}
// function that checks if admin user is logged in.
function require_admin() {
    $adminUser = null;
    session_start();
    if( isset($_SESSION) ) {
        $adminUser = User::RetrieveBySessionData($_SESSION);

        // Nullify user if they don't have permission
        if( isset($adminUser) &&  !$adminUser->getPermissionManager()->hasRight(PermissionManager::RIGHT_GENERAL_ADMIN) ) {
            $adminUser = null;
        }
    }

    // Stop, if user not valid
    if(!isset($adminUser) ) {
        error('Admin user not logged in');
    } else {
        return $adminUser;
    }
}

/**
 * Import users
 *
 * @param array $format csv keys
 * @param mixed $data The user data to be imported
 * @param User $adminUser currently signed in admin user
 *
 **/
function import_users($format, $data, $adminUser) {

    $db = Database::getInstance();
    $mandatoryfields = array(
        'firstname',
        'lastname',
        'email',
        'username'
    );

    $i = 0;
    foreach ($data as $key => $record) {
        $i++;

        $userdata = array();
        $missing = false;

        // ensure required fields set per user
        foreach ($mandatoryfields as $field) {
            if (isset($record[$format[$field]]) && !empty($record[$format[$field]])) {
                if ($field === 'email') {
                    if (validate_email($record[$format[$field]])) {
                        $userdata[$field] = validate_email($record[$format[$field]]);
                    } else {
                        add_error_msg("Required field '$field' not a valid format in CSV file for user at line $i.");
                        $missing = true;
                    }
                } else {
                    $userdata[$field] = $record[$format[$field]];
                }
            } else {
                add_error_msg("Required field '$field' missing in CSV file for user at line $i.");
                $missing = true;
            }
        }

        // if missing mandatory fields carry on to the next user
        if ($missing) {
            continue;
        }

        // sort out remaining non-mandatory fields
        $userdata['description'] = (!empty($record[$format['description']])) ? $record[$format['description']] : '';
        $userdata['password']    = (!empty($record[$format['password']])) ? $record[$format['password']] : generatePassword();
        $userdata['usertype']    = (!empty($record[$format['usertype']])) ? $record[$format['usertype']] : 'student';

        if (!empty($record[$format['institution']])) {
            //get institution id based on institution url above.
            $sqlUser = "SELECT * from institution WHERE url='".$record[$format['institution']]."'";
            $result = $db->query($sqlUser);
            $row = mysql_fetch_assoc($result);
            if (empty($row)) {
                add_info_msg("Couldn't find institution '".$record[$format['institution']]."' for user at $i - using default instead");
                $institutionId = 1;
            } else {
                $institutionId = $row['id'];
            }
        } else {
            $institutionId = 1;
        }

        //TODO: check for SQL injection here. (and in ajax.dispatcher where this is used)
        try {
             $permissionManager = PermissionManager::Create(
                 $userdata['username'],
                 $userdata['password'],
                 $userdata['usertype'],
                 $adminUser
             );
        }
         catch(Exception $e) {
             die($e->getMessage());
         }

        $institution = new Institution($institutionId);
        $user = User::CreateNewUser(
            $userdata['firstname'],
            $userdata['lastname'],
            $userdata['email'],
            $userdata['description'],
             $permissionManager,
             $institution
         );

        if($user->isUnique()) {
            $user->Save($adminUser);

            // add for reporting to admin
            $addedusers[$user->getId()]['fullname'] = $userdata['firstname'] . ' ' . $userdata['lastname'];
            $addedusers[$user->getId()]['institution'] = $institution->getUrl();
        } else {
            add_error_msg("User '" . $userdata['firstname'] . " " . $userdata['lastname'] . "' already existed.");
        }
    }

    // user reporting
    if (isset($addedusers) && !empty($addedusers)) {
        $addedstr = "Added users:<br />";
        foreach ($addedusers as $key => $value) {
            $addedstr .= "\t\t ".$value['fullname']." to ".$value['institution']."<br />";
        }
        add_info_msg($addedstr);
    }
}

/**
 * Validate a users email address
 *
 * @param string $email
 * @return boolean
 */
function validate_email($address) {

    return (ereg('^[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+'.
                 '(\.[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+)*'.
                  '@'.
                  '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.
                  '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$',
                  $address));

}

/**
 * Dump a given object's information in a PRE block.
 *
 * Mostly just used for debugging.
 *
 * @param mixed $object The data to be printed
 */
function print_object($object) {
    echo '<pre class="notifytiny">' . htmlspecialchars(print_r($object,true)) . '</pre>';
}
function notify($message) {
    echo "<p style='color:red;'><strong>$message</strong></p>";
}
//function user to print errors and die.
function error($message, $return ='') {
    if (!empty($return)) {
        add_error_msg($message);
        header('Location: '. $return);
    } else {
        notify($message);
    }
    die;
}

    //Function to delete all the directory contents recursively
    //it supports a excluded dit too
    //Copied from the web !!
    function delete_dir_recursive ($dir,$excludeddir="") {

        if (!is_dir($dir)) {
            // if we've been given a directory that doesn't exist yet, return true.
            // this happens when we're trying to clear out a course that has only just
            // been created.
            return true;
        }
        $slash = "/";

        // Create arrays to store files and directories
        $dir_files      = array();
        $dir_subdirs    = array();

        // Make sure we can delete it
        chmod($dir, 0777);

        if ((($handle = opendir($dir))) == FALSE) {
            // The directory could not be opened
            return false;
        }

        // Loop through all directory entries, and construct two temporary arrays containing files and sub directories
        while(false !== ($entry = readdir($handle))) {
            if (is_dir($dir. $slash .$entry) && $entry != ".." && $entry != "." && $entry != $excludeddir) {
                $dir_subdirs[] = $dir. $slash .$entry;
            }
            else if ($entry != ".." && $entry != "." && $entry != $excludeddir) {
                $dir_files[] = $dir. $slash .$entry;
            }
        }

        // Delete all files in the curent directory return false and halt if a file cannot be removed
        for($i=0; $i<count($dir_files); $i++) {
            chmod($dir_files[$i], 0777);
            if (((unlink($dir_files[$i]))) == FALSE) {
                return false;
            }
        }

        // Empty sub directories and then remove the directory
        for($i=0; $i<count($dir_subdirs); $i++) {
            chmod($dir_subdirs[$i], 0777);
            if (delete_dir_recursive($dir_subdirs[$i]) == FALSE) {
                return false;
            }
            else {
                if (remove_dir($dir_subdirs[$i]) == FALSE) {
                return false;
                }
            }
        }

        // Close directory
        closedir($handle);
        //remove actual dir
        remove_dir($dir);
        // Success, every thing is gone return true
        return true;
    }
/**
 * Delete directory or only it's content
 * @param string $dir directory path
 * @param bool $content_only
 * @return bool success, true also if dir does not exist
 */
function remove_dir($dir, $content_only=false) {
    if (!file_exists($dir)) {
        // nothing to do
        return true;
    }
    $handle = opendir($dir);
    $result = true;
    while (false!==($item = readdir($handle))) {
        if($item != '.' && $item != '..') {
            if(is_dir($dir.'/'.$item)) {
                $result = remove_dir($dir.'/'.$item) && $result;
            }else{
                $result = unlink($dir.'/'.$item) && $result;
            }
        }
    }
    closedir($handle);
    if ($content_only) {
        return $result;
    }
    return rmdir($dir); // if anything left the result will be false, noo need for && $result
}

//password function copied from web:
//http://www.phptoys.com/e107_plugins/content/content.php?content.42
function generatePassword($length=6,$level=2){

   list($usec, $sec) = explode(' ', microtime());
   srand((float) $sec + ((float) $usec * 100000));

   $validchars[1] = "0123456789abcdfghjkmnpqrstvwxyz";
   $validchars[2] = "0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
   $validchars[3] = "0123456789_!@#$%&*()-=+/abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_!@#$%&*()-=+/";

   $password  = "";
   $counter   = 0;

   while ($counter < $length) {
     $actChar = substr($validchars[$level], rand(0, strlen($validchars[$level])-1), 1);

     // All character must be different
     if (!strstr($password, $actChar)) {
        $password .= $actChar;
        $counter++;
     }
   }

   return $password;

}

// functionality for using $_SESSION to report back to user

/**
 * Create a session, by initialising the $_SESSION array.
 */
function ensure_session() {
    if (empty($_SESSION)) {
        if (!session_id()) {
            @session_start();
        }
        $_SESSION = array(
            'messages' => array()
        );
    }
}

/**
 * Adds a message that indicates something was successful
 *
 * @param string $message The message to add
 * @param boolean $escape Whether to HTML escape the message
 */
function add_ok_msg($message) {
    ensure_session();
    $_SESSION['messages'][] = array('type' => 'ok', 'msg' => $message);
}

/**
 * Adds a message that indicates an informational message
 *
 * @param string $message The message to add
 * @param boolean $escape Whether to HTML escape the message
 */
function add_info_msg($message) {
    ensure_session();
    $_SESSION['messages'][] = array('type' => 'info', 'msg' => $message);
}

/**
 * Adds a message that indicates a failure to do something
 *
 * @param string $message The message to add
 * @param boolean $escape Whether to HTML escape the message
 */
function add_error_msg($message) {
    ensure_session();
    $_SESSION['messages'][] = array('type' => 'error', 'msg' => $message);
}

/**
 * Builds HTML that represents all of the messages and returns it.
 *
 * This is designed to let smarty templates hook in any session messages.
 *
 * Calling this function will destroy the session messages that were
 * rendered, so they do not inadvertently get displayed again.
 *
 * @return string The HTML representing all of the session messages.
 */
function render_messages() {
    $result = '<div id="messages">';
    if (isset($_SESSION['messages'])) {
        foreach ($_SESSION['messages'] as $data) {
            $result .= '<div class="' . $data['type'] . '"><p>';
            $result .= $data['msg'] . '</p></div>';
            error_log($data['msg']); // write to standard error log for good measure
        }
        $_SESSION['messages'] = array();
    }
    $result .= '</div>';
    return $result;
}

function get_config($name) {
    global $db;
        if ($db->table_exists('config')) { //check to make sure config table exists first.
            $sqlUser = "SELECT * from config WHERE name='".$name."'";
            $result = $db->query($sqlUser);
            $row = mysql_fetch_assoc($result);
            return $row['value'];
        } else {
            return 0;
        }
}
//set config var in config table. - used to store In-folio version and other site config options.
function set_config($name, $value) {
    global $db;

    $existing = get_config($name);
    if (isset($existing)) {
        $data = array(
                  'value' => (string)$value
                  );
        $db->perform('config', $data, Database::UPDATE, "name='$name'");
    } elseif($existing <> $value) {
        $data = array(
                  'name' => (string)$name,
                  'value' => (string)$value
                  );
        $db->perform('config', $data);
    }
}

//check if upgrade is needed - if so, trigger it.
function check_upgrade() {
    require(DIR_FS_ROOT."version.php");
    $oldversion = get_config('version');
    if ($version > $oldversion) {
        require(DIR_FS_ROOT."db/upgrade.php");
        db_main_upgrade($oldversion);
    }
}

function curURL() {
 $curURL = 'http';
 if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$curURL .= "s";}
 $curURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $curURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
 } else {
  $curURL .= $_SERVER["SERVER_NAME"];
 }
 return $curURL;
}

//used to generate random hash
function newsharehash() {
    return md5(mt_rand());
}
