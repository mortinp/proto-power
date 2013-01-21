// The analisis context
var context;

$(document).ready(function() {
	// Temporally create tabs to prevent unstyled content flash
	var tabs = $("#tabsmain");
	tabs.tabs({
		selected : 0 // 'Main' selected by default
	});

	// Create analisis context
	context = new PQMAnalisisContext("../modules/pqm-analisis-context/", {});
	
	context.loadContext(1, "aaa");
	
	/*// Create toolboxed projects tree view
	new ToolboxedProjectsTreeView("../modules/toolboxed-treeview/", {
		callbacks: {
			fileDblClick: loadData
		},
		pathConfigs: {
			projects_root: "proto-power/projects/"
		}
	});*/
});

/*function loadData(filePath, data) {
	context.loadData(filePath, data.default_parameter);
}*/