/***
 * @copyright 2017 Ruben Schulz
 * @author	Ruben Schulz <info@rubenschulz.nl>
 * @package   Crypto Dashboard
 * @link	  http://www.rubenschulz.nl/
 * @version   1.0
***/

/*** General functions ***/
	function loadChart(){
		// Load data
		$.getJSON('ajax.php', function(response){

d(response.data);
			// Draw chart
			Highcharts.stockChart('container', {

				rangeSelector: {
					buttons: [{
						type: 'hour',
						count: 1,
						text: '1h'
					}, {
						type: 'day',
						count: 1,
						text: '1D'
					}, {
						type: 'month',
						count: 1,
						text: '1M'
					}, {
						type: 'quarter',
						count: 1,
						text: '3M'
					}, {
						type: 'ytd',
						count: 1,
						text: 'YTD'
					}, {
						type: 'year',
						count: 1,
						text: '1Y'
					}, {
						type: 'all',
						count: 1,
						text: 'All'
					}],
					selected: 1,
					inputEnabled: false
				},

				yAxis: {
					labels: {
						formatter: function () {
							return (this.value > 0 ? ' + ' : '') + this.value + '%';
						}
					},
					plotLines: [{
						value: 0,
						width: 2,
						color: 'silver'
					}]
				},

				plotOptions: {
					series: {
						compare: 'percent',
						showInNavigator: true
					}
				},

				tooltip: {
					pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.change}%)<br/>',
					valueDecimals: 2,
					split: true
				},

				series: response.data

/*				series: [{
					name: 'AAPL',
					data: data
				},
				{
					name: 'AAPL',
					data: data
				}]
*/			});
		});



/*
		// Define chart
		var colors	= ['#FF6384', '#5959E6', '#2BABAB', '#8C4D15', '#8BC34A', '#607D8B', '#009688', '#FF0000', '#D69F4A', '#3035FF', '#E8680C', '#000000'];
		var date_from = $('#date_from').val();
		var date_to   = $('#date_to').val();

		// Make AJAX call
		$.ajax({
			url	  : 'ajax.php',
			type	 : 'post', 
			dataType : 'json',
			data	 : {
				date_from : date_from,
				date_to   : date_to
			},
			success: function(response){
				// Set debug information
				debug.push('Function - initChart - Success');

				// Reload the datatable
				if(response != ''){
					// Build datasets
					var datasets = [];
					var labels   = [];
					var index	= 0;
					var total	= 0;

					// Clear totals
					$('#totals').text('');

					// Loop through market
					$.each(response.data, function(label, values){
						// Build labels and data
						var data	 = [];

						// Loop through values
						$.each(values, function(key, value){
							// Add labels
							if(index == 0){
								labels.push(value.timestamp);
							}
							data.push(value.value ? Math.round(value.value * 100) / 100 : null);

							// Add to total
							if(key == (values.length - 1)){
								total += Number(value.value);
							}
						});

						// Add dataset
						if(values.length){
							datasets.push({
								label		   : values[values.length - 1].name+' ('+label+')',
								data			: data,
								borderColor	 : colors[index],
								backgroundColor : colors[index],
								fill			: false
							});
						}

						// Add total
						$('#totals').append('<div><strong>'+label+'</strong> ('+Math.round(values[values.length - 1].amount * 100) / 100+') € '+Math.round(values[values.length - 1].value * 100) / 100)+'</div>';

						// Add index
						index++;
					});

					// Add total
					$('#totals').append('<div><strong>Total</strong> € '+Math.round(total * 100) / 100)+'</div';

					// Check if chart exists
					if(typeof historyChart === 'undefined'){

	   					// Draw chart
						historyChart = new Chart(chart, {
							type   : 'line',
							data   : {
								labels  : labels,
								datasets: datasets
							},
							options: {
								responsive		  : true,
								maintainAspectRatio : false,
								title : {
									display : true,
									text	: 'Crypto dashboard'
								},
								tooltips : {
									enabled   : true,
									mode	  : 'index',
									position  : 'nearest',
									callbacks: {
										label: function(tooltipItem, data){
											return data.datasets[tooltipItem.datasetIndex].label+': € '+Math.round(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] * 100) / 100;
										},
										footer: function(tooltipItems, data){
											var total = 0;
											tooltipItems.forEach(function(tooltipItem){
												total += Number(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]);
											});
											return 'Total: € '+Math.round(total * 100) / 100;
										},
									}
								}
							}
						});
					}else{
						// Update chart
						historyChart.data.labels   = labels;
						historyChart.data.datasets = datasets;
						historyChart.update();

					}
				}
			}
		});

		// Check date change
		$('#date_from, #date_to').datetimepicker({
			onChangeDateTime:function(dp, $input){
				initChart();
			}
		});
*/
	}

	function loadCurrency(){
		// Set script URL
		src = 'https://files.coinmarketcap.com/static/widget/currency.js';

		// Reload script
		$('script[src="'+src+'"]').remove();
		$('<script>').attr('src', src).appendTo('body');

		// Repeat
		window.setTimeout(function(){
			loadCurrency();
		}, 10000);
	}

	function d($value){
		// Debug	
		console.log($value);		
	}



/*** Execute functions ***/
	$(document).ready(function(){
		// Load chart
		loadChart();

		// Load currency
		loadCurrency();
	});	
