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
	$api_url  = 'https://api.coinmarketcap.com/v1/ticker/';
	$response = array();
	$history  = array();
	$totals   = array();
	$data     = array();
	$sql      = array();



/*** Actions ***/
	// Open connection
	$curl           = curl_init();

	// Build header
	$header         = array();
	$header[]       = 'Content-Type: application/json';

	// Set options
	curl_setopt($curl, CURLOPT_URL,            $api_url.'?limit=0&convert=EUR');
	curl_setopt($curl, CURLOPT_HTTPHEADER,     $header);
	curl_setopt($curl, CURLOPT_HEADER,         true); 
	curl_setopt($curl, CURLOPT_VERBOSE,        true); 
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 

	// Execute call
	$result         = curl_exec($curl);

	// Get result parts
	$header_size    = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
	$header         = substr($result, 0, $header_size);
	$response       = substr($result, $header_size);
	$status         = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	// Close connection
	curl_close($curl);

	// Decode response
	$response       = json_decode($response);



/*** Databaase ***/
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
			ORDER BY
				transactions.label ASC
		";
		$data['transactions'] = $database->query($sql['get_transactions'])->fetchAll(PDO::FETCH_ASSOC);

		// Rebuild array
		$transactions = array();
		foreach($data['transactions'] as $transaction){
			$transactions[] = $transaction['label'];
		}

		// Build SQL
		$sql['save_market'] = "
			INSERT INTO markets (
				label,
				name,
				price_btc,
				price_usd,
				price_eur,
				volume_24h,
				timestamp
			) VALUES (
				:label,
				:name,
				:price_btc,
				:price_usd,
				:price_eur,
				:volume_24h,
				:timestamp
			)
		";

		// Prepare statement
		$statement = $database->prepare($sql['save_market']);

		// Loop through markets
		foreach($response as $market){
			// Check if market in transactions
			if(in_array($market->symbol, $transactions)){
				// Bind values
				$statement->bindValue(':label',      $market->symbol);
				$statement->bindValue(':name',       $market->name);
				$statement->bindValue(':price_btc',  $market->price_btc);
				$statement->bindValue(':price_usd',  $market->price_usd);
				$statement->bindValue(':price_eur',  $market->price_eur);
				$statement->bindValue(':volume_24h', $market->{'24h_volume_eur'});
				$statement->bindValue(':timestamp',  date('Y-m-d H:i:00'));

				// Execute statement
				$statement->execute();
			}
		}

		// Delete Bitgem
		$database->query('DELETE FROM markets WHERE name = "Bitgem"');

		// Set memory limit
		ini_set('memory_limit', '256M');

		// Loop through transactions
		foreach($transactions as $transaction){
			// Get history
			$sql['get_history_'.$transaction] = "
				SELECT 
					markets.label,
					markets.name,
					UNIX_TIMESTAMP(intervals.timestamp) * 1000   AS timestamp,
					SUM(transactions.amount)                     AS amount,
					markets.price_eur * SUM(transactions.amount) AS value
				FROM
					intervals
				LEFT JOIN 
					markets
				ON 
					intervals.timestamp      = markets.timestamp
					AND markets.label        = '".$transaction."'
				LEFT JOIN
					transactions
				ON 
					markets.label            = transactions.label
					AND markets.timestamp   >= transactions.timestamp
				WHERE 1 = 1
					AND intervals.timestamp <= '".date('Y-m-d H:i:s')."'
					AND transactions.amount IS NOT NULL
				GROUP BY 
					intervals.timestamp
				ORDER BY
					intervals.timestamp ASC
			";
			$data['history_'.$transaction] = $database->query($sql['get_history_'.$transaction])->fetchAll(PDO::FETCH_ASSOC);

			if(!empty($data['history_'.$transaction])){
				// Set history
				$history[$transaction]         = array();
				$history[$transaction]['name'] = $data['history_'.$transaction][0]['name'];
				$history[$transaction]['data'] = array();

				// Loop trough transactions
				foreach($data['history_'.$transaction] as $key => $value){
					$history[$transaction]['data'][] = array(
						(int)$value['timestamp'], 
						(float)$value['value'], 
						(float)$value['amount']
					);
				}
			}

			// Get history
			$sql['get_totals_'.$transaction] = "
				SELECT 
					markets.label,
					markets.name,
					UNIX_TIMESTAMP(intervals.timestamp) * 1000   AS timestamp,
					MIN(markets.price_eur)                       AS minimum,
					MAX(markets.price_eur)                       AS maximum,
					AVG(markets.price_eur)                       AS average,
					(
						SELECT 
							SUM(transactions.amount) 
						FROM 
							transactions 
						WHERE 1 = 1
							AND transactions.label  = '".$transaction."'
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
					AND markets.label        = '".$transaction."'
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
			$data[$transaction] = $database->query($sql['get_totals_'.$transaction])->fetchAll(PDO::FETCH_ASSOC);

			// Rebuild data
			foreach($data[$transaction] as $value){
				// Set row
				$data['totals'][$value['timestamp']]['timestamp'] = (int)$value['timestamp'];
				$data['totals'][$value['timestamp']]['minimum']   = !empty($data['totals'][$value['timestamp']]['minimum']) ? $data['totals'][$value['timestamp']]['minimum'] + ($value['amount'] * $value['minimum']) : $value['amount'] * $value['minimum'];
				$data['totals'][$value['timestamp']]['maximum']   = !empty($data['totals'][$value['timestamp']]['maximum']) ? $data['totals'][$value['timestamp']]['maximum'] + ($value['amount'] * $value['maximum']) : $value['amount'] * $value['maximum'];
				$data['totals'][$value['timestamp']]['average']   = !empty($data['totals'][$value['timestamp']]['average']) ? $data['totals'][$value['timestamp']]['average'] + ($value['amount'] * $value['average']) : $value['amount'] * $value['average'];
			}
		}

		// Rebuild data
		foreach($data['totals'] as $key => $value){
			$totals['averages'][] = array(
				(int)$value['timestamp'],
				(int)$value['average']
			);

			$totals['ranges'][] = array(
				(int)$value['timestamp'],
				(int)$value['minimum'],
				(int)$value['maximum']
			);
		}

		// Write JSON
		$fp = fopen('data/data-history.json', 'w');
		fwrite($fp, json_encode($history));
		fclose($fp);

		// Write JSON
		$fp = fopen('data/data-totals.json', 'w');
		fwrite($fp, json_encode($totals));
		fclose($fp);

		// Close connection
		$database = null;

	}catch(PDOException $e){
		// Print error message
		echo $e->getMessage();
	}

?>