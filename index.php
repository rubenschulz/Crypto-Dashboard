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
			<div class='small-12 cell'>
				<div id='container'></div>
			</div>
		</div>

		<hr />

		<div id='widgets' class='grid-x'>

			<div class='small-12 medium-6 large-3 cell hide' data-currency='' data-base='EUR' data-secondary='USD' data-rank='false' data-marketcap='false' data-volume='false'></div>

		</div>

	</div>

	<script src='https://code.jquery.com/jquery-3.1.1.min.js'></script>
	<script src='https://code.highcharts.com/stock/highstock.js'></script>
	<script src='http://code.highcharts.com/modules/exporting.js'></script>
	<script src='./scripts/app.js' type='text/javascript'></script>
	
</body>
</html>