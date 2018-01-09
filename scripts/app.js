/***
 * @copyright 2017 Ruben Schulz
 * @author	Ruben Schulz <info@rubenschulz.nl>
 * @package   Crypto Dashboard
 * @link	  http://www.rubenschulz.nl/
 * @version   1.0
***/

/*** General functions ***/
	function loadCharts(){
		// Load data
		$.getJSON('data/data-history.json', function(response){
			// Create series array
			var historySeries        = [];
			var distributionSeries = [{name: 'Distribution', data: []}];

			$.each(response, function(label, data){
				// Add to history chart
				historySeries.push(data);

				// Add to distribution chart
				distributionSeries[0].data.push({
					name: data.name, 
					y: data.data[data.data.length - 1][1]				
				});

				// Set widget name
				widget_name = data.name.toLowerCase().replace(' ', '-');
												
				// Create widget
				$('#widgets').children().first()
					.clone()
					.appendTo('#widgets')
					.attr('data-currency', widget_name)
					.addClass('coinmarketcap-currency-widget')
					.removeClass('hide');
			});

			// Draw history chart
			Highcharts.stockChart('chart-history', {
				// Set series
				series: historySeries,

				// Set chart options
				title: {
					text: 'Crypto Dashboard'
				},
				subtitle: {
					text: 'To the moooon!'
				},
				chart: {
					height: 700
				},
				credits: {
					enabled: false
				},          

				// Set axis options
				yAxis: {
					labels: {
						formatter: function(){
							return '€ '+Highcharts.numberFormat(this.value, 2, ',', '.');
						}
					},
					minorTickInterval: 250,
					min : 0
				},

				// Set legend options
				legend: {
					labelFormatter: function() {
					return '<span style="color:'+this.color+'">'+this.name+': € '+Highcharts.numberFormat(this.yData[this.yData.length - 1], 2, ',', '.')+'</span>';
					},
					enabled: true
				},

				// Set tooltip options
				tooltip: {
					formatter: function(){
						total = 0;
						tooltip_html  = '<table>';
						tooltip_html += '<tr><td colspan="2" style="font-weight:bold; text-align: center">'+ Highcharts.dateFormat('%d %B %Y %H:%M', new Date(this.x)) +'</td></tr>';

						this.points.forEach(function(point){
							tooltip_html += '<tr><td style="font-weight:bold; color:'+ point.series.color +'">'+ point.series.name +':</td><td style="text-align: right">€ '+Highcharts.numberFormat(point.y, 2, ',', '.')+'</td></tr>';
							total        += point.y;
						});

						tooltip_html += '<tr><td style="font-weight:bold;">Total</td><td style="text-align: right; font-weight:bold;">€ '+Highcharts.numberFormat(total, 2, ',', '.')+'</td></tr>'
						tooltip_html += '</table>';

						return tooltip_html;
					},
					shared: true,
					useHTML: true
				},

				// Set range selector
				rangeSelector: {
					buttons: [{
						type: 'day',
						count: 1,
						text: '1D'
					}, {
						type: 'week',
						count: 1,
						text: '1W'
					}, {
						type: 'month',
						count: 1,
						text: '1M'
					}, {
						type: 'month',
						count: 3,
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
					selected: 1
				},

				// Set plot options
				plotOptions: {
					series: {
						showInNavigator: true
					}
				},

				// Set repsonvie options
				responsive: {
					rules: [{
						condition: {
							maxWidth: 640
						},
						chartOptions: {
							chart: {
								height: 650
							},
							rangeSelector : {
								inputEnabled:false
							},
							navigator: {
								enabled: false
							}
						}
					}]
				}

			}, function(chart){
				// apply the date pickers
				setTimeout(function(){
					$('input.highcharts-range-selector', $(chart.container).parent())
						.datepicker();
				}, 0);
			});

			// Draw distribution chart
			Highcharts.chart('chart-distribution', {
				// Set series
				series: distributionSeries,

				// Set chart options
				title: {
					text: 'Currency distribution'
				},
				chart: {
					type: 'pie'
				},
				credits: {
					enabled: false
				},

				// Set tooltip options
				tooltip: {
					pointFormat: '{series.name}: <strong>{point.percentage:.1f}%</strong>'
				},

   				// Set plot options
				plotOptions: {
					pie: {
						allowPointSelect: true,
						dataLabels: {
							enabled: false
						},
						showInLegend: true
					}
				}
    		});

			// Load widgets
			$('<script>').attr('src', src).appendTo('body');

		});

		$.getJSON('data/data-totals.json', function(response){
			Highcharts.chart('chart-totals', {

				// Set series
				series: [{
					name: 'Average value',
					data: response.averages
				}, {
					name: 'Value range',
					data: response.ranges,
					type: 'arearange',
					color: Highcharts.getOptions().colors[0],
					fillOpacity: 0.3,
					lineWidth: 0,
					marker: {
						enabled: false
					}
				}],

				// Set chart options
				title: {
					text: 'Average total value'
				},
				credits: {
					enabled: false
				},          

				// Set axis options
				xAxis: {
					type: 'datetime'
				},
				yAxis: {
					labels: {
						formatter: function(){
							return '€ '+Highcharts.numberFormat(this.value, 2, ',', '.');
						}
					},
					min : 0
				},

				// Set tooltip options
				tooltip: {
					formatter: function(){
						tooltip_html  = '<table>';
						tooltip_html += '<tr><td colspan="2" style="font-weight:bold; text-align: center">'+ Highcharts.dateFormat('%d %B %Y', new Date(this.x)) +'</td></tr>';
						tooltip_html += '<tr><td style="font-weight:bold;">Average:</td><td style="font-weight:bold; text-align: right">€ '+Highcharts.numberFormat(this.points[0].y, 2, ',', '.')+'</td></tr>';
						tooltip_html += '<tr><td>High:</td><td style="text-align: right">€ '+Highcharts.numberFormat(this.points[1].point.high, 2, ',', '.')+'</td></tr>';
						tooltip_html += '<tr><td>Low:</td><td style="text-align: right">€ '+Highcharts.numberFormat(this.points[1].point.low, 2, ',', '.')+'</td></tr>';
						tooltip_html += '<tr><td>Difference:</td><td style="text-align: right">€ '+Highcharts.numberFormat(this.points[1].point.high - this.points[1].point.low, 2, ',', '.')+'</td></tr>';
						tooltip_html += '</table>';

						return tooltip_html;
					},
					crosshairs: [true, true],
					shared: true,
					useHTML: true
				}
			});
		});

		// Set the datepicker's date format
		$.datepicker.setDefaults({
			dateFormat: 'yy-mm-dd',
			onSelect: function () {
				this.onchange();
				this.onblur();
				}
		});
	}

	function loadWidgets(){
		// Set script URL
		src = 'https://files.coinmarketcap.com/static/widget/currency.js';

		// Reload script
		$('script[src="'+src+'"]').remove();
		$('<script>').attr('src', src).appendTo('body');

		// Repeat
		window.setTimeout(function(){
			loadWidgets();
		}, 10000);
	}




/*** Execute functions ***/
	$(document).ready(function(){
		// Load chart
		loadCharts();

		// Load widgets
		loadWidgets();
	});	
