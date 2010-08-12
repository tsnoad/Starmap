<?php

	/*
	 * List Locations
	 *
	 * Called by ajax to get location options that match the user's typed location
	 */

$time = microtime(true);

file_put_contents("/tmp/starmap.log", "\n\n started logging: ".(microtime(true) - $time)."\n", FILE_APPEND);


	/*
	 * Connect to the database and ask for data ^_^
	 */
	function runQuery($query) {
		//Open the database connection. We only want one per server request.
		$db_conn = pg_connect("host=localhost dbname=starmap user=simm password=102mark4");
		//Die on DB connect error.
		if (!$db_conn) die("ERROR: Unable to connect to the database.");
		
		//Run a regular query.
		$result = pg_query($db_conn, $query);
		
		$arr = pg_fetch_all($result);
		$error = pg_last_error($db_conn);

/*
		//logging disabled for now
		//Logging. Selects are only logged at log level 5. All gged at log level 4.
		if (stripos($query, "select") === 0) {
			//Only log at level 5 (debug) because selects aren't very interesting. If there is an error, log at level 2 (errors).
			$log = $log_obj->log_mesg(($result!==false?5:2), "[Query] ".substr($query, 0, 10000). " [Result] ".($result!==false?"Query Executed OK.":"Error: $error."));
		} else {
			//Only log at level 4 (info) because inserts, updates and deletes are only slightly more interesting that selects. If there is an error, log at level 2 (errors).
			$log = $log_obj->log_mesg(($result!==false?4:2), "[Query] ".substr($query, 0, 10000). " [Result] ".($result!==false?"Query Executed OK.":"Error: $error."));
		}
*/

		return $arr;
	}

	//this is the location name the user entered
	$user_location = $_POST['location'];
	
	//make sure some name was entered
	if (empty($user_location)) {
		//if not, show an error message
		echo "<div style=\"padding: 0px; color: white; font-family: Lucida Grande; font-size: 8pt; text-shadow: 2px 2px 0px black;\">please enter the name of a location or city</div>";
		//do no more. the echod html will be passed back to javascript via ajax
		die();
	}

	//get all locations that match the entered text
	//sanitisation required
	$locations = runQuery("SELECT name, country, timezone, lat, lon FROM locations WHERE name ILIKE '{$user_location}%' ORDER BY population DESC LIMIT 10;");
	
	//if no locations found
	if (empty($locations)) {
		//show an error message
		echo "<div style=\"padding: 0px; color: white; font-family: Lucida Grande; font-size: 8pt; text-shadow: 2px 2px 0px black;\">no locations found</div>";
		//do no more. the echod html will be passed back to javascript via ajax
		die();
	}
	
	//for all the locations that match the entered text
	foreach ($locations as $location) {

file_put_contents("/tmp/starmap.log", "location iteration: ".(microtime(true) - $time)."\n", FILE_APPEND);

		//for this location
		$dateTimeZone = new DateTimeZone($location['timezone']);

file_put_contents("/tmp/starmap.log", "location timezone created: ".(microtime(true) - $time)."\n", FILE_APPEND);

		//find all the timezone offset transitions
		//transitions are dates when the offset changes, for example when daylight savings starts or stops
		$timeTransitions = $dateTimeZone->getTransitions();

file_put_contents("/tmp/starmap.log", "location transitions fetched: ".(microtime(true) - $time)."\n", FILE_APPEND);

		//reverse the array, so that transitions start in the future, and end in the past
		$timeTransitions = array_reverse($timeTransitions);
file_put_contents("/tmp/starmap.log", "location transition reversed: ".(microtime(true) - $time)."\n", FILE_APPEND);


		//loop through all transitions for this location
		foreach ($timeTransitions as $timeTransition) {
			//so we can find the next transition that starts in the past (remember we're looping through in reverse order)
			if ($timeTransition['ts'] <= time()) {
				//now we have the current offset
				$timeOffset = $timeTransition['offset'];
				//and we're done for this location's transitions
				break;
			}
		}

file_put_contents("/tmp/starmap.log", "location transitions processed: ".(microtime(true) - $time)."\n", FILE_APPEND);

		//echo out the location, with a hover effect, and that can be clicked on to set the location
		echo "<div style=\"padding: 5px 0px 0px 0px; color: #999999; font-family: Lucida Grande; font-size: 8pt; cursor: pointer;\" onmouseover=\"\" onmouseout=\"\" onclick=\"location_change('{$location['name']}', '{$location['country']}', '{$timeOffset}', ".round($location['lat'], 2).", ".round($location['lon'], 2).");\"><span>{$location['name']}</span><span style=\"\">, {$location['country']}</span></div>";

file_put_contents("/tmp/starmap.log", "location echod: ".(microtime(true) - $time)."\n", FILE_APPEND);
	}

file_put_contents("/tmp/starmap.log", "finished: ".(microtime(true) - $time)."\n", FILE_APPEND);

?>