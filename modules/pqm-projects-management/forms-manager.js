function FormsManager() {
	var _this = this;
	
	this.actions = {};
	
	this.formErrorsZoneHtmlTpl = 
	"<div class='ui-widget ui-helper-hidden' id='errorblock-div1'>" +
		"<div class='ui-state-error ui-corner-all' style='padding: 0pt 0.7em;' id='errorblock-div2' style='display:none;'>" +
			"<p><span class='ui-icon ui-icon-alert' style='float: left; margin-right: 0.3em;'></span><strong>Alert:</strong> Errors detected!</p>" +
			"<ul></ul>" +
		"</div>" +
	"</div>";
}

FormsManager.prototype = {
	/**
	*	@param options An object with options like the following:
			{validations: {name:{required:true}, voltage:{required:true, number:true}}}
	
	*/
	registerAlias: function(alias, formTpl, createHandler, updateHandler, options) {
		this.actions[alias] = {formTpl: formTpl, 
							   createHandler: createHandler, 
							   updateHandler: updateHandler,
							   options: options};
	},
	
	create: function(alias, fnCallback, params) {
		var action = this.actions[alias];
		var form = this._loadFormTpl(action.formTpl, action.createHandler);
		this._buildDialog(form, "Create", fnCallback, params, action.options);
	},
	
	update: function(alias, obj, fnCallback, params) {
		var action = this.actions[alias];
		var form = this._loadFormTpl(action.formTpl, action.updateHandler);
		this._buildUpdateDialog(obj, form, "Update", fnCallback, params, action.options);
	},
	
	/* Auxiliary functions */
	
	_loadFormTpl: function(tplPath, submitHandler) {
		var form;
		$.ajax({
			url: tplPath,
			dataType: 'html',
			async: false,
			success: function(tpl) {
				form = $(tpl).attr("action", submitHandler);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert(textStatus + ": " + errorThrown);
			}
		});
		return form;
	},
	
	_buildDialog: function(form, title, fnCallback, params, options) {
		// Append hidden fields to form if there are extra params to send
		if(params) {
			for(key in params) {
				form.append($("<input type='hidden' name='" + key + "' value='" + params[key] + "'>"));
			}
		}
	
		// Build dialog 
		this._doDialog(form, title, fnCallback, options);
	},
	
	_buildUpdateDialog: function(obj, form, title, fnCallback, params, options) {
		// Append hidden fields to form if there are extra params to send
		if(params) {
			for(key in params) {
				form.append($("<input type='hidden' name='" + key + "' value='" + params[key] + "'>"));
			}
		}
		
		// Show values in fields
		for(key in obj) {
			$("#" + key, form).attr("value", obj[key]);
		}
		
		this._doDialog(form, title, fnCallback, options);
	},
	
	_doDialog: function(form, title, fnCallback, options) {
		_this = this;
		_this._setupValidations(form, fnCallback, options.validations);
		var dialog = $("<div>").append(_this.formErrorsZoneHtmlTpl).append(form);
		$('body').append(dialog);
		dialog.find(':submit').hide();
		dialog.dialog({
			title: title ? title : '',
			modal: true,
			buttons: {
			  'Save': function() {form.submit();$(this).dialog('close');},
			  'Cancel': function() {$(this).dialog('close');}
			},
			close: function() {$(this).remove();},
			width: 'auto'
		});
	},
	
	_setupValidations: function(form, fnCallback, rules) {
		_this = this;
		var aform = form.validate({
			errorContainer: "#errorblock-div1, #errorblock-div2",
			errorLabelContainer: "#errorblock-div2 ul",
			wrapper: "li",
			rules: rules,
			/*messages: {
				name: {
					required: "Please enter name."
				},
				voltage: {
					required: "Please enter voltage.",
					number: "Please enter a valid voltage."
				}
			},*/
			submitHandler: function(form) {
				_this._submitFormWithAjax(form, fnCallback);
			 }
		});
	},
	
	_submitFormWithAjax: function(form, fnCallback) {
		form = $(form);
		$.ajax({
			url: form.attr('action'),
			data: form.serialize(),
			type: form.attr('method'),
			dataType: 'json',
			async: false,
			success: function(data) {
				fnCallback(data);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert(textStatus + ": " + errorThrown);
			}
		});
		return false;
	}
}
