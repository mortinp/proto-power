var projectsPanel;
var projectsMgr;
var toolbox;
var treeView;
var selectedNode;
var context;

$(document).ready(function() {

	/*  Setup ajax loading message  */
	var loading = $("<div id='ajaxLoader' style='display:none'></div>").append('<h1><img src="2.gif" /> Please wait...</h1>');
	$("body").append(loading);

	var timeout;
	loading.ajaxStart(function() {
		timeout = setTimeout(function() {
			$.blockUI({ message: loading });
		}, 0); // Wait at least some seconds (maybe the remote response is fast)
		
	}).ajaxStop(function() {
		clearTimeout(timeout);
		$.unblockUI();
	});
	
    /* Setup projects tabbed panel*/	
	projectsPanel = $('#projects-tabbed-panel').slidingPanel({position:'left', margin:'0em', state:'opened'}).
	                data('slidingPanel'); // This is the real SlidingPanel object 

	// Create analisis context
	context = new PQMAnalisisContext("../modules/pqm-analisis-context/", {})

	projectsMgr = new PQMProjectsManager("../modules/pqm-projects-management/");

	toolbox = new Toolbox({});
	toolbox.addButton("button-add-project", null);
	toolbox.addButton("button-modify-node", null);
	toolbox.addButton("button-delete-node", null);
	toolbox.addUploadButton("button-upload-file", "../modules/pqm-projects-management/server/upload_file.php", fileUploaded, getUploadParams);
	
	treeView = new TreeviewManager("../modules/pqm-projects-management/", {
		callbacks: {
			nodeDblClick: loadData,
			nodeSelection: nodeSelected
		}
	});
});

function loadData(node) {
	if(node.type != "datablock") return;
	
	// Close projects tabbed panel
	projectsPanel.toggle('closed', true); // Close without animation
	
	var parentDevice = treeView.getInmediateParent(node);
	var parentProject = treeView.getInmediateParent(parentDevice);
	params = {project_id: parentProject._id.$id, device_id: parentDevice._id.$id, datablock_id: node._id.$id};
	context.loadContext(params);
}

function getUploadParams() {
	var parentDevice = treeView.getInmediateParent(selectedNode);
	var parentProject = treeView.getInmediateParent(parentDevice);
	return {project_id: parentProject._id.$id, device_id: parentDevice._id.$id, datablock_id: selectedNode._id.$id, datablock_index: selectedNode.index}
}

/*function viewFile(node) {
	if(node.type != "file") return;
	params = {file_id: node._id.$id};
	$.ajax({
		data: params,
		url: "server/view_file.php",
		dataType: 'script',
		type: "POST",
		async: false,
		success: function(resp) {
			$("#file-content").html(resp);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert(textStatus + ": " + errorThrown);
		}
	});
}*/

function fileUploaded(file) {
	treeView.addNode(selectedNode.tree_node, file);
}

function nodeSelected(jsonNode) {
	selectedNode = jsonNode; 

	toolbox.showAllButtons();
	if(jsonNode.type == "root") {
		toolbox.bindButton(0, createProject);
		toolbox.bindButton(1, null);
		toolbox.hideButton(1);
		toolbox.hideButton(2);
		toolbox.hideButton(3);
	} else if(jsonNode.type == "project") {
		toolbox.bindButton(0, createDevice);
		toolbox.bindButton(1, updateProject);
		toolbox.bindButton(2, deleteProject);
		toolbox.hideButton(3);
	} else if(jsonNode.type == "device") {
		toolbox.bindButton(0, createDatablock);
		toolbox.bindButton(1, updateDevice);
		toolbox.bindButton(2, deleteDevice);
		toolbox.hideButton(3);
	} else if(jsonNode.type == "datablock") {
		toolbox.bindButton(0, null);
		toolbox.bindButton(1, updateDatablock);
		toolbox.bindButton(2, deleteDatablock);
		toolbox.hideButton(0);
	} else if(jsonNode.type == "file") {
		toolbox.hideAllButtons();
		toolbox.bindButton(2, deleteFile);
		toolbox.showButton(2);
	}
}

function createProject() {
	projectsMgr.create("project", addToSelected);
}

function updateProject() {
	params = {project_id: selectedNode._id.$id};
	projectsMgr.update("project", selectedNode, updateSelected, params);
}

function deleteProject() {
	params = {project_id: selectedNode._id.$id};
	projectsMgr.remove("project", function(resp) {
		treeView.deleteNode(selectedNode.tree_node);
	}, params);
}

function createDevice() {
	params = {project_id: selectedNode._id.$id};
	projectsMgr.create("device", addToSelected, params);
}

function updateDevice() {
	var parentProject = treeView.getInmediateParent(selectedNode);
	params = {project_id: parentProject._id.$id, device_id: selectedNode._id.$id};
	projectsMgr.update("device", selectedNode, updateSelected, params);
}

function deleteDevice() {
	var parentProject = treeView.getInmediateParent(selectedNode);
	params = {project_id: parentProject._id.$id, device_id: selectedNode._id.$id};
	projectsMgr.remove("device", function(resp) {
		treeView.deleteNode(selectedNode.tree_node);
	}, params);
}

function createDatablock() {
	var parentProject = treeView.getInmediateParent(selectedNode);
	params = {project_id: parentProject._id.$id, device_id: selectedNode._id.$id};
	projectsMgr.create("datablock", addToSelected, params);
}

function updateDatablock() {
	var parentDevice = treeView.getInmediateParent(selectedNode);
	var parentProject = treeView.getInmediateParent(parentDevice);
	params = {project_id: parentProject._id.$id, device_id: parentDevice._id.$id, datablock_index: selectedNode.index};
	projectsMgr.update("datablock", selectedNode, updateSelected, params);
}

function deleteDatablock() {
	var parentDevice = treeView.getInmediateParent(selectedNode);
	var parentProject = treeView.getInmediateParent(parentDevice);
	params = {project_id: parentProject._id.$id, device_id: parentDevice._id.$id, datablock_id: selectedNode._id.$id};
	projectsMgr.remove("datablock", function(resp) {
		treeView.deleteNode(selectedNode.tree_node);
	}, params);
}

function deleteFile() {
	var parentDatablock = treeView.getInmediateParent(selectedNode);
	var parentDevice = treeView.getInmediateParent(parentDatablock);
	var parentProject = treeView.getInmediateParent(parentDevice);
	params = {project_id: parentProject._id.$id, device_id: parentDevice._id.$id, datablock_index: parentDatablock.index, file_id: selectedNode._id.$id};
	projectsMgr.remove("file", function(resp) {
		treeView.deleteNode(selectedNode.tree_node);
	}, params);
}

function addToSelected(obj) {
	treeView.addNode(selectedNode.tree_node, obj);
}

function updateSelected(obj) {
	treeView.replaceNode(selectedNode.tree_node, obj);
	selectedNode = treeView._nodeToObj(treeView.getSelected());
}
