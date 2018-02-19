<!doctype html>

<html>
<head>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=no' />
	<link href='https://cdnjs.cloudflare.com/ajax/libs/foundation/6.4.3/css/foundation.min.css'  rel='stylesheet' />
	<link href='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css' rel='stylesheet' />
	<style>
		html,
		body {
			height: 			100%;
		}

		h1 {
			font-size:			1.5rem;
			text-align: 		center;
		}

		h2 {
			font-size:			1.25rem;
		}

		.coinmarketcap-currency-widget { 
			padding-left:		1rem;
			padding-right:		1rem;
			padding-bottom:		0.5rem;

			float:				left;
		}

	</style>
	<title>Crypto Dashboard</title>
</head>

<body>

	<div class='grid-container'>
		<div class='grid-x'>
			<div class='small-12 cell'>
				<h1>Crypto dashboard</h1>
			</div>
			<div class='small-12 cell'>
				<div id='chart-history'></div>
			</div>
		</div>

		<hr />

		<div class='grid-x'>
			<div class='small-12 large-6 cell'>
				<div id='chart-totals'></div>
			</div>
			<div class='small-12 large-6 cell'>
				<div id='chart-distribution'></div>
			</div>
		</div>

		<hr />

		<div id='widgets' class='grid-x'>

			<div class='small-12 medium-6 large-3 cell hide' data-currency='' data-base='EUR' data-secondary='USD' data-rank='false' data-marketcap='false' data-volume='false'></div>

		</div>

	</div>

	<script src='https://code.jquery.com/jquery-3.1.1.min.js'></script>
	<script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
	<script src='https://code.highcharts.com/stock/highstock.js'></script>
	<script src='https://code.highcharts.com/highcharts-more.js'></script>
	<script src='https://code.highcharts.com/modules/exporting.js'></script>
	<script src='./scripts/app.js' type='text/javascript'></script>
	
</body>
</html>