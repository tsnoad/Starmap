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

<style>
	.leftpanelouter {
		width: 250px; height: 100%; position: absolute; left: 0px; top: 0px; background-color: #2e3436; background: -moz-linear-gradient(right, #2e3436, #192424);
	}
		.leftpanelouter_expanded {
			width: 250px;
		}
		.leftpanelouter_collapsed {
			width: 42px;
		}
		.leftpanelinner {
			width: 240px; height: 480px; position: absolute; left: 0px; top: 50%; margin-top: -240px; background-color: #111717; background: -moz-linear-gradient(top, #000909, #111717 50px);
		}
			.leftpanelinner_expanded {
				width: 240px;
			}
			.leftpanelinner_collapsed {
				width: 40px;
			}
				.leftpanelouter_expanded .leftpanelinner_collapsed {
					display: none;
				}
				.leftpanelouter_collapsed .leftpanelinner_expanded {
					display: none;
				}
</style>

<div id="leftpanel" class="leftpanelouter leftpanelouter_expanded">
	<div class="leftpanelinner leftpanelinner_expanded">
		<div style="padding-top: 10px; text-align: center; color: white; font-family: Lucida Grande; font-size: 10pt; text-shadow: 2px 2px 0px black;">Navigation</div>
		<div style="width: 224px; height: 32px; margin: 8px auto 0px auto;">
			<div style="width: 32px; height: 32px; position: relative; float: left; background-color: #666666; background: -moz-linear-gradient(top, #666666, #444444); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="&#x2193;" onmousedown="newadj(this, 'latadj', -10);" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 0px 1px 0px #666666;" />
				<div style="width: 0px; height: 0px; position: absolute; left: 10px; top: 32px; background-color: transparent; border: 6px solid transparent; border-top: 6px solid #444444;"></div>
			</div>
			<div style="width: 32px; height: 32px; position: relative; float: left; margin-left: 8px; background-color: #cccccc; background: -moz-linear-gradient(top, #cccccc, #888888); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="&#x2193;" onmousedown="newadj(this, 'latadj', -1);" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 0px 1px 0px #cccccc;" />
				<div style="width: 0px; height: 0px; position: absolute; left: 10px; top: 32px; background-color: transparent; border: 6px solid transparent; border-top: 6px solid #888888;"></div>
			</div>
			<div style="width: 64px; height: 32px; position: relative; float: left; margin-left: 8px; text-align: center; color: white; font-family: Lucida Grande; font-size: 8pt;">
				<div>lat</div>
				<div id="locdis_lat"></div>
			</div>
			<div style="width: 32px; height: 32px; position: relative; float: left; margin-left: 8px; background-color: #cccccc; background: -moz-linear-gradient(top, #cccccc, #888888); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="&#x2191;" onmousedown="newadj(this, 'latadj', 1);" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 0px 1px 0px #cccccc;" />
				<div style="width: 0px; height: 0px; position: absolute; left: 10px; bottom: 32px; background-color: transparent; border: 6px solid transparent; border-bottom: 6px solid #cccccc;"></div>
			</div>
			<div style="width: 32px; height: 32px; position: relative; float: left; margin-left: 8px; background-color: #666666; background: -moz-linear-gradient(top, #666666, #444444); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="&#x2191;" onmousedown="newadj(this, 'latadj', 10);" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 0px 1px 0px #666666;" />
				<div style="width: 0px; height: 0px; position: absolute; left: 10px; bottom: 32px; background-color: transparent; border: 6px solid transparent; border-bottom: 6px solid #666666;"></div>
			</div>
		</div>
		<div style="width: 224px; height: 32px; margin: 18px auto 0px auto;">
			<div style="width: 32px; height: 32px; position: relative; float: left; background-color: #666666; background: -moz-linear-gradient(top, #666666, #444444); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="&#x2190;" onmousedown="newadj(this, 'lonadj', -10);" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 0px 1px 0px #666666;" />
				<div style="width: 0px; height: 0px; position: absolute; right: 32px; top: 10px; background-color: transparent; border: 6px solid transparent; border-right: 6px solid #555555;"></div>
			</div>
			<div style="width: 32px; height: 32px; position: relative; float: left; margin-left: 8px; background-color: #cccccc; background: -moz-linear-gradient(top, #cccccc, #888888); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="&#x2190;" onmousedown="newadj(this, 'lonadj', -1);" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 0px 1px 0px #cccccc;" />
				<div style="width: 0px; height: 0px; position: absolute; right: 32px; top: 10px; background-color: transparent; border: 6px solid transparent; border-right: 6px solid #aaaaaa;"></div>
			</div>
			<div style="width: 64px; height: 32px; position: relative; float: left; margin-left: 8px; text-align: center; color: white; font-family: Lucida Grande; font-size: 8pt;">
				<div>lon</div>
				<div id="locdis_lon"></div>
			</div>
			<div style="width: 32px; height: 32px; position: relative; float: left; margin-left: 8px; background-color: #cccccc; background: -moz-linear-gradient(top, #cccccc, #888888); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="&#x2192;" onmousedown="newadj(this, 'lonadj', 1);" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 0px 1px 0px #cccccc;" />
				<div style="width: 0px; height: 0px; position: absolute; left: 32px; top: 10px; background-color: transparent; border: 6px solid transparent; border-left: 6px solid #aaaaaa;"></div>
			</div>
			<div style="width: 32px; height: 32px; position: relative; float: left; margin-left: 8px; background-color: #666666; background: -moz-linear-gradient(top, #666666, #444444); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="&#x2192;" onmousedown="newadj(this, 'lonadj', 10);" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 0px 1px 0px #666666;" />
				<div style="width: 0px; height: 0px; position: absolute; left: 32px; top: 10px; background-color: transparent; border: 6px solid transparent; border-left: 6px solid #555555;"></div>
			</div>
		</div>

		<div style="padding: 10px 10px 0px 10px;">
			<span onclick="latadj = 0; lonadj = 0; draw();" style="border-bottom: 1px dotted #666666; color: #999999; font-family: Lucida Grande; font-size: 8pt; cursor: pointer;">Reset Co-ordinates</span>
		</div>

		<hr style="margin: 8px 8px 0px 8px; border: 0px; border-top: 1px solid #999999; border-bottom: 2px solid black;" />
		<div style="padding-top: 10px; text-align: center; color: white; font-family: Lucida Grande; font-size: 8pt; text-shadow: 2px 2px 0px black;">Zoom</div>
		<div style="width: 152px; height: 32px; margin: 8px auto 0px auto;">
			<div style="width: 32px; height: 32px; position: relative; float: left; background-color: #666666; background: -moz-linear-gradient(top, #666666, #444444); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="&#x2193;" onmousedown="newadj(this, 'zmadj', -100);" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 0px 1px 0px #666666;" />
				<div style="width: 0px; height: 0px; position: absolute; left: 10px; top: 32px; background-color: transparent; border: 6px solid transparent; border-top: 6px solid #444444;"></div>
			</div>
			<div style="width: 32px; height: 32px; position: relative; float: left; margin-left: 8px; background-color: #cccccc; background: -moz-linear-gradient(top, #cccccc, #888888); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="&#x2193;" onmousedown="newadj(this, 'zmadj', -10);" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 0px 1px 0px #cccccc;" />
				<div style="width: 0px; height: 0px; position: absolute; left: 10px; top: 32px; background-color: transparent; border: 6px solid transparent; border-top: 6px solid #888888;"></div>
			</div>
			<div style="width: 32px; height: 32px; position: relative; float: left; margin-left: 8px; background-color: #cccccc; background: -moz-linear-gradient(top, #cccccc, #888888); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="&#x2191;" onmousedown="newadj(this, 'zmadj', 10);" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 0px 1px 0px #cccccc;" />
				<div style="width: 0px; height: 0px; position: absolute; left: 10px; bottom: 32px; background-color: transparent; border: 6px solid transparent; border-bottom: 6px solid #cccccc;"></div>
			</div>
			<div style="width: 32px; height: 32px; position: relative; float: left; margin-left: 8px; background-color: #666666; background: -moz-linear-gradient(top, #666666, #444444); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="&#x2191;" onmousedown="newadj(this, 'zmadj', 100);" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 0px 1px 0px #666666;" />
				<div style="width: 0px; height: 0px; position: absolute; left: 10px; bottom: 32px; background-color: transparent; border: 6px solid transparent; border-bottom: 6px solid #666666;"></div>
			</div>
		</div>
		<hr style="margin: 18px 8px 0px 8px; border: 0px; border-top: 1px solid #999999; border-bottom: 2px solid black;" />

		<div style="width: 224px; height: 32px; position: absolute; left: 8px; bottom: 12px;">
			<div style="width: 32px; height: 32px; position: relative; float: left; background-color: #cccccc; background: -moz-linear-gradient(top, #666666, #444444); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="" onclick="$('leftpanel').className='leftpanelouter leftpanelouter_collapsed'; repos_canvas();" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 1px 1px 0px #666666;" />
			</div>
			<input type="button" value="Collapse Navigation" onclick="$('leftpanel').className='leftpanelouter leftpanelouter_collapsed'; repos_canvas();" style="width: 128px; height: 32px; float: left; margin-left: 8px; padding: 0px; border: 0px; background-color: transparent; color: white; font-family: Lucida Grande; font-size: 8pt; text-align: left; text-shadow: 1px 1px 0px black;" />
		</div>
	</div>
	<div class="leftpanelinner leftpanelinner_collapsed">
		<div style="width: 32px; height: 32px; position: absolute; left: 4px; bottom: 12px;">
			<div style="width: 32px; height: 32px; position: relative; background-color: #cccccc; background: -moz-linear-gradient(top, #666666, #444444); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="" onclick="$('leftpanel').className='leftpanelouter leftpanelouter_expanded'; repos_canvas();" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 1px 1px 0px #666666;" />
			</div>
		</div>
	</div>
</div>

<style>
	.rightpanelouter {
		width: 250px; height: 100%; position: absolute; right: 0px; top: 0px; background-color: #2e3436; background: -moz-linear-gradient(left, #2e3436, #192424);
	}
		.rightpanelouter_expanded {
			width: 250px;
		}
		.rightpanelouter_collapsed {
			width: 42px;
		}
		.rightpanelinner {
			width: 240px; height: 480px; position: absolute; right: 0px; top: 50%; margin-top: -240px; background-color: #111717; background: -moz-linear-gradient(top, #000909, #111717 50px);
		}
			.rightpanelinner_expanded {
				width: 240px;
			}
			.rightpanelinner_collapsed {
				width: 40px;
			}
				.rightpanelouter_expanded .rightpanelinner_collapsed {
					display: none;
				}
				.rightpanelouter_collapsed .rightpanelinner_expanded {
					display: none;
				}
</style>

<div id="rightpanel" class="rightpanelouter rightpanelouter_collapsed">
	<div class="rightpanelinner rightpanelinner_expanded">
		<div style="padding-top: 10px; text-align: center; color: #999999; font-family: Lucida Grande; font-size: 10pt; text-shadow: 2px 2px 0px black;">Location</div>

		<div id="location_current_container">
			<div style="padding: 10px 10px 0px 10px; color: white; font-family: Lucida Grande; font-size: 10pt; text-shadow: 2px 2px 0px black;">
				<span id="location_current_city">Canberra</span><br /><span id="location_current_country" style="font-size: 8pt;">Australia</span>
			</div>

			<div style="padding: 10px 10px 0px 10px;">
				<span onclick="location_change_init();" style="border-bottom: 1px dotted #666666; color: #999999; font-family: Lucida Grande; font-size: 8pt; cursor: pointer;">Change</span>
			</div>
		</div>
		<div id="location_change_container" style="color: white;">
			<div style="padding: 10px 10px 0px 10px;">
				<input type="text" id="location_input" style="" />
			</div>
	
			<div id="locations_list" style="padding: 10px 5px 0px 10px;">
				type type type
			</div>
		</div>

		<hr style="margin: 18px 8px 0px 8px; border: 0px; border-top: 1px solid #999999; border-bottom: 2px solid black;" />

		<div style="padding: 10px 10px 0px 10px;">
			<div style="padding: 2px 0px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt; text-shadow: 1px 1px 0px black;"><input id="option_show_labels" type="checkbox" checked="true" onchange="draw();" /><label for="option_show_labels" style="margin-left: 5px;">Show labels</label></div>
			<div style="padding: 2px 0px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt; text-shadow: 1px 1px 0px black;"><input id="option_show_grid" type="checkbox" checked="true" onchange="draw();" /><label for="option_show_grid" style="margin-left: 5px;">Show grid</label></div>
			<div style="padding: 2px 0px; color: #cccccc; font-family: Lucida Grande; font-size: 8pt; text-shadow: 1px 1px 0px black;"><input id="option_show_ecliptic" type="checkbox" checked="true" onchange="draw();" /><label for="option_show_ecliptic" style="margin-left: 5px;">Show ecliptic</label></div>
		</div>

		<hr style="margin: 10px 8px 0px 8px; border: 0px; border-top: 1px solid #999999; border-bottom: 2px solid black;" />

		<div style="width: 224px; height: 32px; position: absolute; left: 8px; bottom: 12px;">
			<div style="width: 32px; height: 32px; position: relative; float: right; margin-left: 8px; background-color: #cccccc; background: -moz-linear-gradient(top, #666666, #444444); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="" onclick="$('rightpanel').className='rightpanelouter rightpanelouter_collapsed'; repos_canvas();" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 1px 1px 0px #666666;" />
			</div>
			<input type="button" value="Collapse Location" onclick="$('rightpanel').className='rightpanelouter rightpanelouter_collapsed'; repos_canvas();" style="width: 128px; height: 32px; float: right; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: white; font-family: Lucida Grande; font-size: 8pt; text-align: right; text-shadow: 1px 1px 0px black;" />
		</div>
	</div>
	<div class="rightpanelinner rightpanelinner_collapsed">
		<div style="width: 32px; height: 32px; position: absolute; left: 4px; bottom: 12px;">
			<div style="width: 32px; height: 32px; position: relative; background-color: #cccccc; background: -moz-linear-gradient(top, #666666, #444444); -moz-border-radius: 5px; border-bottom: 2px solid black;">
				<input type="button" value="" onclick="$('rightpanel').className='rightpanelouter rightpanelouter_expanded'; repos_canvas();" style="width: 32px; height: 32px; margin: 0px; padding: 0px; border: 0px; background-color: transparent; color: black; font-family: Lucida Grande; font-size: 9pt; font-weight: bold; text-shadow: 1px 1px 0px #666666;" />
			</div>
		</div>
	</div>
</div>


		<div style="display: none;">
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
		</div>
	</body>
</html>