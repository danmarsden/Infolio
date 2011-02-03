<!-- swfupload -->
<script type="text/javascript" src="/_scripts/jquery/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="/_scripts/swfupload/swfupload.js"></script>
<script type="text/javascript" src="/_scripts/fileprogress.js"></script>
<script type="text/javascript">
var swfu;

// swfuploader is hidden unless JS is enabled on the browser
function hideHtmlUploader() {
    var swfuploader = document.getElementById('swf-uploader');
    var htmluploader = document.getElementById('html-uploader');

    htmluploader.style.visibility = "hidden";
    swfuploader.style.visibility = "";
}

window.onload = function () {
    hideHtmlUploader();

    var settings_object = {
        flash_url : "/_flash/swfupload.swf",
        upload_url: "/swfupload.php",
        file_post_name : "Filedata", 
        post_params: {"PHPSESSID" : ""},
        file_size_limit : "100 MB",
        button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
        file_upload_limit : "1",
        file_types : "*",
        file_types_description : "Web image, video and audio files",
        custom_settings : {
            progressTarget : "fsUploadProgress",
            cancelButtonId : "btnCancel"
        },

        // we need to keep track of the current user
        // and page for attachment vs asset creation
        post_params: {
            userid: "<?php echo $_SESSION['userID']; ?>"
        },
        debug: false,

        // Button settings
        button_image_url: "/_images/si/browseBtn.png",
        button_width: "100",
        button_height: "28",
        button_placeholder_id: "spanButtonPlaceHolder",

        // Callback functions
        file_queued_handler : fileQueued,
        file_queue_error_handler : fileQueueError,
        file_dialog_complete_handler : fileDialogComplete,
        upload_start_handler : uploadStart,
        upload_progress_handler : uploadProgress,
        upload_error_handler : uploadError,
        upload_success_handler : uploadSuccess,
        upload_complete_handler : uploadComplete,
        queue_complete_handler : queueComplete	// Queue plugin event
    };
    swfu = new SWFUpload(settings_object);
};

/* Event Handlers */
function fileQueued(file) {
    try {
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setStatus("Pending...");
        progress.toggleCancel(true, this);

    } catch (ex) {
        this.debug(ex);
    }

}

function fileQueueError(file, errorCode, message) {
    try {
        if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
            alert("You have attempted to queue too many files.\n" + (message === 0 ? "You have reached the upload limit." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
            return;
        }

        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setError();
        progress.toggleCancel(false);

        switch (errorCode) {
        case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
            progress.setStatus("File is too big.");
            this.debug("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
        case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
            progress.setStatus("Cannot upload Zero Byte files.");
            this.debug("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
        case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
            progress.setStatus("Invalid File Type.");
            this.debug("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
        default:
            if (file !== null) {
                progress.setStatus("Unhandled Error");
            }
            this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
        }
    } catch (ex) {
        this.debug(ex);
    }
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
    try {
        if (numFilesSelected > 0) {
            document.getElementById(this.customSettings.cancelButtonId).disabled = false;
        }

        this.startUpload();
    } catch (ex)  {
        this.debug(ex);
    }
}

function uploadStart(file) {
    this.addPostParam('pageid', document.getElementById('pageid').value);
    try {
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setStatus("Uploading...");
        progress.toggleCancel(true, this);
    }
    catch (ex) {}

    return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
    try {
        var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setProgress(percent);
        progress.setStatus("Uploading...");
    } catch (ex) {
        this.debug(ex);
    }
}

function uploadSuccess(file, serverData) {
    try {
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setComplete();
        progress.setStatus("Complete.");
        progress.toggleCancel(false);

    } catch (ex) {
        this.debug(ex);
    }
}

function uploadError(file, errorCode, message) {
    try {
        var progress = new FileProgress(file, this.customSettings.progressTarget);
        progress.setError();
        progress.toggleCancel(false);

        switch (errorCode) {
        case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
            progress.setStatus("Upload Error: " + message);
            this.debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
            break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
            progress.setStatus("Upload Failed.");
            this.debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
        case SWFUpload.UPLOAD_ERROR.IO_ERROR:
            progress.setStatus("Server (IO) Error");
            this.debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
            break;
        case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
            progress.setStatus("Security Error");
            this.debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
            break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
            progress.setStatus("Upload limit exceeded.");
            this.debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
        case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
            progress.setStatus("Failed Validation.  Upload skipped.");
            this.debug("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
        case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
            // If there aren't any files left (they were all cancelled) disable the cancel button
            if (this.getStats().files_queued === 0) {
                document.getElementById(this.customSettings.cancelButtonId).disabled = true;
            }
            progress.setStatus("Cancelled");
            progress.setCancelled();
            break;
        case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
            progress.setStatus("Stopped");
            break;
        default:
            progress.setStatus("Unhandled Error: " + errorCode);
            this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
            break;
        }
    } catch (ex) {
        this.debug(ex);
    }
}

function uploadComplete(file) {
    if (this.getStats().files_queued === 0) {
        document.getElementById(this.customSettings.cancelButtonId).disabled = true;
    }
    // redirect back to the page so that the
    // user can see their newly added file
    var returnurl = document.getElementById('returnurl').value;
    window.location.href = returnurl;
}

// This event comes from the Queue Plugin
function queueComplete(numFilesUploaded) {
    var status = document.getElementById("divStatus");
    status.innerHTML = numFilesUploaded + " file" + (numFilesUploaded === 1 ? "" : "s") + " uploaded.";
}


</script>
