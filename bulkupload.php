<?php

session_start();
include_once("system/initialise.php");
include_once("model/User.class.php");
include_once("function/shared.php");
include_once("function/core.php");

// Turn on autodetecting of line endings, so mac newlines (\r) will work
ini_set('auto_detect_line_endings', 1);
define('MAX_LINE_LENGTH', 1024);

$allowedkeys = array(
    'firstname',
    'lastname',
    'email',
    'description',
    'username',
    'password',
    'usertype',
    'institution',
);

$adminUser = User::RetrieveById($_POST['adminuser']);
$returnurl = $adminUser->getInstitution()->getUrl() . '/' . DIR_WS_ADMIN . '?do=' . SECTION_USER;

if ($_FILES['bulk-user-file']['error'] != UPLOAD_ERR_OK) {
    file_upload_error_message($_FILES['bulk-user-file']);
    header('Location: ' . $returnurl);
    exit;
} else {
    if (file_exists($_FILES['bulk-user-file']['tmp_name'])) {
        if (($handle = fopen($_FILES['bulk-user-file']['tmp_name'], 'r')) !== false) {
            $data = array();
            $format = array();
            $i = 0;
            while (($line = fgetcsv($handle, MAX_LINE_LENGTH)) !== false) {
                $i++;
                if ($i == 1) {
                    foreach ($line as $potentialkey) {
                        if (!empty($potentialkey)) {
                            $potentialkey = trim($potentialkey);
                            if (!in_array($potentialkey, $allowedkeys)) {
                                add_error_msg('Invalid key "'. $potentialkey .'" in CSV file.');
                                header('Location: '. $returnurl); // return to alert user
                                exit;
                            }
                            $format[] = $potentialkey;
                        }
                    }

                    // Now we know all of the field names are valid, we need to make
                    // sure that the required fields are included
                    $mandatoryfields = array(
                        'firstname',
                        'lastname',
                        'email',
                        'username'
                    );
                    foreach ($mandatoryfields as $field) {
                        if (!in_array($field, $line)) {
                            add_error_msg("Required key '$field' missing in CSV file.");
                            header('Location: '. $returnurl); // return to alert user
                            exit;
                        }
                    }
                } else {
                    // Trim non-breaking spaces -- they get left in place by File_CSV
                    foreach ($line as &$field) {
                        $field = preg_replace('/^(\s|\xc2\xa0)*(.*?)(\s|\xc2\xa0)*$/', '$2', $field);
                    }

                    // All OK!
                    $data[] = $line;
                }
            }
            fclose($handle);
        }
    } else {
        add_error_msg('could not open file: '.$_FILES['bulk-user-upload']['name']);
        header('Location: '. $returnurl);
        exit;
    }

    $formatkeylookup = array_flip($format);

    if ($i == 1) {
        // There was only the title row :(
        add_error_msg('No records found in the CSV file.');
        header('Location: '. $returnurl);
        exit;
    }

    if ($data === null) {
        // Oops! Couldn't get CSV data for some reason
        add_error_msg('Couldn\'t retrieve any data from the CSV file.');
        header('Location: '. $returnurl);
        exit;
    }
    // Everything good: import the users
    import_users($formatkeylookup, $data, $adminUser);

    // redirect back to correct page at end of script
    header('Location: '. $returnurl);
    exit;
}
?>
