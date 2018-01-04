<!doctype html>

<html>
<head>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1.0' />
	<link href='https://cdnjs.cloudflare.com/ajax/libs/foundation/6.4.3/css/foundation.min.css' rel='stylesheet' />
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

		.totals {
			padding-bottom:		1rem;
		}

		.coinmarketcap-currency-widget { 
			padding:			1rem;
			float:				left;
		}

	</style>
	<title>Crypto Dashboard</title>
</head>

<body>

	<div class='grid-container'>
		<div class='grid-x'>
			<div class='small-12 cell chart'>
				<h1>Crypto Dashboard</h1>
				<a href='totals.php'>Show daily totals</a>

				<div id='container'></div>
			</div>
		</div>

		<hr />

		<div class='grid-x'>

			<div class='coinmarketcap-currency-widget small-12 medium-6 large-3 cell' data-currency='bitcoin'  data-base='EUR' data-secondary='USD' data-rank='false' data-marketcap='false' data-volume='false'></div>
			<div class='coinmarketcap-currency-widget small-12 medium-6 large-3 cell' data-currency='ethereum' data-base='EUR' data-secondary='USD' data-rank='false' data-marketcap='false' data-volume='false'></div>
			<div class='coinmarketcap-currency-widget small-12 medium-6 large-3 cell' data-currency='litecoin' data-base='EUR' data-secondary='USD' data-rank='false' data-marketcap='false' data-volume='false'></div>
			<div class='coinmarketcap-currency-widget small-12 medium-6 large-3 cell' data-currency='ripple'   data-base='EUR' data-secondary='USD' data-rank='false' data-marketcap='false' data-volume='false'></div>

		</div>

	</div>

	<script src='https://code.jquery.com/jquery-3.1.1.min.js'></script>
	<script src='https://code.highcharts.com/stock/highstock.js'></script>
	<script src='./scripts/app.js' type='text/javascript'></script>
	
</body>
</html>