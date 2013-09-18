/* Demo Note:  This demo uses a FileProgress class that handles the UI for displaying the file name and percent complete.
The FileProgress class is not part of SWFUpload.
*/


/* **********************
   Event Handlers
   These are my custom event handlers to make my
   web application behave the way I went when SWFUpload
   completes different tasks.  These aren't part of the SWFUpload
   package.  They are part of my application.  Without these none
   of the actions SWFUpload makes will show up in my application.
   ********************** */
function fileQueued(file) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("<span class=status_ready>等待上传...</span>");
		progress.toggleCancel(true, this);

	} catch (ex) {
		this.debug(ex);
	}

}

function fileQueueError(file, errorCode, message) {
	try {
		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert(message === 0 ? "您本次上传的文件数已达到了上限." : (message > 1 ?  "您最多只能选择 " + message + " 个文件." : "您选择的文件数或本次上传的文件数已达到上限."));
			return;
		}

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			progress.setStatus("<span class=status_warning>文件太大!</span>");
			this.debug("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			progress.setStatus("<span class=status_warning>0字节文件!</span>");
			this.debug("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
			progress.setStatus("<span class=status_warning>类型无效!</span>");
			this.debug("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		default:
			if (file !== null) {
				progress.setStatus("<span class=status_warning>未知错误!</span>");
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
		//if (numFilesSelected > 0) {//有错误的文件时, 也可以取消, 无用
			//$$(this.customSettings.cancelButtonId).disabled = false;
		//}

		if (numFilesQueued > 0) {
			var the_cancel_btn = $$(this.customSettings.cancelButtonId);
			var the_upload_btn = $$(this.customSettings.uploadButtonId);

			the_cancel_btn.disabled = false;
			the_cancel_btn.className = 'btnCancel';//仅当上传文件有效进入队列时, 允许取法上传

			the_upload_btn.disabled = false;//仅当上传文件有效进入队列时, 允许上传
			the_upload_btn.className = 'btnUpload';
		}

		this.chFilesStatus(); //改变文件提示信息
		
		/* 选择文件后立即上传 */
		//this.startUpload();
	} catch (ex)  {
        this.debug(ex);
	}
}

function uploadStart(file) {
	try {
		/* I don't want to do any file validation or anything,  I'll just update the UI and
		return true to indicate that the upload should start.
		It's important to update the UI here because in Linux no uploadProgress events are called. The best
		we can do is say we are uploading.
		 */
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("正在上传...");
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
		//progress.setStatus("正在上传...");
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadSuccess(file, serverData, response) {
	
	//alert(serverData + '---' + response);  
	
	//服务器upload.php程序返回数据serverData, response参数表示是否有回应(true|false)
	// 通过检测upload.php返回的数据，可以进一下告诉UI，上传的文件是否已经成功保存等信息.

	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setComplete();
		progress.setStatus("<span class=status_complete>上传成功.</span>");
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
			switch (message) {
			case '530':
				message = '您无权限上传!';
				break;
			case '531':
				message = '无效的上传文件!';
				break;
			case '532':
				message = '文件类型不允许!';
				break;
			case '533':
				message = '上传的文件太大!';
				break;
			case '534':
				message = '保存文件失败!';
				break;
			case '535':
				message = '服务器不支持GD2!';
				break;
			case '536':
				message = '文件夹不存在!';
				break;
			case '537':
				message = '保存文件夹不可写!';
				break;
			}
			progress.setStatus("<span class=status_warning>错误: " + message + "</span>");
			this.debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			progress.setStatus("<span class=status_warning>上传失败.</span>");
			this.debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			progress.setStatus("<span class=status_warning>服务器错误(IO)</span>");
			this.debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			progress.setStatus("<span class=status_warning>权限错误.</span>");
			this.debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			progress.setStatus("<span class=status_warning>上传数量错误.</span>");
			this.debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
			progress.setStatus("<span class=status_warning>文件无效.</span>");
			this.debug("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			// If there aren't any files left (they were all cancelled) disable the cancel button
			if (this.getStats().files_queued === 0) {
				var the_cancel_btn = $$(this.customSettings.cancelButtonId);
				var the_upload_btn = $$(this.customSettings.uploadButtonId);

				the_cancel_btn.disabled = true;
				the_cancel_btn.className = 'btnCancel_disabled';

				the_upload_btn.disabled = true;
				the_upload_btn.className = 'btnUpload_disabled';
			}
			progress.setStatus("<span class=status_warning>已取消上传!</span>");
			//progress.setCancelled();  
			//取消: progress.setError()中已经做了相同的动作

			this.chFilesStatus();//更新文件提示信息
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			progress.setStatus("<span class=status_warning>终止上传.</span>");
			break;
		default:
			progress.setStatus("<span class=status_warning>未知错误: " + errorCode + "</span>");
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function uploadComplete(file) {
	if (this.getStats().files_queued === 0) {
		var the_cancel_btn = $$(this.customSettings.cancelButtonId);
		var the_upload_btn = $$(this.customSettings.uploadButtonId);

		the_cancel_btn.disabled = true;
		the_cancel_btn.className = 'btnCancel_disabled';

		the_upload_btn.disabled = true;
		the_upload_btn.className = 'btnUpload_disabled';
	}

}

// This event comes from the Queue Plugin
function uploadCompleteInfo(numFilesUploaded) {
	var filesStatus = $$(this.customSettings.filesStatusId);
	if (filesStatus){
		filesStatus.innerHTML = '<span class="num_selected">' + numFilesUploaded + '</span> 个文件上传成功.';
	}
}


