<?php
	require("calculcate_objects.php");
?>

<!DOCTYPE HTML>
<html>
	<head>
		<title></title>
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
	    <style type="text/css">
			html {
				margin: 0px;
			}
			body {
				margin: 0px; overflow: hidden; background-color: black;
			}
			canvas {
				position: absolute; left: 0px; top: 0px;
			}
	    </style>  
	</head>
	<body onload="init();">  
	    <canvas id="tutorial" width="3600" height="1800"></canvas>
<div>
		<div style="width: 155px; height: 60px; position: absolute; left: 50px; bottom: 20px; padding: 2px;">
			<div style="position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px; border: 2px solid black; background-color: black; -moz-border-radius: 10px; -webkit-border-radius: 10px; opacity: 0.75"></div>
			<div style="position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px; border: 2px solid white; background-color: transparent; -moz-border-radius: 10px; -webkit-border-radius: 10px; opacity: 0.15"></div>

			<div style="width: 70px; height: 20px; position: absolute; left: 5px; top: 20px; padding: 1px 0px; background-color: black;">
				<label style="position: relative; vertical-align: middle; font-family: Lucida Grande; font-size: 8pt;">&nbsp;
					<span style="width: 20px; position: absolute; left: 0px; top: 0px; color: #999999; text-align: center;">lat</span>
					<span onmousedown="newadj(this, 'latadj', 1);" style="width: 35px; position: absolute; left: 0px; top: -20px; color: white; text-align: right;">+</span>
					<span onmousedown="newadj(this, 'latadj', -1);" style="width: 35px; position: absolute; left: 0px; top: 20px; color: white; text-align: right;">-</span>
				</label>
				<label style="position: relative; vertical-align: middle; font-family: Lucida Grande; font-size: 6pt;">&nbsp;
					<span onmousedown="newadj(this, 'latadj', 10);" style="width: 35px; position: absolute; left: 35px; top: -20px; color: #999999; text-align: left;">++</span>
					<span onmousedown="newadj(this, 'latadj', -10);" style="width: 35px; position: absolute; left: 35px; top: 20px; color: #999999; text-align: left;">--</span>
				</label>
				<input id="locdis_lat" type="text" value="-35" style="width: 50px; height: 20px; position: absolute; left: 20px; top: 0px; border: 0px; background-color: black; color: white; text-align: center;" />
			</div>
			<div style="width: 70px; height: 20px; position: absolute; left: 80px; top: 20px; padding: 1px 0px; background-color: black;">
				<label style="position: relative; vertical-align: middle; font-family: Lucida Grande; font-size: 8pt;">&nbsp;
					<span style="width: 20px; position: absolute; left: 0px; top: 0px; color: #999999; text-align: center;">lon</span>
					<span onmousedown="newadj(this, 'lonadj', 1);" style="width: 35px; position: absolute; left: 0px; top: -20px; color: white; text-align: right;">+</span>
					<span onmousedown="newadj(this, 'lonadj', -1);" style="width: 35px; position: absolute; left: 0px; top: 20px; color: white; text-align: right;">-</span>
				</label>
				<label style="position: relative; vertical-align: middle; font-family: Lucida Grande; font-size: 6pt;">&nbsp;
					<span onmousedown="newadj(this, 'lonadj', 10);" style="width: 35px; position: absolute; left: 35px; top: -20px; color: #999999; text-align: left;">++</span>
					<span onmousedown="newadj(this, 'lonadj', -10);" style="width: 35px; position: absolute; left: 35px; top: 20px; color: #999999; text-align: left;">--</span>
				</label>
				<input id="locdis_lon" type="text" value="-149" style="width: 50px; height: 20px; position: absolute; left: 20px; top: 0px; border: 0px; background-color: black; color: white; text-align: center;" />
			</div>
		</div>

		<div style="width: 235px; height: 60px; position: absolute; left: 225px; bottom: 20px; padding: 2px;">
			<div style="position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px; border: 2px solid black; background-color: black; -moz-border-radius: 10px; -webkit-border-radius: 10px; opacity: 0.25"></div>
			<div style="position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px; border: 2px solid white; background-color: transparent; -moz-border-radius: 10px; -webkit-border-radius: 10px; opacity: 0.15"></div>

			<div style="width: 50px; height: 20px; position: absolute; left: 5px; top: 20px; padding: 1px 0px; background-color: black;">
				<label style="position: relative; font-family: Lucida Grande; font-size: 8pt;">&nbsp;
					<span style="width: 5px; position: absolute; left: 50px; top: 0px; color: white; text-align: center;">/</span>
					<span onmousedown="newadj(this, 'dateadj', 60 * 60 * 24 * 30 * 12);" style="width: 50px; position: absolute; left: 0px; top: -20px; color: white; text-align: center;">+</span>
					<span onmousedown="newadj(this, 'dateadj', -60 * 60 * 24 * 30 * 12);" style="width: 50px; position: absolute; left: 0px; top: 20px; color: white; text-align: center;">-</span>
				</label>
				<input id="datedis_year" type="text" value="" style="width: 50px; height: 20px; position: absolute; left: 0px; top: 0px; border: 0px; background-color: #111111; color: white; text-align: center;" />
			</div>
			<div style="width: 30px; height: 20px; position: absolute; left: 60px; top: 20px; padding: 1px 0px; background-color: black;">
				<label style="position: relative; font-family: Lucida Grande; font-size: 8pt;">&nbsp;
					<span style="width: 5px; position: absolute; left: 30px; top: 0px; color: white; text-align: center;">/</span>
					<span onmousedown="newadj(this, 'dateadj', 60 * 60 * 24 * 30);" style="width: 30px; position: absolute; left: 0px; top: -20px; color: white; text-align: center;">+</span>
					<span onmousedown="newadj(this, 'dateadj', -60 * 60 * 24 * 30);" style="width: 30px; position: absolute; left: 0px; top: 20px; color: white; text-align: center;">-</span>
				</label>
				<input id="datedis_month" type="text" value="" style="width: 30px; height: 20px; position: absolute; left: 0px; top: 0px; border: 0px; background-color: #111111; color: white; text-align: center;" />
			</div>
			<div style="width: 30px; height: 20px; position: absolute; left: 95px; top: 20px; padding: 1px 0px; background-color: black;">
				<label style="position: relative; font-family: Lucida Grande; font-size: 8pt;">&nbsp;
					<span onmousedown="newadj(this, 'dateadj', 60 * 60 * 24);" style="width: 30px; position: absolute; left: 0px; top: -20px; color: white; text-align: center;">+</span>
					<span onmousedown="newadj(this, 'dateadj', -60 * 60 * 24);" style="width: 30px; position: absolute; left: 0px; top: 20px; color: white; text-align: center;">-</span>
				</label>
				<input id="datedis_day" type="text" value="" style="width: 30px; height: 20px; position: absolute; left: 0px; top: 0px; border: 0px; background-color: #111111; color: white; text-align: center;" />
			</div>

			<div style="width: 30px; height: 20px; position: absolute; left: 130px; top: 20px; padding: 1px 0px; background-color: black;">
				<label style="position: relative; font-family: Lucida Grande; font-size: 8pt;">&nbsp;
					<span style="width: 5px; position: absolute; left: 30px; top: 0px; color: white; text-align: center;">:</span>
					<span onmousedown="newadj(this, 'dateadj', 60 * 60);" style="width: 30px; position: absolute; left: 0px; top: -20px; color: white; text-align: center;">+</span>
					<span onmousedown="newadj(this, 'dateadj', -60 * 60);" style="width: 30px; position: absolute; left: 0px; top: 20px; color: white; text-align: center;">-</span>
				</label>
				<input id="datedis_hour" type="text" value="" style="width: 30px; height: 20px; position: absolute; left: 0px; top: 0px; border: 0px; background-color: #111111; color: white; text-align: center;" />
			</div>
			<div style="width: 30px; height: 20px; position: absolute; left: 165px; top: 20px; padding: 1px 0px; background-color: black;">
				<label style="position: relative; font-family: Lucida Grande; font-size: 8pt;">&nbsp;
					<span style="width: 5px; position: absolute; left: 30px; top: 0px; color: white; text-align: center;">:</span>
					<span onmousedown="newadj(this, 'dateadj', 60);" style="width: 30px; position: absolute; left: 0px; top: -20px; color: white; text-align: center;">+</span>
					<span onmousedown="newadj(this, 'dateadj', -60);" style="width: 30px; position: absolute; left: 0px; top: 20px; color: white; text-align: center;">-</span>
				</label>
				<input id="datedis_min" type="text" value="" style="width: 30px; height: 20px; position: absolute; left: 0px; top: 0px; border: 0px; background-color: #111111; color: white; text-align: center;" />
			</div>
			<div style="width: 30px; height: 20px; position: absolute; left: 200px; top: 20px; padding: 1px 0px; background-color: black;">
				<input id="datedis_sec" type="text" value="" style="width: 30px; height: 20px; position: absolute; left: 0px; top: 0px; border: 0px; background-color: #111111; color: white; text-align: center;" />
			</div>
		</div>

<!--
		<div style="position: absolute; left: 50%; top: 50%;">
			<div style="width: 200px; height: 80px; position: absolute; left: -100px; top: -40px;">
				<input type="button" onmousedown="newadj(this, 'obslat', 1);" value="&nbsp;" style="width: 40px; height: 40px; position: absolute; left: 40px; top: 0px; border: 0px; background-color: transparent; color: white; font-size: 12pt; vertical-align: middle;" />
				<input type="button" onmousedown="newadj(this, 'obslat', -1);" value="&nbsp;" style="width: 40px; height: 40px; position: absolute; left: 40px; top: 40px; border: 0px; background-color: transparent; color: white; font-size: 12pt; vertical-align: middle;" />

				<input type="button" onmousedown="newadj(this, 'zmadj', 100);" value="+" style="width: 40px; height: 40px; position: absolute; left: 160px; top: 0px; border: 0px; background-color: transparent; color: white; font-size: 12pt; vertical-align: middle;" />
				<input type="button" onmousedown="newadj(this, 'zmadj', -100);" value="-" style="width: 40px; height: 40px; position: absolute; left: 160px; top: 40px; border: 0px; background-color: transparent; color: white; font-size: 12pt; vertical-align: middle;" />
			</div>
		</div>
-->
</div>


<div style="width: 300px; position: absolute; left: 50px; top: 20px;">
	<div style="position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px; border: 2px solid black; background-color: black; -moz-border-radius: 10px; -webkit-border-radius: 10px; opacity: 0.75;"></div>
	<div style="position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px; border: 2px solid white; background-color: transparent; -moz-border-radius: 10px; -webkit-border-radius: 10px; opacity: 0.15;"></div>


<style>
	.location_option {
		padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;
	}
	.location_option_hover {
		padding: 5px 10px; background-color: #666666; color: white; font-family: Lucida Grande; font-size: 8pt; text-shadow: #333333 -0.5px -1px 0.75px; -moz-border-radius: 5px; -webkit-border-radius: 5px;
	}
</style>

	<div style="position: relative; padding: 15px 10px;">
		<div id="location_current">
			<div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;"><span style="color: #999999;">Location: </span><span style="font-size: 9pt;">Canberra</span><span style="color: #333333; font-size: 9pt;">, Australia</span></div>
	
			<div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;" onclick="location_change_init();">Change...</div>
		</div>
		<div id="location_change" style="display: none;">
			<div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 9pt;">Location:</div>
	
			<div style="padding: 5px 10px;"><input type="text" id="location_input" style="width: 254px;" /></div>
	
			<hr style="position: relative; margin: 15px 10px; border: 0px; border-top: 2px solid white; opacity: 0.15;" />
	
			<div id="locations_list">
				<div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;">type type type</div>
			</div>
		</div>
	</div>
</div>
<!--

<div style="width: 300px; position: absolute; left: 400px; top: 50px;">
	<div style="position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px; border: 2px solid black; background-color: black; -moz-border-radius: 10px; -webkit-border-radius: 10px; opacity: 0.75;"></div>
	<div style="position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px; border: 2px solid white; background-color: transparent; -moz-border-radius: 10px; -webkit-border-radius: 10px; opacity: 0.15;"></div>

	<div style="position: relative; padding: 15px 10px;">
		<div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;"><input type="checkbox" checked="true" disabled="true" /><span style="margin-left: 5px;">Show star labels</span></div>
		<div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;"><input type="checkbox" checked="true" disabled="true" /><span style="margin-left: 5px;">Show planet labels</span></div>
		<div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;"><input type="checkbox" checked="true" disabled="true" /><span style="margin-left: 5px;">Show nebula labels</span></div>

		<hr style="position: relative; margin: 15px 10px; border: 0px; border-top: 2px solid white; opacity: 0.15;" />

		<div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;"><input type="checkbox" checked="true" disabled="true" /><span style="margin-left: 5px;">Show grid</span></div>
		<div style="padding: 5px 10px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt;"><input type="checkbox" disabled="true" /><span style="margin-left: 5px;">Show horizon</span></div>
	</div>
</div>

<div style="width: 200px; height: 180px; position: absolute; left: 750px; top: 50px;">
	<div style="position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px; border: 2px solid black; background-color: black; -moz-border-radius: 10px; -webkit-border-radius: 10px; opacity: 0.75;"></div>
	<div style="position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px; border: 2px solid white; background-color: transparent; -moz-border-radius: 10px; -webkit-border-radius: 10px; opacity: 0.15;"></div>

	<div style="width: 40px; position: absolute; left: 20px; top: 15px;">
		<div style="color: #cccccc; font-family: Lucida Grande; font-size: 8pt; text-align: center;">Dec.</div>
	</div>

	<div style="width: 40px; position: absolute; left: 80px; top: 15px;">
		<div style="color: #cccccc; font-family: Lucida Grande; font-size: 8pt; text-align: center;">R.A.</div>
	</div>

	<div style="width: 40px; position: absolute; left: 140px; top: 15px;">
		<div style="color: #cccccc; font-family: Lucida Grande; font-size: 8pt; text-align: center;">Zoom</div>
	</div>

	<div style="width: 40px; height: 80px; position: absolute; left: 20px; top: 40px;">
		<div style="position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px; border: 2px solid white; background-color: transparent; opacity: 0.15;"></div>

		<hr style="position: relative; margin: 40px 10px; border: 0px; border-top: 2px solid white; opacity: 0.15;" />

		<input type="button" value="+" onmousedown="newadj(this, 'latadj', 1);" style="width: 40px; height: 40px; position: absolute; left: 0px; top: 0px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: white; font-size: 16pt; font-weight: bold;" />
		<input type="button" value="-" onmousedown="newadj(this, 'latadj', -1);" style="width: 40px; height: 40px; position: absolute; left: 0px; top: 40px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: white; font-size: 16pt; font-weight: bold;" />
	</div>

	<div style="width: 40px; height: 120px; position: absolute; left: 80px; top: 40px;">
		<div style="position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px; border: 2px solid white; background-color: transparent; opacity: 0.15;"></div>

		<hr style="position: relative; margin: 20px 10px 0px 10px; border: 0px; border-top: 2px solid white; opacity: 0.15;" />
		<hr style="position: relative; margin: 40px 10px 0px 10px; border: 0px; border-top: 2px solid white; opacity: 0.15;" />
		<hr style="position: relative; margin: 40px 10px 0px 10px; border: 0px; border-top: 2px solid white; opacity: 0.15;" />

		<input type="button" value="++" onmousedown="newadj(this, 'lonadj', 10);" style="width: 40px; height: 20px; position: absolute; left: 0px; top: 0px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: #999999; font-size: 10pt; font-weight: bold;" />
		<input type="button" value="+" onmousedown="newadj(this, 'lonadj', 1);" style="width: 40px; height: 40px; position: absolute; left: 0px; top: 20px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: white; font-size: 16pt; font-weight: bold;" />
		<input type="button" value="-" onmousedown="newadj(this, 'lonadj', -1);" style="width: 40px; height: 40px; position: absolute; left: 0px; top: 60px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: white; font-size: 16pt; font-weight: bold;" />
		<input type="button" value="--" onmousedown="newadj(this, 'lonadj', -10);" style="width: 40px; height: 20px; position: absolute; left: 0px; top: 100px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: #999999; font-size: 10pt; font-weight: bold;" />
	</div>

	<div style="width: 40px; height: 80px; position: absolute; left: 140px; top: 40px;">
		<div style="position: absolute; left: 0px; right: 0px; top: 0px; bottom: 0px; border: 2px solid white; background-color: transparent; opacity: 0.15;"></div>

		<hr style="position: relative; margin: 40px 10px; border: 0px; border-top: 2px solid white; opacity: 0.15;" />

		<input type="button" value="+" onmousedown="newadj(this, 'zmadj', 10);" style="width: 40px; height: 40px; position: absolute; left: 0px; top: 0px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: white; font-size: 16pt; font-weight: bold;" />
		<input type="button" value="-" onmousedown="newadj(this, 'zmadj', -10);" style="width: 40px; height: 40px; position: absolute; left: 0px; top: 40px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: white; font-size: 16pt; font-weight: bold;" />
	</div>
-->

	</body>
</html>