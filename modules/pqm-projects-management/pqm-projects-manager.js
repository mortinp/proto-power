function PQMProjectsManager(pathToThisFile) {
	var _this = this;
	
	this.pathToThisFile = pathToThisFile;
	
	this.formsMgr = new FormsManager(/*"/proto-power/tests/test_toolbox_forms/"*/);
	this.formsMgr.registerAlias("project", 
							   "/proto-power/modules/pqm-projects-management/tpl/form_project.tpl", // Fix absolute path
							   this.pathToThisFile + "/server/create_project.php", 
							   this.pathToThisFile + "/server/update_project.php",
							   {validations:{name:"required", description:"required"}});
	this.formsMgr.registerAlias("device", 
							   "/proto-power/modules/pqm-projects-management/tpl/form_device.tpl", 
							   this.pathToThisFile + "/server/create_device.php", 
							   this.pathToThisFile + "/server/update_device.php",
							   {validations:{name:"required", kva:{required:true, number:true}, reactance:{required:true, number:true}, voltage:{required:true, number:true}}});
	this.formsMgr.registerAlias("datablock", 
							   "/proto-power/modules/pqm-projects-management/tpl/form_datablock.tpl", 
							   this.pathToThisFile + "/server/create_datablock.php", 
							   this.pathToThisFile + "/server/update_datablock.php",
							   {validations:{name:"required"}});
}

PQMProjectsManager.prototype = {
	create: function(alias, fnCallback, params) {
		this.formsMgr.create(alias, fnCallback, params);
	},
	
	update: function(alias, obj, fnCallback, params) {
		this.formsMgr.update(alias, obj, fnCallback, params);
	},
	
	remove: function(alias, fnCallback, params) {
		$.ajax({
			data: params,
			url: this.pathToThisFile + "/server/delete_" + alias + ".php",// TODO: this is a hack: needs to be fixed
			dataType: 'json',
			type: "POST",
			async: false,
			success: function(resp) {
				fnCallback(resp);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert(textStatus + ": " + errorThrown);
			}
		});
	}
}
