<?php

$user_location = $_POST['location'];

if (empty($user_location)) {
	?><div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;">type type type</div><?
	die();
}

$locations = array("foo", "foofoo", "foobar", "barfoo", "bar");

function runQuery($query) {
	//Open the database connection. We only want one per server request.
	$db_conn = pg_connect("host=localhost dbname=starmap user=simm password=102mark4");
	//Die on DB connect error.
	if (!$db_conn) die("ERROR: Unable to connect to the database.");
	
	//Run a regular query.
	$result = pg_query($db_conn, $query);
	
	$arr = pg_fetch_all($result);
	$error = pg_last_error($db_conn);

	//Logging. Selects are only logged at log level 5. All gged at log level 4.
	if (stripos($query, "select") === 0) {
		//Only log at level 5 (debug) because selects aren't very interesting. If there is an error, log at level 2 (errors).
/* 			$log = $log_obj->log_mesg(($result!==false?5:2), "[Query] ".substr($query, 0, 10000). " [Result] ".($result!==false?"Query Executed OK.":"Error: $error.")); */
	} else {
		//Only log at level 4 (info) because inserts, updates and deletes are only slightly more interesting that selects. If there is an error, log at level 2 (errors).
/* 			$log = $log_obj->log_mesg(($result!==false?4:2), "[Query] ".substr($query, 0, 10000). " [Result] ".($result!==false?"Query Executed OK.":"Error: $error.")); */
	}

	return $arr;
}

$locations = runQuery("SELECT name, country, lat, lon FROM locations WHERE name ILIKE '{$user_location}%' ORDER BY population DESC LIMIT 20;");

if (empty($locations)) {
	?><div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;">aaaargh</div><?
	die();
}

foreach ($locations as $location) {
	?><div class="location_option" onmouseover="this.className='location_option_hover';" onmouseout="this.className='location_option';" onclick="location_change('<?= $location['name'] ?>', '<?= $location['country'] ?>', <?= round($location['lat'], 4) ?>, <?= round($location['lon'], 4) ?>);"><span><?= $location['name'] ?></span><span style="">, <?= $location['country'] ?></span></div><?
	$matches ++;
}

if ($matches < 3) {
	?><hr style="position: relative; margin: 15px 10px; border: 0px; border-top: 2px solid white; opacity: 0.15;" /><div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;">show more</div><?
}

?>