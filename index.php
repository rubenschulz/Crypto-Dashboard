<!doctype html>

<html>
<head>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1.0' />
	<link href='style/foundation/foundation.min.css'            rel='stylesheet' type='text/css' media='screen' />
	<link href='style/datetimepicker/jquery.datetimepicker.css' rel='stylesheet' type='text/css' media='screen' />
	<style>
		html,
		body {
			height: 			100%;
		}

		h2 {
			font-size:			1.5rem;
		}

		h3 {
			font-size:			1.25rem;
		}

		.chart {
			height: 			750px;
		}

		.options {
			background-color:	#EEEEEE;

			padding-left:		1rem;
			padding-right:		1rem;

			height: 			750px;
		}

		.crypto { 
			padding:			1rem;
			float:				left;
		}

		.compare {
			background-color:	#EEEEEE;

			padding:			1rem;
		}

		.compare label {
			padding:			1rem;
		}

	</style>
	<title>Crypto Dashboard</title>
</head>

<body>

	<div class='grid-container'>
		<div class='grid-x'>
			<div class='small-12 medium-9 large-10 cell chart'>
				<canvas id='chart'></canvas>
			</div>
			<div class='small-12 medium-3 large-2 cell options'>
				<h2>Current total value</h2>
				<span id='total'></span>

				<h3>From date</h3>
				<input type='text' name='date_from' id='date_from' value='<?php !empty($_REQUEST['date_from']) ? print($_REQUEST['date_from']) : print(date('Y-m-d', strtotime('7 days ago'))); ?>' class='datepicker' />

				<h3>To date</h3>
				<input type='text' name='date_to'   id='date_to'   value='<?php !empty($_REQUEST['date_to'])   ? print($_REQUEST['date_to'])   : print(date('Y-m-d')); ?>' class='datepicker' />
			</div>
		</div>

		<hr />

		<div class='grid-x'>

			<div class='coinmarketcap-currency-widget crypto' data-currency='bitcoin'  data-base='EUR' data-secondary='USD' data-rank='false' data-marketcap='false' data-volume='false'></div>
			<div class='coinmarketcap-currency-widget crypto' data-currency='ethereum' data-base='EUR' data-secondary='USD' data-rank='false' data-marketcap='false' data-volume='false'></div>
			<div class='coinmarketcap-currency-widget crypto' data-currency='litecoin' data-base='EUR' data-secondary='USD' data-rank='false' data-marketcap='false' data-volume='false'></div>
			<div class='coinmarketcap-currency-widget crypto' data-currency='ripple'   data-base='EUR' data-secondary='USD' data-rank='false' data-marketcap='false' data-volume='false'></div>

		</div>

	</div>

	<script src='./scripts/jquery/jquery-2.1.4.min.js'                       type='text/javascript'></script>
	<script src='./scripts/datetimepicker/jquery.datetimepicker.full.min.js' type='text/javascript'></script>
	<script src='./scripts/chart/chart.js'                                   type='text/javascript'></script>
	<script src='./scripts/library.js'                                       type='text/javascript'></script>
	<script type='text/javascript'>
		// Init chart
		initChart();
		initDatepicker();

		// Load currency
		loadCurrency();
	</script>
	
</body>
</html>