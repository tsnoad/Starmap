<?php

	/*
	 * Calculate Objects
	 *
	 * This file gets the data of stars, planets and messier objects
	 * and prepares it to be sent on to javacript
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

	/*
	 * Calculate sun, moon, and planet positions
	 */

	//See http://stjarnhimlen.se/comp/ppcomp.html
	//for in-depth explanation of these calculations

	//the current date
	$y = gmdate("Y");
	$m = gmdate("m");
	$D = gmdate("d");

	//convert date into the epoch expected by these formulas. julian?
	$d = 367 * $y - floor((7 * ($y + floor(($m + 9) / 12))) / 4) + floor((275 * $m) / 9) + $D - 730530;

	//seconds since the start of the day
	$UT_seconds = fmod(time(), 86400);
	//fraction of a day, since the start of the day
	$UT = $UT_seconds / (60 * 60 * 24);

	//add the day fraction to the complete days
	$d += $UT;

	//calculate the obliquity of the ecliptic
	//IE earths current tilt
    $ecl = deg2rad(23.4393 - 0.0000003563 * $d);


	/*
	 * Calculate the position of the sun
	 */

	//predefined variables for the sun
	//argument of perihelion
    $w = deg2rad(282.9404 + 0.0000470935 * $d);
	//eccentricity
    $e = 0.016709 - 0.000000001151 * $d;
	//mean anomaly
    $M = deg2rad(356.0470 + 0.9856002585 * $d);

	//calculate eccentric anomaly
	$E = $M + (($e * sin($M)) * (1 + ($e * cos($M))));

    $xv = cos($E) - $e;
    $yv = sqrt(1.0 - $e * $e) * sin($E);

	//calculate sun's true anomoly
    $v = atan2($yv, $xv);
	//and distance
    $r = sqrt($xv * $xv + $yv * $yv);

	//true longitude
    $lonsun = $v + $w;

	//calculate ecliptic rectangular geocentric coordinates
	//zs will always be zero since the sun is always on the ecliptic
    $xs = $r * cos($lonsun);
    $ys = $r * sin($lonsun);

	//calculate equatorial rectangular geocentric coordinates
    $xe = $xs;
    $ye = $ys * cos($ecl);
    $ze = $ys * sin($ecl);

	//calculate the sun's right ascencion and declination
    $RA = atan2($ye, $xe);
    $Dec = atan2($ze, sqrt($xe * $xe + $ye * $ye));

	//add to the json string with all the other stars
	//so that it's position on the skydome will be calculated, and drawn
	$json_pass .= json_encode(array("n" => "sun", "x" => rad2deg($RA), "y" => rad2deg($Dec), "r" => 4, "c" => "#ffffff")).",";
	//add to array of planets so that it will be labelled
	$json_planets['sun']['n'] = ucwords('sun');


	/*
	 * Calculate the positions of the moon and the planets
	 */

	//for the moon
	//predefined variables
	$moon = true;
	//longitude of the ascending node
    $N = deg2rad(125.1228 - 0.0529538083 * $d);
	//inclination to the ecliptic
    $i = deg2rad(5.1454);
	//argument of perihelion
    $w = deg2rad(318.0634 + 0.1643573223 * $d);
	//semi-major axis, or mean distance from Sun
	$a = 60.2666;
	//eccentricity
	$e = 0.054900;
	//mean anomaly
	$M = deg2rad(115.3654 + 13.0649929509 * $d);
	//add to an array that we can loop through
	$planets['moon'] = array("moon" => $moon, "color" => "white", "N" => $N, "i" => $i, "w" => $w, "a" => $a, "e" => $e, "M" => $M);

	//for venus
	//predefined variables
	$moon = false;
    $N =  deg2rad(76.6799 + 0.0000246590 * $d);
    $i = deg2rad(3.3946 + 0.0000000275 * $d);
    $w =  deg2rad(54.8910 + 0.0000138374 * $d);
    $a = 0.723330;
    $e = 0.006773 - 0.000000001302 * $d;
    $M =  deg2rad(48.0052 + 1.6021302244 * $d);
	$planets['venus'] = array("moon" => $moon, "color" => "white", "N" => $N, "i" => $i, "w" => $w, "a" => $a, "e" => $e, "M" => $M);

	//for mars
	//predefined variables
	$moon = false;
    $N =  deg2rad(49.5574 + 0.0000211081 * $d);
    $i = deg2rad(1.8497 - 0.0000000178 * $d);
    $w = deg2rad(286.5016 + 0.0000292961 * $d);
    $a = 1.523688;
    $e = 0.093405 + 0.000000002516 * $d;
    $M =  deg2rad(18.6021 + 0.5240207766 * $d);
	$planets['mars'] = array("moon" => $moon, "color" => "white", "N" => $N, "i" => $i, "w" => $w, "a" => $a, "e" => $e, "M" => $M);

	//for jupiter
	//predefined variables
	$moon = false;
    $N = deg2rad(100.4542 + 0.0000276854 * $d);
    $i = deg2rad(1.3030 - 0.0000001557 * $d);
    $w = deg2rad(273.8777 + 0.0000164505 * $d);
    $a = 5.20256;
    $e = 0.048498 + 0.000000004469 * $d;
    $M =  deg2rad(19.8950 + 0.0830853001 * $d);
	$planets['jupiter'] = array("moon" => $moon, "color" => "white", "N" => $N, "i" => $i, "w" => $w, "a" => $a, "e" => $e, "M" => $M);


	/*
	 * Calculate the positions
	 */

	//loop through the planets we just defined
	foreach ($planets as $planet_name => $planet) {
		//get the pre defined variables from the array
	    $N = $planet['N'];
	    $i = $planet['i'];
	    $w = $planet['w'];
		$a = $planet['a'];
		$e = $planet['e'];
		$M = $planet['M'];
		
		//calculate eccentric anomaly
		$E = $M + (($e * sin($M)) * (1 + ($e * cos($M))));
	
		$xv = $a * (cos($E) - $e);
		$yv = $a * (sqrt(1.0 - ($e * $e)) * sin($E));
		
		//calculate planet's true anomoly
		$v = atan2($yv, $xv);
		//and distance
		$r = sqrt(($xv * $xv) + ($yv * $yv));
	
		//calculate co ordinates
		//for planets these will be ecliptic heliocentric coordinates
		//for the moon these will be ecliptic geocentric coordinates
		$xh = $r * (cos($N) * cos($v + $w) - sin($N) * sin($v + $w) * cos($i));
		$yh = $r * (sin($N) * cos($v + $w) + cos($N) * sin($v + $w) * cos($i));
		$zh = $r * (sin($v + $w) * sin($i));
	
		//if we're calculating for the moon
		if (true) {
			//we already have geocentric coordinates
			//so no calculation required
			$xg = $xh;
			$yg = $yh;
			$zg = $zh;

		//if we're calculating for a planet
		} else {
			//take the planet's distance, that we calculated earlier
			$rs = $r;
	
			//take the sun's ecliptic rectangular geocentric coordinates, that we calculated earlier
		    $xs = $xs;
		    $ys = $xs;
	
			//calculate the planet's ecliptic geocentric coordinates
			$xg = $xh + $xs;
			$yg = $yh + $ys;
			$zg = $zh;
		}
	
		//calculate equatorial rectangular geocentric coordinates
	    $xe = $xg;
	    $ye = $yg * cos($ecl) - $zg * sin($ecl);
	    $ze = $yg * sin($ecl) + $zg * cos($ecl);
	
		//calculate the planet's right ascencion and declination
	    $RA = atan2($ye, $xe);
	    $Dec = atan2($ze, sqrt($xe * $xe + $ye * $ye));
	
		//add to the json string with all the other stars
		//so that it's position on the skydome will be calculated, and drawn
		$json_pass .= json_encode(array("n" => $planet_name, "x" => rad2deg($RA), "y" => rad2deg($Dec), "r" => 2, "c" => $planet['color'])).",";
		//add to array of planets so that it will be labelled
		$json_planets[$planet_name]['n'] = ucwords($planet_name);
	}


	/*
	 * Draw and label major Messier objects: nebulae, star clusters and galaxies
	 */

	//Messier 45: Pleiades
	$RA = 3 * 15 + 47 * 0.25;
	$Dec = 24 + 7 * 0.25;
	$json_pass .= json_encode(array("n" => "M45", "x" => $RA, "y" => $Dec, "r" => 1, "c" => "rgba(0, 0, 0, 0)")).",";
	$json_planets['M45']['n'] = ucwords("M45");

	//Messier 31: Andromeda Galaxy
	$RA = 0 * 15 + 42 * 0.25;
	$Dec = 41 + 16 * 0.25;
	$json_pass .= json_encode(array("n" => "M31", "x" => $RA, "y" => $Dec, "r" => 1, "c" => "rgba(0, 0, 0, 0)")).",";
	$json_planets['M31']['n'] = ucwords("M31");

	//Messier 7: Ptolemy Cluster
	$RA = 17 * 15 + 53.9 * 0.25;
	$Dec = -1 * (34 + 49 * 0.25);
	$json_pass .= json_encode(array("n" => "M7", "x" => $RA, "y" => $Dec, "r" => 1, "c" => "rgba(0, 0, 0, 0)")).",";
	$json_planets['M7']['n'] = ucwords("M7");

	//Messier 44: Beehive Cluster
	$RA = 8 * 15 + 40.4 * 0.25;
	$Dec = 19 + 41 * 0.25;
	$json_pass .= json_encode(array("n" => "M44", "x" => $RA, "y" => $Dec, "r" => 1, "c" => "rgba(0, 0, 0, 0)")).",";
	$json_planets['M44']['n'] = ucwords("M44");

	//Messier 42: Orion Nebula
	$RA = 5 * 15 + 35 * 0.25;
	$Dec = -1 * (5 + 23 * 0.25);
	$json_pass .= json_encode(array("n" => "M42", "x" => $RA, "y" => $Dec, "r" => 1, "c" => "rgba(0, 0, 0, 0)")).",";
	$json_planets['M42']['n'] = ucwords("M42");


	/*
	 * Try to draw the milky way
	 */

/*
	$milky_way_svg = "M 1919.9062 -468.46875 L 1856.1562 -465.46875 L 1725.2812 -431.8125 L 1669.5938 -371.25 L 1584.8438 -315 L 1544.0625 -308.90625 L 1429.5938 -299.90625 L 1410 -316.875 L 1451.3438 -351.375 L 1495.7812 -382.21875 L 1507.7812 -416.90625 L 1418.9062 -396.5625 L 1375.4062 -380.71875 L 1326 -333.5625 L 1306.3125 -309 L 1253.4375 -344.0625 L 1133.5312 -395.15625 L 1052.5312 -390.375 L 988.875 -381.09375 L 934.40625 -338.4375 L 920.71875 -313.96875 L 911.15625 -305.34375 L 864.375 -307.40625 L 817.5 -309.5625 L 787.5 -299.8125 L 687 -295.21875 L 634.5 -300 L 571.5 -307.21875 L 448.59375 -319.40625 L 423 -300.375 L 460.78125 -222.09375 L 532.21875 -205.3125 L 604.5 -199.3125 L 681 -193.78125 L 741 -195.75 L 895.5 -202.40625 L 987.375 -210.46875 L 1098.375 -237.5625 L 1172.8125 -270 L 1190.9062 -246.375 L 1206.4688 -214.125 L 1261.9688 -194.625 L 1405.5 -207.375 L 1501.6875 -222 L 1637.25 -156.09375 L 1708.9688 -147.9375 L 1764 -154.5 L 1820.25 -160.6875 L 1910.4375 -159 L 1999.4062 -162.09375 L 2102.8125 -177 L 2159.8125 -182.90625 L 2241.4688 -193.40625 L 2316.4688 -205.21875 L 2407.5 -213.1875 L 2480.8125 -202.6875 L 2539.3125 -195.1875 L 2587.5 -198.09375 L 2731.2188 -199.5 L 2859.375 -207.1875 L 2982.1875 -216 L 3100.9688 -222.5625 L 3162 -223.96875 L 3227.3438 -216 L 3276 -210.09375 L 3343.3125 -216.28125 L 3397.5938 -222.28125 L 3475.5 -229.3125 L 3528 -232.125 L 3579.75 -234.84375 L 3600 -239.25 L 3600 -371.25 L 3588.5625 -373.03125 L 3394.5 -377.625 L 3253.5 -379.125 L 3099 -393.65625 L 2923.875 -387.375 L 2830.125 -384.46875 L 2802.8438 -390.9375 L 2769.9375 -382.96875 L 2719.2188 -374.90625 L 2680.5 -370.21875 L 2577 -372.84375 L 2445 -391.78125 L 2404.5 -399.28125 L 2340 -408.1875 L 2265.0938 -420.46875 L 2185.5938 -432.28125 L 2126.25 -436.875 L 2064.1875 -415.96875 L 1978.2188 -424.21875 L 1935.4688 -459.9375 L 1919.9062 -468.46875 z M 0.84375 -366 L 0 -239.25 L 11.25 -241.03125 L 57.375 -238.21875 L 124.5 -238.59375 L 183.09375 -244.3125 L 232.125 -249.9375 L 247.6875 -309.65625 L 144.9375 -344.8125 L 100.96875 -350.90625 L 0.84375 -366 z M 2625.9375 0 L 2607 19.3125 L 2626.5 39 L 2638.125 32.8125 L 2646 20.25 L 2625.9375 0 z";

	$milky_way_points = explode(" ", $milky_way_svg);

	for ($i = 0; $i < count($milky_way_points); $i += 3) {
		if ($milky_way_points[$i] == "z") {
			$i -= 3;
			$i += 1;
			continue;
		}
	
		//deal with crazy SVG measurements from bottom of page
		$milky_way_points[$i + 2] -= 600;
		$milky_way_points[$i + 2] += 1800;
	
		$xpos = $milky_way_points[$i + 1];
		$ypos = $milky_way_points[$i + 2];
	
		$xpos /= 10;
		$ypos /= 10;
		$ypos -= 90;
	
		if ($milky_way_points[$i] == "M") {
			$json_pass .= json_encode(array("mw" => "M", "x" => $xpos, "y" => $ypos, "r" => 1, "c" => "rgba(0, 0, 0, 0)")).",";
		} else {
			$json_pass .= json_encode(array("mw" => "L", "x" => $xpos, "y" => $ypos, "r" => 1, "c" => "rgba(0, 0, 0, 0)")).",";
		}
	}
*/

	/*
	 * Get stars from the database, and get the information we'll need to pass on to javascript to draw them
	 */

	//get the 10000 brightest stars
	$stars = runQuery("SELECT name, vmag, ra, dec, spect_type FROM stars WHERE vmag < 5.0 ORDER BY vmag LIMIT 1000;");
/* 	$stars = runQuery("SELECT name, vmag, ra, dec, spect_type FROM stars WHERE vmag < 5.0 union select name, vmag, ra, dec, spect_type from stars where ra>129 and ra<169 and dec>-55 and dec<-15 and vmag > 5.0 and vmag < 9.0;"); */

	foreach ($stars as $i => $star) {

		//calculate star color and brightness

		//show dimmer stars as more transparent
		$starcolopacity = min(1 - (($star['vmag'] - 1) / 4), 1);
/* 		$starcolopacity = min(1 - (($star['vmag'] - 1) / 8), 1); */

		//make up star rgb from color spectral type
		switch (substr($star['spect_type'], 0, 1)) {
			case "O":
				$starcol = "rgba(155, 176, 255, {$starcolopacity})";
				break;
			case "B":
				$starcol = "rgba(170, 191, 255, {$starcolopacity})";
				break;
			case "A":
				$starcol = "rgba(202, 215, 255, {$starcolopacity})";
				break;
			case "F":
				$starcol = "rgba(248, 247, 255, {$starcolopacity})";
				break;
			case "G":
				$starcol = "rgba(255, 244, 234, {$starcolopacity})";
				break;
			case "K":
				$starcol = "rgba(255, 210, 161, {$starcolopacity})";
				break;
			case "M":
				$starcol = "rgba(255, 204, 111, {$starcolopacity})";
				break;
		}

		//calculate star size
		$rad = ($star['vmag'] + 1.6) / (8.2);
		$rad = 1 - $rad;
		$rad *= 2.5;
		$rad = max($rad, 1);

		//label bright stars
		unset($brightstar_name);
		switch ($star['name']) {
			default:
				break;
			case "SAO 151881":
				$brightstar_name = "sirius";
				break;
			case "SAO 234480":
				$brightstar_name = "canopus";
				break;
			case "SAO 100944":
				$brightstar_name = "arcturus";
				break;
			case "SAO 252838":
				$brightstar_name = "alpha centauri";
				break;
			case "SAO 67174":
				$brightstar_name = "vega";
				break;
			case "SAO 131907":
				$brightstar_name = "rigel";
				break;
		}

		//round co-ordinates to 2 decimal places: this will save on space when we transmit them to the client
		$star['ra'] = round($star['ra'], 2);
		$star['dec'] = round($star['dec'], 2);
		//round star radius to 1 decimal place
		$rad = round($rad, 1);

		//create an array with the star's details
		$stararr = array("x" => $star['ra'], "y" => $star['dec'], "r" => $rad, "c" => $starcol);

		//if this star is meant to be labelled
		if (!empty($brightstar_name)) {
			//add the name the star details array
			//so that it's position will be sent to the labeller
			$stararr['n'] = $brightstar_name;
			//add it the array of planets, so that it will be labelled
			$json_planets[$brightstar_name]['n'] = ucwords($brightstar_name);
		}


		//create a json string with this star's details, and concatenate into a long json string of all stars
		//it's done this way because we can't simply json_encode the whole array: that causes php to run out of memory

		//prepend a comma in front of this star's json string, so that we have valid json					
		if ($not_first_star) {
			$json_pass .= ",";

		//but not if we're looking at the first star in the list: that wouldn't make valid json
		} else {
			$not_first_star = true;
		}

		//concatenate the star's details
		$json_pass .= json_encode($stararr);
	}
?>