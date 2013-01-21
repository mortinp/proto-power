
var ChartAnalyser = {
	unbalance: function(chart) {
		var providers = [];
		var dataSets = chart.dataSets;
		for(var i=0;i<dataSets.length;i++) {
			providers.push({dataProvider: dataSets[i].dataProvider, indicator: dataSets[i].title});
		}
		var data = _calculateUnbalance2(providers);
		
		var builder = new ChartBuilder2({});
		return builder.multiDatasetStockChart(data, {where: "unbalance", indicators: ["VALUE"], min_time: "ss"});//TODO: fix mintime
	},
	
	THD: function(chart) {
		data = chart.dataProvider;
		var builder = new ChartBuilder2({});
		return builder.multiDatasetStockChart(data, {where: "THD", indicators: ["THD-F"], min_time: "ss"});//TODO: fix mintime
	},
			
};

/**
	@param providers An array in the form [p1, p2, p3] 
	where each p is an object in the form {dataProvider: chartDataProvider, indicator: fieldName}
*/
function _calculateUnbalance2(providers) {
	var unbalance = [];
	var dataLength = providers[0].dataProvider.length;
	
	for(var i=0;i<dataLength;i++) {
		var average = 0;
		var currentMin = Number(providers[0].dataProvider[i][providers[0].indicator]);
		for(var j=0;j<providers.length;j++) {
			var currentIndicator = providers[j].indicator;
			average += Number(providers[j].dataProvider[i][currentIndicator]);
			
			if(currentMin > Number(providers[j].dataProvider[i][currentIndicator])) {
				currentMin = Number(providers[j].dataProvider[i][currentIndicator]);
			}
		}
		average /= providers.length;
		
		var currentTime = providers[0].dataProvider[i]["DATETIME"];
		unbalance[i] = {DATETIME: currentTime, VALUE: roundNumber(((average - currentMin)/average)*100, 2)};
	}
	
	return unbalance;
}

function roundNumber(number, decimals) { // Arguments: number to round, number of decimal places
	var newnumber = new Number(number+'').toFixed(parseInt(decimals));
	return newnumber;
}