/**
Add some extensions to AmCharts objects
*/
(function() {

	// add a function to add events of one type
	AmCharts.DataSet.prototype.addEvents = function(type, events) {
		var typeIndex = EventsBuilder.indexOf(type);
		this.eventsMapping[typeIndex] = new Array(events.length);
		for(var i=0;i<events.length;i++) {
			this.stockEvents.push(events[i]);
			this.eventsMapping[typeIndex][i] = this.stockEvents.length - 1;
		}
	};
	
	// add a function to remove all events of one type
	AmCharts.DataSet.prototype.removeEvents = function(type) {
		var typeIndex = EventsBuilder.indexOf(type);
		
		var firstEvent = this.eventsMapping[typeIndex][0];
		var nEvents = this.eventsMapping[typeIndex].length;
		var higher = firstEvent + nEvents - 1;
		
		// remove event
		this.stockEvents.splice(firstEvent, nEvents);
		
		// update mapping
		$.each(this.eventsMapping, function(eId, events) {
			if(events == undefined) return;
			for(var k=0;k<events.length;k++) {
				if(events[k] < higher) break;
				events[k] = events[k] - nEvents;
			}
		});
		this.eventsMapping[typeIndex].splice(0, nEvents);
	};
	
	/*
		Stock Chart
	*/
	AmCharts.AmStockChart.prototype.addEvents = function(type) {
		var dataSets = this.dataSets;
		for(var i=0;i<dataSets.length;i++) {
			var graph = this.panels[0].stockGraphs[0];//TODO: This works only for charts with one panel and one graph. Find out how to extend this!!!
			dataSets[i].addEvents(type, EventsBuilder[type](dataSets[i].dataProvider, dataSets[i].title, graph));
		}
	};
	
	AmCharts.AmStockChart.prototype.removeEvents = function(type) {
		var dataSets = this.dataSets;
		for(var i=0;i<dataSets.length;i++) {
			dataSets[i].removeEvents(type);
		}
	};
	
	AmCharts.AmStockChart.prototype.setCursorPan = function() {
		this.chartCursorSettings.pan = true;
		this.chartCursorSettings.zoomable = false;
		this.validateNow();
	};
	
	AmCharts.AmStockChart.prototype.setCursorZoom = function() {
		this.chartCursorSettings.pan = false;
		this.chartCursorSettings.zoomable = true;
		this.validateNow();
	};
	
	AmCharts.AmStockChart.prototype.addPanSelectSwitch = function() {
		this.panSelectSwitch = true;
	};
	
	AmCharts.AmStockChart.prototype.draw = function(where) {
		var _this = this;
		
		this.placement = where;
		
		// Loading message
		$("#" + where).block({
			message: 'Loading chart...'/*'<h1><img src="2.gif" /></h1>'*/, 
			css:{backgroundColor:'transparent', border:"0px"}, 
			overlayCSS:{backgroundColor:'transparent'}
		});
		
		setTimeout(function() {// We must wait some time to start painting the chart, since it freezes the screen and a bad effect is seen otherwise
			_this.write(where);
			$("#" + where).unblock();
			_this.animationPlayed = true; // animate only on first drawing
		}, 500);		
		
		/*// Tweak some stuff
		$(".amChartsLegend").mouseover(function(e) {
			alert("OK");
		});*/
	
		
		// Add pan/select selectors
		/*if(this.panSelectSwitch) {
			var radioGroupName = "selpan-" + where;// we get here a unique name, since 'where' should be an id
			var select = $("<input type='radio' name='" + radioGroupName + "' checked='true'><span>Select</span>");
			var pan = $("<input type='radio' name='" + radioGroupName + "'><span>Pan</span>");
			select.click(function() {
				_this.setCursorZoom();
			});
			pan.click(function() {
				_this.setCursorPan();
			});			
			
			var panSelectBox = $("<div class='pan-select-box'></>").append(select).append(pan);
			
			// Remove any previously created selectors and add the new one
			var parent = $("#" + where).parent();
			$("div", parent).remove('.pan-select-box');
			
			parent.append(panSelectBox);
		}*/
	};
})();

// Create extension for AmCharts DataSet. This extension adds an array that represents the mapping for each type
// of event in the dataset. This mapping takes the form: ... TODO
function ExtendedDataSet() {
	AmCharts.DataSet.call(this);
	this.eventsMapping = [];
}

function ExtendedStockChart(/*o*/) {
	AmCharts.AmStockChart.call(this);
	
	var _this = this;
	
	this.maxPlots = 'all';
	this.maxPlotsErrorTolerance = 0;
	
	/*this._options = {
		maxPlots: 'all',
		maxPlotsErrorTolerance: 0,
	};
	if(o)$.extend(this._options, o);*/
	
	// Add listener to 'dataUpdated' to setup some default values for the chart
	this.addListener('dataUpdated', function(e) {
		
		// Zoom to max number of plots
		if(_this.maxPlots != 'all') {
			var dataProvider = _this.dataSets[0].dataProvider; // TODO: is it ok to use the first dataset???
			if(dataProvider.length > _this.maxPlots && 
			   dataProvider.length - _this.maxPlots > _this.maxPlotsErrorTolerance) {
				_this.zoom(dataProvider[0]["DATETIME"], dataProvider[_this.maxPlots]["DATETIME"]);
			}
		}
	});
	
	// Whether to draw a pan/select box
	this.panSelect = false;
}

// create custom dataset that inherits from DataSet
ExtendedDataSet.prototype = new AmCharts.DataSet();

//create custom stock chart that inherits from AmStockChart
ExtendedStockChart.prototype = new AmCharts.AmStockChart();


/** 
Events Builder 
*/
var EventsBuilder = {
	
	indexOf: function(eType) {
		if(eType == "max") return 0;
		else if(eType == "min") return 1;
		return -1;
	},

	max: function(dataProvider, indicator, graph) {
		// the events to return
		var events = [];
		
		extremeData = findExtremePoints(dataProvider, indicator, "max");
		var maxIndexes = extremeData.indexes;
		var maxValue = extremeData.value;
		
		//TODO: move the creation of the events to the outside and only record the indexes (return maxIndexes)
		for(var j=0;j<maxIndexes.length;j++){
			var eMax = {
				date: dataProvider[maxIndexes[j]]["DATETIME"],
				type: "arrowDown",
				backgroundColor: "#333300",
				graph: graph,
				text: "Max",
				description: "MAX. value: " + Number(maxValue) + "\nTime: " + dataProvider[maxIndexes[j]]["DATE"] + " (" + dataProvider[maxIndexes[j]]["TIME"] + ")"
			};
			events.push(eMax);
		}
		
		return events;
	},
	
	min: function(dataProvider, indicator, graph) {
		// the events to return
		var events = [];
		
		extremeData = findExtremePoints(dataProvider, indicator, "min");
		var minIndexes = extremeData.indexes;
		var minValue = extremeData.value;
		
		//TODO: move the creation of the events to the outside and only record the indexes (return minIndexes)
		for(var j=0;j<minIndexes.length;j++){
			var eMin = {
				date: dataProvider[minIndexes[j]]["DATETIME"],
				type: "arrowUp",
				backgroundColor: "#CCCCCC",
				graph: graph,
				text: "Min",
				description: "MIN. value: " + Number(minValue) + "\nTime: " + dataProvider[minIndexes[j]]["DATE"] + " (" + dataProvider[minIndexes[j]]["TIME"] + ")"
			};
			events.push(eMin);
		}
		
		return events;
	}
};

function findExtremePoints(dataProvider, indicator, extremeType) {
	// find all max values for indicator
	var currentExtremeIndexes = [];
	currentExtremeIndexes[0] = 0;
	var currentExtremes = [];
	currentExtremes[0] = dataProvider[0][indicator];
	for(var j=1;j<dataProvider.length;j++) {
		if((extremeType == "max" && Number(dataProvider[j][indicator]) > Number(currentExtremes[0])) ||
			(extremeType == "min" && Number(dataProvider[j][indicator]) < Number(currentExtremes[0]))) {
			currentExtremeIndexes.splice(0, currentExtremeIndexes.length)
			currentExtremes.splice(0, currentExtremes.length)
			currentExtremeIndexes[0] = j;
			currentExtremes[0] = dataProvider[j][indicator];
		} else if(Number(dataProvider[j][indicator]) == Number(currentExtremes[0])) {
			currentExtremeIndexes.push(j);
			currentExtremes.push(dataProvider[j][indicator]);
		}
	}
	return {indexes:currentExtremeIndexes, value: currentExtremes[0]};
}
