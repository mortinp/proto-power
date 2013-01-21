
function ReportingManager(pathToThisFile, o) {
	var _this = this;
	
	this.pathToThisFile = pathToThisFile;
	
	this._where = {
		report: "reports-container",
	};
	
	this.project_id = o.project_id;
	this.device_id = o.device_id;
	this.datablock_id = o.datablock_id;
}


ReportingManager.prototype = {

	getWidget: function() {
		var _this = this;
		params = {project_id: _this.project_id, device_id: _this.device_id, datablock_id: _this.datablock_id};
		var widget = $("<div id='reports-button'><a href='#'>View Report</a></div>").click(function() {
			$.ajax({
				data: params,
				type: "GET",
				dataType: "json",
				url: _this.pathToThisFile + "server/reporting/view_report.php",
				success: function(data) {
					_this._createReport(data);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert(textStatus + ": " + errorThrown);
				}
			});
		}); 
		
		return widget;
	},
	
	
	_createReport: function(data) {
		var container = $("#" + this._where.report);
		container.empty();
		//container.resizable('destroy');
		
		/* Setup widgets for reports */
		container.addClass("ui-widget ui-widget-content");
		container.append($("<div class='ui-widget-header' style='height:auto'>Reports</div>"));
		
		var report = $("<div id='report-accordion' style='width:100%;height:100%'>");
		for(var section in data) {
			report.append($("<h3>" + section + "</h3>"));
			var sectionContainer = $("<div></div>");
			
			for(var entry in data[section]) {
				if(data[section][entry].type == "table-2D-phases") this._createTable(data[section][entry], sectionContainer);
			}
			
			report.append(sectionContainer);
		}
		container.append(report);
		
		
		$( "#report-accordion" ).accordion({heightStyle:'fill'}).accordion( "widget" );
		$( "#" + this._where.report ).resizable({handles: "n", minHeight:65, resize: function(e, ui) {
			$( "#report-accordion" ).accordion('refresh');
		}});
		
		$("#reports-button").hide();
		
		//alert(data);
	},
	
	_createTable: function(tableDef, container) {
		var table = $("<table border='1' style='width:100%'></table>");
		for(var i in tableDef.phases) {
			var th = $("<th></th>");
		
			th.append($("<td>Phase" + tableDef.phases[i] + "</td>"));
			
			var tr = $("<tr></tr>");
			for(var j in tableDef.calculus) {
				tr.append($("<td>" + tableDef.calculus[j] + "</td>"));
			}
			th.append(tr);
			table.append(th);
		}
		
		container.append(table);
	}
};
