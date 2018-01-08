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
		$.getJSON('data/data.json', function(response){
			// Create series array
			seriesOptions = [];
			$.each(response, function(label, data){
				// Add to chart series
				seriesOptions.push(data);

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

			// Load widgets
			$('<script>').attr('src', src).appendTo('body');

			// Draw chart
			Highcharts.stockChart('container', {
				// Set series
				series: seriesOptions,

				// Set chart options
				title: {
					text: 'Crypto Dashboard'
				},
				subtitle: {
					text: 'To the moooon!'
				},
				chart: {
					height: '65%',
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
					minorTickInterval: 250
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

				// Set navigator options
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
								height: '150%'
							}
						}
					}]
				}

			});
		});
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



/*** Execute functions ***/
	$(document).ready(function(){
		// Load chart
		loadChart();

		// Load currency
		loadCurrency();
	});	
