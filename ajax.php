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



/*** Functions ***/
	function simplify($points, $tolerance = 1, $highestQuality = false) {
		if (count($points) < 2) return $points;
		$sqTolerance = $tolerance * $tolerance;
		if (!$highestQuality) {
			$points = simplifyRadialDistance($points, $sqTolerance);
		}
		$points = simplifyDouglasPeucker($points, $sqTolerance);
		return $points;
	}

	function getSquareDistance($p1, $p2) {
		$dx = $p1['timestamp'] - $p2['timestamp'];
		$dy = $p1['value'] - $p2['value'];
		return $dx * $dx + $dy * $dy;
	}

	function getSquareSegmentDistance($p, $p1, $p2) {
		$x = $p1['timestamp'];
		$y = $p1['value'];
		$dx = $p2['timestamp'] - $x;
		$dy = $p2['value'] - $y;
		if ($dx <> 0 || $dy <> 0) {
			$t = (($p['timestamp'] - $x) * $dx + ($p['value'] - $y) * $dy) / ($dx * $dx + $dy * $dy);
			if ($t > 1) {
				$x = $p2['timestamp'];
				$y = $p2['value'];
			} else if ($t > 0) {
				$x += $dx * $t;
				$y += $dy * $t;
			}
		}
		$dx = $p['timestamp'] - $x;
		$dy = $p['value'] - $y;
		return $dx * $dx + $dy * $dy;
	}

	function simplifyRadialDistance($points, $sqTolerance) { // distance-based simplification	
		$len = count($points);
		$prevPoint = $points[0];
		$newPoints = array($prevPoint);
		$point = null;
		
		for ($i = 1; $i < $len; $i++) {
			$point = $points[$i];
			if (getSquareDistance($point, $prevPoint) > $sqTolerance) {
				array_push($newPoints, $point);
				$prevPoint = $point;
			}
		}
		if ($prevPoint !== $point) {
			array_push($newPoints, $point);
		}
		return $newPoints;
	}

	// simplification using optimized Douglas-Peucker algorithm with recursion elimination
	function simplifyDouglasPeucker($points, $sqTolerance) {
		$len = count($points);
		$markers = array_fill ( 0 , $len - 1, null);
		$first = 0;
		$last = $len - 1;
		$firstStack = array();
		$lastStack  = array();
		$newPoints  = array();
		$markers[$first] = $markers[$last] = 1;
		while ($last) {
			$maxSqDist = 0;
			for ($i = $first + 1; $i < $last; $i++) {
				$sqDist = getSquareSegmentDistance($points[$i], $points[$first], $points[$last]);
				if ($sqDist > $maxSqDist) {
					$index = $i;
					$maxSqDist = $sqDist;
				}
			}
			if ($maxSqDist > $sqTolerance) {
				$markers[$index] = 1;
				array_push($firstStack, $first);
				array_push($lastStack, $index);
				array_push($firstStack, $index);
				array_push($lastStack, $last);
			}
			$first = array_pop($firstStack);
			$last = array_pop($lastStack);
		}
		for ($i = 0; $i < $len; $i++) {
			if ($markers[$i]) {
				array_push($newPoints, $points[$i]);
			}
		}
		return $newPoints;
	}


/*** Set variables ***/
	$response = array();
	$data     = array();
	$sql      = '';



/*** Actions ***/
	try {
		// Connect to database
		$database = new PDO('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME, DATABASE_USERNAME, DATABASE_PASSWORD);
		$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// Clean REQUEST
		$_REQUEST['date_from'] = !empty($_REQUEST['date_from']) ? $_REQUEST['date_from'] : date('Y-m-d', strtotime('7 days ago'));
		$_REQUEST['time_from'] = !empty($_REQUEST['time_from']) ? $_REQUEST['time_from'] : '00:00:00';
		$_REQUEST['date_to']   = !empty($_REQUEST['date_to'])   ? $_REQUEST['date_to']   : date('Y-m-d');
		$_REQUEST['time_to']   = !empty($_REQUEST['time_to'])   ? $_REQUEST['time_to']   : ($_REQUEST['date_to'] == date('Y-m-d') ? date('H:i:s') : '23:59:59');

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
					AND intervals.timestamp >= '".$_REQUEST['date_from']." ".$_REQUEST['time_from']."'
					AND intervals.timestamp <= '".$_REQUEST['date_to']." ".$_REQUEST['time_to']."'
					AND intervals.timestamp <= NOW()
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

			// Simplify graph
			$tolerance = (strtotime($_REQUEST['date_to']) - strtotime($_REQUEST['date_from'])) / (60 * 60 * 24) / 60;
//			!empty($data[$transaction['label']]) ? $data[$transaction['label']] = simplify($data[$transaction['label']], $tolerance) : '';
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