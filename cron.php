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
	$data     = array();
	$sql      = '';



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
			WHERE 1 = 1
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
		$sql = "
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
		$statement = $database->prepare($sql);

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

		// Close connection
		$database = null;

	}catch(PDOException $e){
		// Print error message
		echo $e->getMessage();
	}

?>