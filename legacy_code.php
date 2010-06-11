<?php

/*
 * Legacy Code
 *
 * Code that's no longer used, but may be useful for reference
 */

if (false) {
	//polar perspective projection
	if (false) {
		$skoo = cos(deg2rad($star['dec'])) * 900;
	
		$xpos = sin(deg2rad($star['ra'])) * $skoo;
		$ypos = cos(deg2rad($star['ra'])) * $skoo;
	
		$xpos += 900;
		$ypos += 900;
	
	//equatorial perspective projection
	} else if (false) {
		$obs_lat = 0;
	
		if (!($star['ra'] >= $obs_lat && $star['ra'] < $obs_lat + 180)) continue;
	
		$star['dec'] *= -1;
		$star['ra'] = 360 - $star['ra'];
	
		$ypos = sin(deg2rad($star['dec'])) * 900;
	
		$xpos = cos(deg2rad($star['ra'] + $obs_lat)) * sqrt(pow(900, 2) - pow(abs($ypos), 2));
	
		$xpos += 900;
		$ypos += 900;
	
	//mobile perspective
	} else if (false) {
		$star['ra'] = fmod($star['ra'] + 180, 360);
	
		$j = $star['dec'];
		$i = $star['ra'];
	
		$viewlat = 45;
		$sphererad = 900;
	
		$ra_rad = ($i * pi() / 180);
	
		$width = $sphererad * cos(deg2rad($j));
	
		$height = $width * sin(deg2rad($viewlat));
	
		$alt = $sphererad * sin(deg2rad($j));
		$altpersp = $alt * cos(deg2rad($viewlat));
	
		$xpos = 1800 + ($width * cos($ra_rad) * cos(deg2rad(0))) - ($width * sin($ra_rad) * sin(deg2rad(0)));
		$ypos = 900 - $altpersp + ($height * cos($ra_rad) * sin(deg2rad(0))) + ($height * sin($ra_rad) * cos(deg2rad(0)));
	
		$vislim = (atan(sin((($i - 180) * pi()) / 180) / tan(($viewlat * pi()) / 180)) * 2 * 90) / pi();
	
		if ($vislim > $j) {
			$xpos = 0;
			$ypos = 0;
		} else {
		}
	
	//normal projection
	} else {
		$star['dec'] *= -1;
		$star['ra'] = 360 - $star['ra'];
	
		$xpos = $star['ra'] * 10;
		$ypos = ($star['dec'] + 90) * 10;
	}

	//draw longitude lines
	for ($j = 0; $j < 180; $j += 15) {
		echo "ctx.strokeStyle = '#001133';";
		echo "ctx.beginPath();";

		for ($i = 0; $i <= 360; $i += 1) {
			$moo = ($i * pi() / 180);

			$rot = $j + .01;

			$height = $sphereradtrans * cos(deg2rad($viewlat));
			$width = $sphereradtrans * cos(deg2rad($rot));
			

			$boxtopperspheight = $sphererad * sin(deg2rad($viewlat));
			$nonperspheight = $sphereradtrans * sin(deg2rad($rot)) * 2;
			$perspheight = ($nonperspheight / ($sphererad * 2)) * $boxtopperspheight;
			$perspang = $perspheight / ($width);

			$xpos = 1800 + ($width * cos($moo) * cos(deg2rad(0))) - ($width * sin($moo) * sin(deg2rad(0)));
			$ypos = 900 + ($height * cos($moo) * sin(deg2rad(0))) + ($height * sin($moo) * cos(deg2rad(0))) + (($xpos - 1800) * $perspang);


			$vislim = -1 * (atan(sin((($j - 180) * pi()) / 180) / tan(($viewlat * pi()) / 180)) * 2 * 90) / pi();

			if ($i - 180 < $vislim && $i > $vislim) {
				echo "ctx.moveTo({$xpos}, {$ypos});";
			} else {
				echo "ctx.lineTo({$xpos}, {$ypos});";
			}
		}

		echo "ctx.stroke();";
	}


	//draw circle to surround skydome
	echo "ctx.strokeStyle = '#001133';";
	echo "ctx.beginPath();";

	for ($i = 0; $i <= 360; $i += 1) {
		$moo = ($i * pi() / 180);

		$height = 900;
		$width = 900;

		$xpos = 1800 + ($width * cos($moo) * cos(deg2rad(0))) - ($width * sin($moo) * sin(deg2rad(0)));
		$ypos = 900 + ($height * cos($moo) * sin(deg2rad(0))) + ($height * sin($moo) * cos(deg2rad(0)));

		echo "ctx.lineTo({$xpos}, {$ypos});";
	}

	echo "ctx.closePath();";
	echo "ctx.stroke();";

	//draw latitude lines
	for ($j = -90; $j < 90; $j += 15) {
		echo "ctx.strokeStyle = '#001133';";
		echo "ctx.beginPath();";

		for ($i = 0; $i <= 360; $i += 1) {
			$moo = ($i * pi() / 180);

			$width = $sphererad * cos(deg2rad($j));

			$height = $width * sin(deg2rad($viewlat));

			$alt = $sphereradtrans * sin(deg2rad($j));
			$altpersp = $alt * cos(deg2rad($viewlat));

			$xpos = 1800 + ($width * cos($moo) * cos(deg2rad(0))) - ($width * sin($moo) * sin(deg2rad(0)));
			$ypos = 900 - $altpersp + ($height * cos($moo) * sin(deg2rad(0))) + ($height * sin($moo) * cos(deg2rad(0)));


			$vislim = (atan(sin((($i - 180) * pi()) / 180) / tan(($viewlat * pi()) / 180)) * 2 * 90) / pi();

			if ($vislim > $j) {
				echo "ctx.moveTo({$xpos}, {$ypos});";
			} else {
				echo "ctx.lineTo({$xpos}, {$ypos});";
			}
		}

		echo "ctx.stroke();";
	}
}
?>