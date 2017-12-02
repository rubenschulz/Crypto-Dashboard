<?php

/***
 * @copyright 2017 Ruben Schulz
 * @author    Ruben Schulz <info@rubenschulz.nl>
 * @package   Crypto Dashboard
 * @link      http://www.rubenschulz.nl/
 * @version   1.0
***/

/*** Init system ***/
	// Set UTF-8 header
	header('Content-type: text/html; charset=UTF-8');
	
	// Set error reporting & zlib output compression
	error_reporting(E_ALL);
	ini_set('display_errors', true);
	ini_set('zlib.output_compression', true);

	// Set default timezone
	date_default_timezone_set('Europe/Amsterdam');
	
	// Require config
	require_once(__DIR__.'/config/config.inc.php');



/*** Set variables ***/
	$response = array();
	$data     = array();
	$sql      = '';



/*** Actions ***/
	try {
		// Connect to database
		$database = new PDO('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME, DATABASE_USERNAME, DATABASE_PASSWORD);
		$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// Get transactions
		$sql['get_transactions'] = "
			SELECT 
				DISTINCT (transactions.label)
			FROM
				transactions
			WHERE 1 = 1
			ORDER BY
				transactions.label ASC
		";
		$transactions = $database->query($sql['get_transactions'])->fetchAll(PDO::FETCH_ASSOC);

		foreach($transactions as $transaction){
			// Get history
			$sql['get_history_'.$transaction['label']] = "
				SELECT 
					markets.label,
					markets.name,
					DATE_FORMAT(intervals.timestamp, '%d-%m-%Y') AS date,
					MIN(markets.price_eur)                       AS minimum,
					MAX(markets.price_eur)                       AS maximum,
					AVG(markets.price_eur)                       AS average,
					(
						SELECT 
							SUM(transactions.amount) 
						FROM 
							transactions 
						WHERE 1 = 1
							AND transactions.label  = '".$transaction['label']."'
							AND markets.timestamp  >= transactions.timestamp
						GROUP BY 
							transactions.label
					) AS amount
				FROM
					intervals
				LEFT JOIN 
					markets
				ON 
					intervals.timestamp      = markets.timestamp
					AND markets.label        = '".$transaction['label']."'
				LEFT JOIN
					transactions
				ON 
					markets.label            = transactions.label
					AND markets.timestamp   >= transactions.timestamp
				WHERE 1 = 1
					AND intervals.timestamp <= NOW()
				GROUP BY 
					DATE(intervals.timestamp)
				ORDER BY
					intervals.timestamp ASC
			";
			$data[$transaction['label']] = $database->query($sql['get_history_'.$transaction['label']])->fetchAll(PDO::FETCH_ASSOC);

			// Rebuild data
			foreach($data[$transaction['label']] as $row){
				if(!empty($row['label'])){
					// Set row
					$response[$row['date']][$row['label']]          = $row;
					$response[$row['date']][$row['label']]['total'] = !empty($response[$row['date']][$row['label']]['total']) ? $response[$row['date']][$row['label']]['total'] + $row['amount'] : 0;
				}
			}
		}

		// Close connection
		$database = null;

	}catch(PDOException $e){
		// Set error message
		$data = $e->getMessage();
	}

?>
<!doctype html>

<html>
<head>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1.0' />
	<link href='style/foundation/foundation.min.css' rel='stylesheet' type='text/css' media='screen' />
	<title>Crypto Dashboard</title>
</head>

<body>

	<table>
		<thead>
			<tr>
				<th>Date</th>
				<?php foreach($response[key($response)] as $key => $row){ ?>
					<th colspan='4' class='text-center'><?php print($row['name'].' ('.$row['label'].')'); ?></th>
				<?php } ?>
				<th>Total</th>
			</tr>
			<tr>
				<th></th>
				<?php foreach($response[key($response)] as $key => $row){ ?>
					<th>Amount</th>
					<th>Minimum</th>
					<th>Maximum</th>
					<th>Average</th>
				<?php } ?>
				<th></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach($response as $key => $row){ ?>
				<tr>
					<td><?php print($key); ?></td>

					<?php foreach($row as $column){ ?>
						<td><?php print(number_format($column['amount'], 5)); ?></td>
						<td>€ <?php print(number_format($column['amount'] * $column['minimum'], 2)); ?></td>
						<td>€ <?php print(number_format($column['amount'] * $column['maximum'], 2)); ?></td>
						<td>€ <?php print(number_format($column['amount'] * $column['average'], 2)); ?></td>
					<?php } ?>

					<td><?php print($column['total']); ?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>

</body>
</html>