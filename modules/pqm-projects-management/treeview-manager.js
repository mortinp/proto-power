function _overrideProperties(first, second){
    for (var prop in second){
        first[prop] = second[prop];
    }
};

/**
* @param pathToThisFile The path to this file relative to the page (html or php file) that imports it
* @param o A configuration object
*/
function TreeviewManager(pathToThisFile, o) {
	// Fix JQuery 'this' to access from inside callbacks.
	var _this = this;

	this.pathToThisFile = pathToThisFile;

	/* Paths to the handler pages */
	this._serverHandlers = {
		find: this.pathToThisFile  + "server/get_projects_treeview.php",
	};
	
	/* Some configuration allowed for the tree */
	this._treeConfigs = {
		elemId: "projects-tree"
	};
	
	/* Callback functions for some events */
	this._callbacks = {
		nodeSelection: function(jsonNode) {},
		nodeDblClick: function(jsonNode) {}
	};
	
	/* Override properties with the ones provided through constructor */
	_overrideProperties(this._serverHandlers, o.serverHandlers);
	_overrideProperties(this._callbacks, o.callbacks);
	_overrideProperties(this._treeConfigs, o.treeConfigs);
	
	/* Create the tree */
	this.tree = this._loadProjectsTree();
	this.treeElem = $("#" + this._treeConfigs.elemId);
}

TreeviewManager.prototype = {
	_loadProjectsTree: function () {
		var tree;
		var _this = this;
		$.ajax({ // TODO: Should this be a $.getJSON() call???
			type: "GET",
			dataType: "json",
			url: _this._serverHandlers.find,
			success: function(jsonTree) {
				tree = $("#" + _this._treeConfigs.elemId).jstree(jsonTree)
				
				// Bind double click to file nodes to load data
				.bind("dblclick.jstree", function (event, data) {
					//alert($(event.target).closest("li").index());
					var node = $(event.target).closest("li");
					_this._callbacks.nodeDblClick(_this._nodeToObj(node));
				})
				// Bind nodes selection to accomodate the upload files button
				.bind("select_node.jstree", function (event, data) {
					var node = data.rslt.obj;
					_this._callbacks.nodeSelection(_this._nodeToObj(node));
				});
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert(textStatus + ": " + errorThrown);
				tree = false;
			}
		});
		
		return tree;
	},
	
	addNode: function(parent, newNode, rename) {
		var _this = this;
		_this.treeElem.jstree("create", parent, "last", newNode, function(){}, !rename);
	},

	replaceNode: function(oldNode, newNode) {
		var _this = this;

		//_this.treeElem.jstree("_parse_json", newNode, oldNode);
		var data = newNode.metadata;
		for(key in data) {
			oldNode.data(key, data[key]);
		}
		_this.treeElem.jstree("rename_node", oldNode, newNode.data);
		
		// Trigger a node selection event
		//_this.treeElem.jstree.save_selected( ); 
		//_this.treeElem.jstree("select_node", oldNode);
		
	},
	
	deleteNode: function(node) {
		var _this = this;
		_this.treeElem.jstree("remove");
	},
	
	renameNode: function(node, newName) {
		node.data("name", newName);
	},
	
	getInmediateParent: function(obj) {
		return this._nodeToObj($(obj.tree_node.parents("li").get()[0]));
	},
	
	getSelected: function() {
		return this.treeElem.jstree("get_selected"); 
	},

	/* Auxiliary functions */
	_getPathToNode: function(node) {
		// TODO: use built-in function to find the path to a node
		/*var _this = this;
		var pathArray = _this.treeElem.jstree("get_path", node, false);
		for(var i=0;i<pathArray.length;i++) {
			alert(pathArray[i]);
		}*/
		// If root, return its name (NOTE: this is used to search data in the proper directory)
		if(node.attr("rel") == "root") return node.data("name");
		
		var parents = node.parents("li").get().reverse();
		parents.splice(0,1);// remove top parent (not part of the tree, but used for docking)... NOTE: this is a hack (needs to be fixed).
		var pathToNode = "";
		$(parents).each(function() {
			pathToNode += $(this).data("name") + "/";
			//pathToNode += $(this).children("a").text().trim() + "/";
		});
		pathToNode += node.data("name");
		return pathToNode;
	},
	
	/**
	* Converts a node to an object using the node's data attributes. Each key/value in the node.data()
	* array is converted to a key/value in the object, plus another key with the 'type' of the
	* node, as sepcified by the attribute 'rel'. Also, it adds an entry named 'tree_node' where the
	* real tree node is stored (so you can use it directly in the treeview).
	*/
	_nodeToObj: function(node) {
		var obj = {};
		var data = node.data();
		for(key in data) {
			obj[key] = data[key];
		}
		obj["type"] = node.attr("rel");
		obj["index"] = node.closest("li").index();
		obj["tree_node"] = node;
		return obj;
	}
};

