<?php

	/*
	 * List Locations
	 *
	 * Called by ajax to get location options that match the user's typed location
	 */


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
	$locations = runQuery("SELECT name, country, lat, lon FROM locations WHERE name ILIKE '{$user_location}%' ORDER BY population DESC LIMIT 10;");
	
	//if no locations found
	if (empty($locations)) {
		//show an error message
		echo "<div style=\"padding: 0px; color: white; font-family: Lucida Grande; font-size: 8pt; text-shadow: 2px 2px 0px black;\">no locations found</div>";
		//do no more. the echod html will be passed back to javascript via ajax
		die();
	}
	
	//for all the locations that match the entered text
	foreach ($locations as $location) {
		//echo out the location, with a hover effect, and that can be clicked on to set the location
		echo "<div style=\"padding: 5px 0px 0px 0px; color: #999999; font-family: Lucida Grande; font-size: 8pt; cursor: pointer;\" onmouseover=\"\" onmouseout=\"\" onclick=\"location_change('{$location['name']}', '{$location['country']}', ".round($location['lat'], 2).", ".round($location['lon'], 2).");\"><span>{$location['name']}</span><span style=\"\">, {$location['country']}</span></div>";
	}

?>