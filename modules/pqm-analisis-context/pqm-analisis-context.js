function _overrideProperties(first, second){
    for (var prop in second){
        first[prop] = second[prop];
    }
};

function PQMAnalisisContext(pathToThisFile, o) {
	this.pathToThisFile = pathToThisFile;
	
	/* The ids/classes of the elements where you want to put some parts of the analisis environment */
	this._places = {
		project_info: "project-info",
		device_info: "device-info",
		datablock_info: "datablock-info",
		subanalisis_selector: "subanalisis-selectors",
	};
	
	// Analisis array
	this.analisis = [];

	this._pathConfigs = {
		context_reader: this.pathToThisFile + "server/get_context.php",
	}
	
	// Override properties with the ones provided through constructor 
	_overrideProperties(this._pathConfigs, o.pathConfigs);
}

PQMAnalisisContext.prototype = {
	loadContext: function(params) {
		var _this = this;
		$.ajax({
			data: params,
			type: "GET",
			dataType: "json",
			url: _this._pathConfigs.context_reader,
			success: function(contextData) {
				/** 
				*	INFO
				*/
				// Show project info
				var projectContainer = $("#" + _this._places.project_info);
				projectContainer.empty();
				projectContainer.append("<p><strong>Project Info</strong></p>");
				projectContainer.append("<p>Name: " + contextData.project.name + "</p>");
				projectContainer.append("<p>Description: " + contextData.project.description + "</p>");
				
				// Show device info
				var deviceContainer = $("#" + _this._places.device_info);
				deviceContainer.empty();
				deviceContainer.append("<p><strong>Device Info</strong></p>");
				deviceContainer.append("<p>Name: " + contextData.device.name + "</p>");
				deviceContainer.append("<p>KVA: " + contextData.device.kva + "</p>");
				deviceContainer.append("<p>% Reactance: " + contextData.device.reactance + "</p>");
				deviceContainer.append("<p>Voltage: " + contextData.device.voltage + "</p>");
				deviceContainer.append("<p>Isc: " + contextData.device.Isc + "</p>");
				
				// Show datablock info
				var datablockContainer = $("#" + _this._places.datablock_info);
				datablockContainer.empty();
				datablockContainer.append("<p><strong>Datablock Info</strong></p>");
				datablockContainer.append("<p>Name: " + contextData.datablock.name + "</p>");
			
				
				/** 
				*	CONTEXT
				*/
				// Create analisis objects and its selectors
				var selDiv = $("#" + _this._places.subanalisis_selector);
				selDiv.empty();
				var analisis = contextData.analisis;
				for(var a in analisis) {
					config = {project: contextData.project, device: contextData.device, datablock: contextData.datablock, analisis: analisis[a]};
					_this.analisis[a] = new PQMAnalisis(_this.pathToThisFile, config);
					_this.analisis[a]._addSubanalisisSelectors(selDiv);
				}
				
				// Load default analisis
				var main = contextData.current_analisis.main;// analisis
				var subname = contextData.current_analisis.default;// subanalisis
				_this.analisis[main]._loadSubanalisis(subname);
				
				/** 
				*	REPORTING
				*/
				// Load reporting
				/*var reporting = new ReportingManager(_this.pathToThisFile, params);
				projectContainer.append(reporting.getWidget());*/
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert(textStatus + ": " + errorThrown);
			}
		});
	},
};


/**
	Analisis
*/
function PQMAnalisis(pathToThisFile, o) {
	this.pathToThisFile = pathToThisFile;

	this.project = o.project;
	this.device = o.device;
	this.datablock = o.datablock;

	// The main chart
	this.mainChart = undefined;
	
	// The data retrieved from server. The data is updated everytime a request is made
	this.mainData = [];
	
	this.chartBuilder = new ChartBuilder2({});
	
	// Cached tabs (user already opened them). Used to avoid charts recreation.
	this.cachedTabs = [];

	/* The ids/classes of the elements where you want to put some parts of the analisis environment */
	this._places = {
		tabs: "tabsmain",
		subanalisis_selector: "subanalisis-selectors",
		parameter_selector: "parameter-selectors",
		main_chart_panel: "main-chart-panel",
		events: "events-box",
	};
	
	this._pathConfigs = {
		parameters_reader: this.pathToThisFile + "server/get_parameter.php",
	}
	
	/* Override properties with the ones provided through constructor */
	_overrideProperties(this._places, o.places);
	_overrideProperties(this._pathConfigs, o.pathConfigs);
	
	// Create subanalsis using configurations given
	this.subanalisis = [];
	var subs = o.analisis.subanalisis;
	for(var i in subs) {
		// Create an index after each subanalisis' tag, with the subanalisis created
		this.subanalisis[subs[i].tag] = new PQMSubanalisis(subs[i], $.proxy(this.loadData, this));
	}
	//this._addSubanalisisSelectors();
}

PQMAnalisis.prototype = {
	/*
	Loads the data for one type of parameter, e.g. I, V or H.
	*/
	loadData: function(type, scope) {
		var _this = this;
		params = {project_id: _this.project._id.$id, device_id: _this.device._id.$id, datablock_id: _this.datablock._id.$id, type: type, scope: scope};
		$.ajax({
			data: params,
			type: "GET",
			dataType: "json",
			url: _this._pathConfigs.parameters_reader,
			success: function(data) {
				_this.mainData = data;
				_this._showData(type, scope, data);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert(textStatus + ": " + errorThrown);
			}
		});
	},
	
	_addSubanalisisSelectors: function(container) {
		//var selDiv = $("#" + this._places.subanalisis_selector);
		//selDiv.empty();
		for(var s in this.subanalisis) {
			var selector = this.subanalisis[s].getSelector();
			container.append(selector);
		}
	},
	
	_showData: function(type, scope, data) {
		if(data.reports) alert("Isc: " + data.reports.Isc);
		
		// Create tabs
		this._createTabs(data.analisis);
		
		// Create parameters selectors
		var selectorsContainer = $("#" + this._places.parameter_selector);
		selectorsContainer.empty();
		this._addParameterSelectors(selectorsContainer, data.selectors, scope, type);
		
		this._drawChartAndWidgets('main', this._places.main_chart_panel, "tabsmain-main");
		
		// Reset
		this.cachedTabs = [];
	},
	
	_createTabs: function(analisis) {
		var templateForMain = 
			"<ul>" + 
				"<li><a href='#tabsmain-main'>Main</a></li>" +
			"</ul>" +
			"<div id='tabsmain-main' style='padding:4px 2px 2px 2px;'>" +
				"<div id='" + this._places.parameter_selector + "' style='margin-right:35px;'>" +
				"</div>" +
				"<br>" +
				"<div id='" + this._places.main_chart_panel + "' style='width:100%;height:400px;'></div>" +
			"</div>";
	
		// Clear
		$("#" + this._places.tabs).empty();
		$("#" + this._places.tabs).append($(templateForMain));
		//$("#" + this._places.main_chart_panel).empty();
		//$("." + this._places.events).remove();
		//$("#" + _this._places.events).empty();
		
		// Temporally create tabs to prevent 'unstyled content flash'
		var tabs = $("#" + this._places.tabs);
		tabs.tabs({
			selected : 0 // 'Main' selected by default
		});
		
		// Create analisis tabs
		this._addAnalisisTabs(analisis);
	},
	
	/*
	This function generates one tab for each type of analisis of the current parameter.
	It generates one <li> element to create the tab, and one <div> element with the id of
	the analisis to print the analisis chart (the <div> works as a chart panel).
	It also binds the tabs selection event to one specific function that performs the
	calculations of the analisis.
	*/
	_addAnalisisTabs: function(analisis) {
		var _this = this;
	
		// Clear tabs and panels (delete previously created temporal <li>s and <div>s)
		var tabs = $("#" + this._places.tabs);
		var ul = $("ul", tabs);
		$('div', tabs).remove('.temp'); // remove temp <div>s
		$('li', ul).remove('.temp'); // remove temp <li>s
		
		// Add tabs according to analisis specified for the current parameter. Here we add temp <li>s to the <ul>, and <div>s to the tabs element (panels)
		//var insertAfter = ul.find('li')[0];//this is tab 'Main'
		if(analisis != undefined) {
			for(an in analisis) {
				if(an.toLowerCase() == "main") continue; // skip 'Main' analisis
				var tabName = an[0].toUpperCase() + an.substring(1);
				var panelRef = "panel-" + an;
				var toInsert = $("<li class='temp'><a href='#"+ panelRef + "' data-analisis-name=" + an + ">" + tabName + "</a></li>");
				ul.append(toInsert);
				//toInsert.insertAfter(insertAfter);
				//insertAfter = toInsert;
				
				var newDiv = $("<div class='temp' id='" + panelRef + "' style='padding:4px 2px 2px 2px;'></div>");

				newDiv.append($("<div id='" + an + "' style='width:100%;height:400px;'></div>"));
				tabs.append(newDiv);
			}
		}
		
		// (re)Create tabs
		tabs.tabs("destroy").tabs({
			selected : 0, // 'Main' selected by default
			show: function(event, ui) {
				var analisisName = $(ui.tab).data("analisis-name");
				// Bind tabs selection to a function in ChartAnalyser
				if(ui.index > 0) {
					// Only recreate the chart if its tab is not cached (wasn't opened previously)
					if($.inArray(ui.index, _this.cachedTabs) == -1) {
						_this._drawChartAndWidgets(analisisName, analisisName, $(ui.panel).attr('id'));
						_this.cachedTabs.push(ui.index);
					}
				}
				return true;
			}
		});
	},
	
	_drawChartAndWidgets: function(analisisName, whereChart, whereWidgets) {
		var _this = this;
		
		// Create chart
		chartConfig = {
			where: whereChart, 
			title: _this.mainData["title"],
			indicators: _this.mainData.analisis[analisisName].indicators, 
			min_time: _this.mainData.options["MINTIME"],
		};
		if(_this.mainData.analisis[analisisName].normalization) {// Testing normalization
			if(_this.mainData.analisis[analisisName].normalization.type == "threshold-line") {
				chartConfig.extras = [{type:"threshold-line", 
									  value:_this.mainData.analisis[analisisName].normalization.value,
									  label:_this.mainData.analisis[analisisName].normalization.label }];
			}	
		}
		if(_this.mainData.analisis[analisisName].chart.options) {
			$.extend(chartConfig, _this.mainData.analisis[analisisName].chart.options)
		}
		var ch = _this.chartBuilder[_this.mainData.analisis[analisisName].chart.type](_this.mainData.data, chartConfig);
		
		// Append events widget
		_this._appendEventsSelectors(ch, _this.mainData.analisis[analisisName].events, $("#" + whereWidgets));
		
		// Create autoexport button widget
		var exportButton = $("<input type='button' value='Export' id='export-button'></input>").click(function() {
			items = AmCharts.getExport(whereChart);
			
			var img = items[0];
			// atob to base64_decode the data-URI
			var image_data = atob(img.src.split(',')[1]);
			// Use typed arrays to convert the binary data to a Blob
			var arraybuffer = new ArrayBuffer(image_data.length);
			var view = new Uint8Array(arraybuffer);
			for (var i=0; i<image_data.length; i++) {
				view[i] = image_data.charCodeAt(i) & 0xff;
			}
			try {
				// This is the recommended method:
				var blob = new Blob([arraybuffer], {type: 'application/octet-stream'});
			} catch (e) {
				// The BlobBuilder API has been deprecated in favour of Blob, but older
				// browsers don't know about the Blob constructor
				// IE10 also supports BlobBuilder, but since the `Blob` constructor
				//  also works, there's no need to add `MSBlobBuilder`.
				a = e;
				var bb = new (window.WebKitBlobBuilder || window.MozBlobBuilder || window.BlobBuilder);
				bb.append(arraybuffer);
				var blob = bb.getBlob('application/octet-stream'); // <-- Here's the Blob!
			}

			// Use the URL object to create a temporary URL
			var url = (window.webkitURL || window.URL).createObjectURL(blob);
			location.href = url; // <-- Download!
		});
		$("#" + whereWidgets).append(exportButton);
	},

	_addParameterSelectors: function(container, selectors, scope, defaultSelected) {
		container.append("<strong>Load:</strong>");
		for(var i=0;i<selectors.length;i++) {
			container.append(this._createParameterSelector(selectors[i], scope, selectors[i]==defaultSelected?true:false));
		}
	},

	_createParameterSelector: function(type, scope, checked) {
		var _this = this;
		var sel = $("<input type='radio' name='load'><strong>" + type + "</strong>");
		if(checked) sel.attr("checked", true);
		
		var selected = false;
		sel.click(function() {
			selected = !selected;
			// Load only if not currently selected
			if(selected) _this.loadData(type, scope);
		});
		return sel;
	},

	_appendEventsSelectors: function(chart, events, panel) {
		var container = $("<div class='events-box' style='width:100%;'></div>");
		//var container = $("#" + this._places.events);
		if(events.length != 0) container.append("<p>View events:</p>")
		for(var i=0;i<events.length;i++) {
			var eName = events[i];
			container.append(this._createEventSelector(chart, eName));
		}
		panel.append(container);
	},

	_createEventSelector: function(chart, eName) {
		var e = $("<input type='checkbox' id='e-" + eName + "' name='" + eName + "'>" + eName + "</input>");
		
		var checked = false;
		e.click(function() {
			checked = !checked;
			if(checked) {
				chart.addEvents(eName);
				chart.validateNow();
			} else {
				chart.removeEvents(eName);
				chart.validateData();
			}
		});
		return e;
	},
	
	_loadSubanalisis: function(subName) {
		this.subanalisis[subName].load();
	},
};

/**
	Sub analisis
*/
function PQMSubanalisis(o, fnOnRun) {
	var _this = this;
	
	this.tag = o.tag;
	//this.filePath = o.file_path;
	this.defaultParameter = o.default_param;
	this.scope = o.scope;
	//this.id_device = o.id_device;
	//this.datablock = o.datablock;
	
	// Callback function executed when the selector is checked
	this.onRun = fnOnRun; // ex. function(defaultParameter, scope){}
	
	this._selector = $("<input type='radio' name='sub-selector'><span>" + this.tag + "</span>");
	this._selector.click(function() {
		_this.load();
	});
}

PQMSubanalisis.prototype = {
	getSelector: function() {
		return this._selector;
	},
	
	load: function() {
		this.onRun(this.defaultParameter, this.scope);
		this._selector.attr("checked", true);
	},
	
	_addSubanalisisSelector: function() {
		
	},
};
