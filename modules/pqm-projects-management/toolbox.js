function _overrideProperties(first, second){
    for (var prop in second){
        first[prop] = second[prop];
    }
};

function Toolbox(o) {
	var _this = this;
	
	this.uploadButton = undefined; // FIX: this allows only one upload button. Should allow more...
	
	this._places = {
		toolbox: "toolbox",
	};
	
	if(o.places)_overrideProperties(this._places, o.places);

	this.buttons = [];
}

Toolbox.prototype = {
	addButton: function(btnClass, fnToExecute, fnGetParams) {
		btn = new ToolBoxButton(btnClass, fnToExecute, fnGetParams);
		this.buttons.push(btn);
		$("#" + this._places.toolbox).append(btn.toJQueryObject());
	},
	
	addUploadButton: function(btnClass, serverHandler, fnCallback, fnLazyParams) {
		var _this = this;
	
		btn = $("<div class='tool-button " + btnClass + "' id='button-upload-file'></div>");
		$("#" + this._places.toolbox).append(btn);
		this.uploadButton = new qq.FileUploader({
			element: document.getElementById("button-upload-file"),
			action: serverHandler,
			onComplete: function(id, fileName, responseJSON){
				fnCallback(responseJSON);
			},
			onError: function(id, fileName, reason) {
				alert(reason);
			},
			disableDefaultDropzone: true,
			//inputName: 'userfile',// the file as referenced in the server
			uploadButtonText: '',
		});
		// Get lazy params before sending to server
		btn.click(function() {
			_this.uploadButton.setParams(fnLazyParams());
		});
		this.buttons.push(btn);
	},
	
	hideButton: function(btnIndex) {
		this.buttons[btnIndex].hide();
	},
	
	hideAllButtons: function() {
		for(i in this.buttons) {
			this.buttons[i].hide();
		}
	},
	
	showButton: function(btnIndex) {
		this.buttons[btnIndex].show();
	},
	
	showAllButtons: function() {
		for(i in this.buttons) {
			this.buttons[i].show();
		}
	},

	bindButton: function(btnIndex, fnToExecute, fnGetParams) {
		this.buttons[btnIndex].bind(fnToExecute, fnGetParams);
	}
}

function ToolBoxButton(btnClass, fnToExecute, fnGetParams) {
	var _this = this;
	
	this.htmlTemplate = "<a href='#'><div class='tool-button " + btnClass + "'></div></a>";
	
	this.button = $(this.htmlTemplate);
	this.fnToExecute = fnToExecute;
	this.fnGetParams = fnGetParams;
	this.button.click(function(){
		if(!_this.fnToExecute) return;
		if(_this.fnGetParams) _this.fnToExecute(_this.fnGetParams());
		else _this.fnToExecute();
	});
}

ToolBoxButton.prototype = {
	toJQueryObject: function() {
		return this.button;
	},
	
	bind: function(fnToExecute, fnGetParams) {
		this.fnToExecute = fnToExecute;
		this.fnGetParams = fnGetParams;
	},
	
	hide: function() {
		this.button.hide();
	},
	
	show: function() {
		this.button.show();
	}
}
