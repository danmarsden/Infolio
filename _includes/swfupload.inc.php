<!-- swfupload -->
<script type="text/javascript" src="/_scripts/jquery/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="/_scripts/swfupload/swfupload.js"></script>
<script type="text/javascript">
var swfu;
window.onload = function () {
    var settings_object = {
        // Basic settings
        flash_url: '/_flash/swfupload.swf',
        upload_url: 'swfupload.php',
        file_post_name: 'Filedata', /* officially recommended to leave this as 'Filedata' */
        file_size_limit: '300 MB',

        // Button settings
        button_placeholder_id: 'swfupload-container',
        button_image_url: '/_images/si/browseBtn.png',
        button_width: '100',
        button_height: '28',

        // Custom settings
        custom_settings: {
            upload_successful: false
        },

        // Callback functions
        file_queued_handler: fileQueued,
        upload_progress_handler: uploadProgress,
        upload_error_handler: uploadError,
        upload_success_handler: uploadSuccess,
        upload_complete_handler: uploadComplete
    };
    swfu = new SWFUpload(settings_object);
};

/**
 * This function places the name of the file in the empty text box we created earlier.
 * This serves no actual functional purpose. It's just a visual effect.
 */
function fileQueued(file) {
    console.log(file.name);
    $('filename-text').attr("value", file.name);
}

/**
 * This function tells SWFUpload to start uploading the queued files.
 */
function uploadFile(form, e) {
    try {
        swfu.startUpload();
    } catch (ex) {
    }
    return false;
}

/**
 * This function reads progress information.
 * It reveals the progress bar and expands the inner div to reflect upload progress.
 */
function uploadProgress(file, bytesLoaded, bytesTotal) {
    try {
        var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
        $('upload-progressbar-container').css({display:'block'});
        $('upload-progressbar').css({width:percent+'%'});
    } catch (e) {
    }
}

/**
 * This function is huge.
 */
function uploadError(file, errorCode, message) {
}

/**
 * This function handles data that is returned from the PHP script.
 * That data is just whatever the PHP script echos to the page (no JSON or XML).
 * This function expects an ID representing the new file and places it
 * in the hidden field in the form.
 * It also sets the custom 'upload_successful' parameter to true for use later.
 */
function uploadSuccess(file, serverData, receivedResponse) {
    try {
        if (serverData === " ") {
            this.customSettings.upload_successful = false;
        } else {
            this.customSettings.upload_successful = true;
            $('hidFileID').attr("value", serverData);
        }
    } catch (e) {
    }
}

/**
 * If the upload was completed successfully, this function calls the function
 * that submits the entire form via AJAX.
 * If the upload was unsuccessful, this function alerts an error message.
 */
function uploadComplete(file) {
    try {
        if (this.customSettings.upload_successful) {
            var form = $('create-file');
            submitForm(form); // Not described here.
            // The above function is not described here.
            // It is a function that receives a form element as input
            // and constructs POST data from all the user input found on that form.
            // It then submit that data via AJAX and calls fileUploaded();
        } else {
            alert('There was a problem with the upload.');
        }
    } catch (e) {
    }
}

/**
 * This file decides what do to after the entire form has been submitted.
 * It expects a JSON object with a 'message' and 'status' value.
 * The message value is always displayed to the user.
 * If the server returns a successful status, the upload and processing of form
 * data is complete and the user is redirected to another page.
 * If the server returns an unsuccessful status, the form submit action
 * is reconfigured to try submitted the form again rather than try the entire upload.
 */
function fileUploaded(json) {
    alert(json.message);
    if (json.status == 1) {
        window.location = 'upload_successful.php';
    } else {
        form.setAttribute('onsubmit','submitForm(this);return false;');
    }
}

</script>
