/*
	A simple class for displaying file information and progress
	Note: This is a demonstration only and not part of SWFUpload.
	Note: Some have had problems adapting this class in IE7. It may not be suitable for your application.
*/

// Constructor
// file is a SWFUpload file object
// targetID is the HTML element id attribute that the FileProgress HTML structure will be added to.
// Instantiating a new FileProgress object with an existing file will reuse/update the existing DOM elements


function FileProgress(file, targetID) {
	this.fileProgressID = file.id;

	this.opacity = 100;
	this.height = 0;

	this.fileProgressRow = $$(this.fileProgressID);

	if (!this.fileProgressRow) {
		var cell, file_list;

		this.fileProgressRow = document.createElement("tr");
		//this.fileProgressRow.className = "progressRow88";
		this.fileProgressRow.setAttribute("id", this.fileProgressID);

		cell=document.createElement("td");
		cell.innerHTML = file.index + 1;//文件编号
		this.fileProgressRow.appendChild(cell);

		cell=document.createElement("td");
		cell.className = 'swf_align_left';
		cell.innerHTML = '<div class=filename>' + file.name + '</div>';//文件名
		this.fileProgressRow.appendChild(cell);

		cell=document.createElement("td");
		cell.innerHTML = this.getFilesize(file.size);//文件大小
		this.fileProgressRow.appendChild(cell);

		cell=document.createElement("td");
		cell.className = 'swf_align_left';
		cell.innerHTML = '';//文件状态
		this.fileProgressRow.appendChild(cell);

		cell=document.createElement("td");
		cell.innerHTML = '<a href="#" hidefocus="true" title="取消上传" onfocus="this.blur();" class="progressCancel" style="visibility: hidden;"></a>';
		this.fileProgressRow.appendChild(cell);

		file_list = $$(targetID);

		if(file_list){
			file_list.appendChild(this.fileProgressRow);
		}

	} else {
		this.reset();
	}

	this.height = this.fileProgressRow.offsetHeight;
	this.setTimer(null);
}


FileProgress.prototype.setTimer = function (timer) {
	this.fileProgressRow["FP_TIMER"] = timer;
};


FileProgress.prototype.getTimer = function (timer) {
	return this.fileProgressRow["FP_TIMER"] || null;
};


//重置
FileProgress.prototype.reset = function () {
	//这里添加一些需要在重置时更新的处理
	//this.fileProgressElement.childNodes[2].innerHTML = "&nbsp;";
	//this.fileProgressRow.childNodes[3].innerHTML = "<div></div>";
	//this.fileProgressRow.childNodes[3].childNodes[0].className = "progressBarInProgress";
	//tthis.fileProgressRow.childNodes[3].childNodes[0].style.width = "0%";
	
	this.appear();	
};


FileProgress.prototype.setProgress = function (percentage) {
	this.fileProgressRow.childNodes[3].innerHTML = "<div></div>";
	this.fileProgressRow.childNodes[3].childNodes[0].className = "progressBarInProgress";
	this.fileProgressRow.childNodes[3].childNodes[0].style.width = percentage + "%";

	this.appear();	
};


FileProgress.prototype.setComplete = function () {
	//取消上传完成后定时消失

	//var oSelf = this;
	//this.setTimer(setTimeout(function () {
		//oSelf.disappear();
	//}, 10000));
};


FileProgress.prototype.setError = function () {
	this.fileProgressRow.className = "row_cancel";

	var oSelf = this;
	this.setTimer(setTimeout(function () {
		oSelf.disappear();
	}, 10000));
};

//setCancelled() 同上面的 setError()函数
FileProgress.prototype.setCancelled = function () {
	this.fileProgressRow.className = "row_cancel";

	var oSelf = this;
	this.setTimer(setTimeout(function () {
		oSelf.disappear();
	}, 2000));
};


FileProgress.prototype.setStatus = function (status) {
	this.fileProgressRow.childNodes[3].innerHTML = status;
};


// Show/Hide the cancel button
FileProgress.prototype.toggleCancel = function (show, swfUploadInstance) {
	this.fileProgressRow.childNodes[4].childNodes[0].style.visibility = show ? "visible" : "hidden";
	if (swfUploadInstance) {
		var fileID = this.fileProgressID;
		this.fileProgressRow.childNodes[4].childNodes[0].onclick = function () {
			swfUploadInstance.cancelUpload(fileID);
			return false;
		};
	}
};


FileProgress.prototype.appear = function () {
	if (this.getTimer() !== null) {
		clearTimeout(this.getTimer());
		this.setTimer(null);
	}
	
	if (this.fileProgressRow.filters) {
		try {
			this.fileProgressRow.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 100;
		} catch (e) {
			// If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
			this.fileProgressRow.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=100)";
		}
	} else {
		this.fileProgressRow.style.opacity = 1;
	}
		
	this.fileProgressRow.style.height = "";
	
	this.height = this.fileProgressRow.offsetHeight;
	this.opacity = 100;
	this.fileProgressRow.style.display = "";
	
};


// Fades out and clips away the FileProgress box.
FileProgress.prototype.disappear = function () {

	var reduceOpacityBy = 15;
	var reduceHeightBy = 4;
	var rate = 30;	// 15 fps

	if (this.opacity > 0) {
		this.opacity -= reduceOpacityBy;
		if (this.opacity < 0) {
			this.opacity = 0;
		}

		if (this.fileProgressRow.filters) {
			try {
				this.fileProgressRow.filters.item("DXImageTransform.Microsoft.Alpha").opacity = this.opacity;
			} catch (e) {
				// If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
				this.fileProgressRow.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + this.opacity + ")";
			}
		} else {
			this.fileProgressRow.style.opacity = this.opacity / 100;
		}
	}

	if (this.height > 0) {
		this.height -= reduceHeightBy;
		if (this.height < 0) {
			this.height = 0;
		}

		this.fileProgressRow.style.height = this.height + "px";
	}

	if (this.height > 0 || this.opacity > 0) {
		var oSelf = this;
		this.setTimer(setTimeout(function () {
			oSelf.disappear();
		}, rate));
	} else {
		this.fileProgressRow.style.display = "none";
		this.setTimer(null);
	}
};


//my new function
FileProgress.prototype.getFilesize = function (filesize) {
	if(filesize >= 0 && filesize < 1024) {
		filesize = filesize + ' B';
	} else if (filesize >= 1024 && filesize < 1048576) {
		filesize = Math.floor(filesize/1024) + ' K';
	} else if (filesize >= 1048576 && filesize < 1073741824) {
		filesize = (filesize/1048576).toFixed(1) + ' M';
	} else if (filesize >= 1073741824) {
		filesize =(filesize/1073741824).toFixed(2) + ' G';
	}

	return filesize;
};
