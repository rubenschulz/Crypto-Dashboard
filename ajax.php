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
					DATE_FORMAT(intervals.timestamp, '%d-%m-%Y %H:%i') AS timestamp,
					SUM(transactions.amount)                           AS amount,
					markets.price_eur * SUM(transactions.amount)       AS value
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
					AND intervals.timestamp <= '".date('Y-m-d H:i:s')."'
				GROUP BY 
					intervals.timestamp
				ORDER BY
					intervals.timestamp ASC
			";
			$data[$transaction['label']] = $database->query($sql['get_history_'.$transaction['label']])->fetchAll(PDO::FETCH_ASSOC);

			// Unset if empty
			if(empty(end($data[$transaction['label']])['value'])){
				unset($data[$transaction['label']]);
			}
		}

		// Set response
		$response['result'] = true;
		$response['data']   = $data;

		// Close connection
		$database = null;

	}catch(PDOException $e){
		// Set error message
		$response['result'] = false;
		$response['data']   = $e->getMessage();
	}

	// Set headers
	header('Content-type:application/json');

	// Output
	echo json_encode($response, JSON_PRETTY_PRINT);

?>