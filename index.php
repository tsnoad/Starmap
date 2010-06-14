<?php
	require("calculate_objects.php");
?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Starmap</title>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/prototype/1.6.1.0/prototype.js"></script>
		<script type="text/javascript" src="script.js"></script>

	    <script type="text/javascript">
			//JSON array of the stars we'll be showing
			var jsonstars = '[<?= $json_pass ?>]';
			//convert the list of stars from JSON to an object
			var stars = jsonstars.evalJSON();

			//JSON array of the planets, messier objects and bright stars
			var planets = '<?= json_encode($json_planets) ?>';
			//convert to object
			planets = planets.evalJSON();

/*
			//global variable where we'll keep the coordinates of the outline of the milky way
			var milkyway = new Array();
*/
	    </script>  
		<link href="style.css" rel="stylesheet" type="text/css" />
	</head>
	<body onload="init();">  
	    <canvas id="tutorial" width="3600" height="1800"></canvas>

		<div class="container container_latlon">
			<div class="container_background"></div>
			<div class="container_border"></div>

			<div class="container_lat_container">
				<span class="container_lat_minor_ctrls">&nbsp;
					<span class="container_lat_ctrl_label">lat</span>
					<input type="button" onmousedown="newadj(this, 'latadj', 1);" class="container_ctrl_up" value="+" />
					<input type="button" onmousedown="newadj(this, 'latadj', -1);" class="container_ctrl_down" value="-" />
				</span>
				<span class="container_lat_major_ctrls">&nbsp;
					<input type="button" onmousedown="newadj(this, 'latadj', 10);" class="container_ctrl_up" value="++" />
					<input type="button" onmousedown="newadj(this, 'latadj', -10);" class="container_ctrl_down" value="--" />
				</span>
				<input id="locdis_lat" type="text" value="-35" class="container_lat_disp" />
			</div>
			<div class="container_lon_container">
				<span class="container_lon_minor_ctrls">&nbsp;
					<span class="container_lon_ctrl_label">lon</span>
					<input type="button" onmousedown="newadj(this, 'lonadj', 1);" class="container_ctrl_up" value="+" />
					<input type="button" onmousedown="newadj(this, 'lonadj', -1);" class="container_ctrl_down" value="-" />
				</span>
				<span class="container_lon_major_ctrls">&nbsp;
					<input type="button" onmousedown="newadj(this, 'lonadj', 10);" class="container_ctrl_up" value="++" />
					<input type="button" onmousedown="newadj(this, 'lonadj', -10);" class="container_ctrl_down" value="--" />
				</span>
				<input id="locdis_lon" type="text" value="-149" class="container_lon_disp" />
			</div>
		</div>

		<div class="container container_date">
			<div class="container_background"></div>
			<div class="container_border"></div>

			<div class="container_year_container">
				<span class="container_year_ctrls">&nbsp;
					<span class="container_year_ctrl_sep">/</span>
					<input type="button" onmousedown="newadj(this, 'dateadj', 60 * 60 * 24 * 30 * 12);" class="container_ctrl_up" value="+" />
					<input type="button" onmousedown="newadj(this, 'dateadj', -60 * 60 * 24 * 30 * 12);" class="container_ctrl_down" value="-" />
				</span>
				<input id="datedis_year" type="text" value="" class="container_year_disp" />
			</div>
			<div class="container_month_container">
				<span class="container_month_ctrls">&nbsp;
					<span class="container_month_ctrl_sep">/</span>
					<input type="button" onmousedown="newadj(this, 'dateadj', 60 * 60 * 24 * 30);" class="container_ctrl_up" value="+" />
					<input type="button" onmousedown="newadj(this, 'dateadj', -60 * 60 * 24 * 30);" class="container_ctrl_down" value="-" />
				</span>
				<input id="datedis_month" type="text" value="" class="container_month_disp" />
			</div>
			<div class="container_day_container">
				<span class="container_day_ctrls">&nbsp;
					<input type="button" onmousedown="newadj(this, 'dateadj', 60 * 60 * 24);" class="container_ctrl_up" value="+" />
					<input type="button" onmousedown="newadj(this, 'dateadj', -60 * 60 * 24);" class="container_ctrl_down" value="-" />
				</span>
				<input id="datedis_day" type="text" value="" class="container_day_disp" />
			</div>

			<div class="container_hour_container">
				<span class="container_hour_ctrls">&nbsp;
					<span class="container_hour_ctrl_sep">:</span>
					<input type="button" onmousedown="newadj(this, 'dateadj', 60 * 60);" class="container_ctrl_up" value="+" />
					<input type="button" onmousedown="newadj(this, 'dateadj', -60 * 60);" class="container_ctrl_down" value="-" />
				</span>
				<input id="datedis_hour" type="text" value="" class="container_hour_disp" />
			</div>
			<div class="container_minute_container">
				<span class="container_minute_ctrls">&nbsp;
					<span class="container_minute_ctrl_sep">:</span>
					<input type="button" onmousedown="newadj(this, 'dateadj', 60);" class="container_ctrl_up" value="+" />
					<input type="button" onmousedown="newadj(this, 'dateadj', -60);" class="container_ctrl_down" value="-" />
				</span>
				<input id="datedis_min" type="text" value="" class="container_minute_disp" />
			</div>
			<div class="container_second_container">
				<input id="datedis_sec" type="text" value="" class="container_hour_disp" />
			</div>
		</div>


		<div class="container container_location">
			<div class="container_background"></div>
			<div class="container_border"></div>
		
			<div class="location_padding">
				<div id="location_current_container">
					<div class="location_current">
						<span class="location_current_label">Location: </span><span id="location_current_city" class="location_current_city">Canberra</span><span class="location_current_country">, <span id="location_current_country">Australia</span></span>
					</div>
			
					<div>
						<input type="button" class="location_current_change" onclick="location_change_init();" value="Change..." />
					</div>
				</div>
				<div id="location_change_container">
					<div class="location_change_label">Location:</div>
			
					<div style="padding: 5px 10px;">
						<input type="text" id="location_input" style="width: 254px;" />
					</div>
			
					<hr class="location_hr" />
			
					<div id="locations_list">
						<div class="location_option">type type type</div>
					</div>
				</div>
			</div>
		</div>
<!--
		
		<div class="container " style="width: 300px; left: 400px; top: 50px;">
			<div class="container_background"></div>
			<div class="container_border"></div>
		
			<div style="position: relative; padding: 15px 10px;">
				<div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;"><input type="checkbox" checked="true" disabled="true" /><span style="margin-left: 5px;">Show star labels</span></div>
				<div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;"><input type="checkbox" checked="true" disabled="true" /><span style="margin-left: 5px;">Show planet labels</span></div>
				<div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;"><input type="checkbox" checked="true" disabled="true" /><span style="margin-left: 5px;">Show nebula labels</span></div>
		
				<hr style="position: relative; margin: 15px 10px; border: 0px; border-top: 2px solid white; opacity: 0.15;" />
		
				<div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;"><input type="checkbox" checked="true" disabled="true" /><span style="margin-left: 5px;">Show grid</span></div>
				<div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;"><input type="checkbox" disabled="true" /><span style="margin-left: 5px;">Show horizon</span></div>
			</div>
		</div>
-->
	</body>
</html>