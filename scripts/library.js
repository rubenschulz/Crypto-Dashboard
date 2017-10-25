/***
 * @copyright 2017 Ruben Schulz
 * @author    Ruben Schulz <info@rubenschulz.nl>
 * @package   Crypto Dashboard
 * @link      http://www.rubenschulz.nl/
 * @version   1.0
***/

/*** General variables ***/
	var debug       = [];



/*** General functions ***/
	function initChart(){
		// Set debug information
		debug.push('Function - initChart');

		// Define chart
		var colors    = ['#FF6384', '#5959E6', '#2BABAB', '#8C4D15', '#8BC34A', '#607D8B', '#009688', '#FF0000', '#D69F4A', '#3035FF', '#E8680C', '#000000'];
		var date_from = $('#date_from').val();
		var date_to   = $('#date_to').val();
		var chart     = $('#chart');

		// Make AJAX call
		$.ajax({
			url      : 'ajax.php',
			type     : 'post', 
			dataType : 'json',
			data     : {
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
					var index    = 0;
					var total    = 0;

					// Loop through market
					$.each(response.data, function(label, values){
						// Build labels and data
						var data     = [];

						// Loop through values
						$.each(values, function(key, value){
							// Add labels
							if(index == 0){
								labels.push(value.timestamp);
							}
							data.push(value.value);

							// Add to total
							if(key == (values.length - 1)){
								total += Number(value.value);
							}
						});

						// Add dataset
						if(values.length){
							datasets.push({
								label           : values[values.length - 1].name+': € '+Math.round(data[data.length-1] * 100) / 100,
								data            : data,
								borderColor     : colors[index],
								backgroundColor : colors[index],
								fill            : false
							});
						}

						// Add index
						index++;
					});

					// Add total
					$('#total').text('€ '+Math.round(total * 100) / 100);

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
								responsive: true,
								maintainAspectRatio: false,
								title     : {
									display : true,
									text    : 'Crypto dashboard'
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

		// Check  change
		$('#date_from, #date_to').change(function(){
			initChart();
		});

		// Check date change
		$('#date_from, #date_to').datetimepicker({
			onChangeDateTime:function(dp, $input){
				initChart();
			}
		});
	}

	function initDatepicker(){
		// Init datetime picker
		$('.datepicker').datetimepicker({
			'datepicker'    : true,
			'timepicker'    : false,
			'weeks'         : true,
			'validateOnBlur': true,
			'allowBlank'    : true,
			'format'        : 'Y-m-d'
		});
	}

	function loadCurrency(crypto){
		// Get currency
		var currency = $(crypto).attr('data-currency');
		var compare  = $('input[name="compare"]:checked').val();
		var url      = 'https://www.worldcoinindex.com/widget/renderWidget?size=medium&from='+currency+'&to='+compare+'&clearstyle=true';

		// Check iframe
		if(!$('#'+currency).length){
			// Create iframe
			$('<iframe>', {
				src        : url,
				id         : currency,
				width      : '300px',
				height     : '240px',
				frameborder: '0px',
				scrolling  : 'no'
			}).appendTo(crypto);

		}else{
			// Reload iframe
			$('#'+currency).attr('src', url);
		}

		// Repeat
		window.setTimeout(function(){
			loadCurrency(crypto);
		}, 10000);
	}

	$('.crypto').each(function(index, crypto){
		loadCurrency(crypto);
	});

	function d($value){
		// Debug	
		console.log($value);		
	}