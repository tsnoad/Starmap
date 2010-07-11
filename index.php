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

		<div id="leftpanel" class="leftpanelouter leftpanelouter_expanded">
			<div class="leftpanelinner leftpanelinner_expanded">
				<div style="padding-top: 10px; text-align: center; color: white; font-family: Lucida Grande; font-size: 10pt; text-shadow: 2px 2px 0px black;">Navigation</div>
				<div style="width: 224px; height: 32px; margin: 8px auto 0px auto;">
					<div class="panelbutton panelbutton_first panelbutton_down panelbutton_downdouble panelbutton_double">
						<input type="button" value="" onmousedown="newadj(this, 'latadj', -10);" class="panelbutton_input" />
						<div class="panelbutton_pointer"></div>
					</div>
					<div class="panelbutton panelbutton_down">
						<input type="button" value="" onmousedown="newadj(this, 'latadj', -1);" class="panelbutton_input" />
						<div class="panelbutton_pointer"></div>
					</div>
					<div style="width: 64px; height: 32px; position: relative; float: left; margin-left: 8px; text-align: center; color: white; font-family: Lucida Grande; font-size: 8pt;">
						<div>lat</div>
						<div id="locdis_lat"></div>
					</div>
					<div class="panelbutton panelbutton_up">
						<input type="button" value="" onmousedown="newadj(this, 'latadj', 1);" class="panelbutton_input" />
						<div class="panelbutton_pointer"></div>
					</div>
					<div class="panelbutton panelbutton_up panelbutton_updouble panelbutton_double">
						<input type="button" value="" onmousedown="newadj(this, 'latadj', 10);" class="panelbutton_input" />
						<div class="panelbutton_pointer"></div>
					</div>
				</div>
				<div style="width: 224px; height: 32px; margin: 18px auto 0px auto;">
					<div class="panelbutton panelbutton_first panelbutton_left panelbutton_leftdouble panelbutton_double">
						<input type="button" value="" onmousedown="newadj(this, 'lonadj', -10);" class="panelbutton_input" />
						<div class="panelbutton_pointer"></div>
					</div>
					<div class="panelbutton panelbutton_left">
						<input type="button" value="" onmousedown="newadj(this, 'lonadj', -1);" class="panelbutton_input" />
						<div class="panelbutton_pointer"></div>
					</div>
					<div style="width: 64px; height: 32px; position: relative; float: left; margin-left: 8px; text-align: center; color: white; font-family: Lucida Grande; font-size: 8pt;">
						<div>lon</div>
						<div id="locdis_lon"></div>
					</div>
					<div class="panelbutton panelbutton_right">
						<input type="button" value="" onmousedown="newadj(this, 'lonadj', 1);" class="panelbutton_input" />
						<div class="panelbutton_pointer"></div>
					</div>
					<div class="panelbutton panelbutton_right panelbutton_rightdouble panelbutton_double">
						<input type="button" value="" onmousedown="newadj(this, 'lonadj', 10);" class="panelbutton_input" />
						<div class="panelbutton_pointer"></div>
					</div>
				</div>
		
				<div style="padding: 10px 10px 0px 10px;">
					<span onclick="latadj = 0; lonadj = 0; draw();" style="border-bottom: 1px dotted #666666; color: #999999; font-family: Lucida Grande; font-size: 8pt; cursor: pointer;">Reset Co-ordinates</span>
				</div>
		
				<hr style="margin: 8px 8px 0px 8px; border: 0px; border-top: 1px solid #999999; border-bottom: 2px solid black;" />
				<div style="padding-top: 10px; text-align: center; color: white; font-family: Lucida Grande; font-size: 8pt; text-shadow: 2px 2px 0px black;">Zoom</div>
				<div style="width: 152px; height: 32px; margin: 8px auto 0px auto;">
					<div class="panelbutton panelbutton_first panelbutton_zmout panelbutton_zmoutdouble panelbutton_double">
						<input type="button" value="" onmousedown="newadj(this, 'zmadj', -100);" class="panelbutton_input" />
						<div class="panelbutton_pointer"></div>
					</div>
					<div class="panelbutton panelbutton_zmout">
						<input type="button" value="" onmousedown="newadj(this, 'zmadj', -10);" class="panelbutton_input" />
						<div class="panelbutton_pointer"></div>
					</div>
					<div class="panelbutton panelbutton_zmin">
						<input type="button" value="" onmousedown="newadj(this, 'zmadj', 10);" class="panelbutton_input" />
						<div class="panelbutton_pointer"></div>
					</div>
					<div class="panelbutton panelbutton_zmin panelbutton_zmindouble panelbutton_double">
						<input type="button" value="" onmousedown="newadj(this, 'zmadj', 100);" class="panelbutton_input" />
						<div class="panelbutton_pointer"></div>
					</div>
				</div>
				<hr style="margin: 18px 8px 0px 8px; border: 0px; border-top: 1px solid #999999; border-bottom: 2px solid black;" />
		
				<div style="width: 224px; height: 32px; position: absolute; left: 8px; bottom: 12px;">
					<div class="panelbutton panelbutton_first panelbutton_pnllftminms panelbutton_double">
						<input type="button" value="" onclick="$('leftpanel').className='leftpanelouter leftpanelouter_collapsed'; repos_canvas();" class="panelbutton_input" />
					</div>
					<input type="button" value="Collapse Navigation" onclick="$('leftpanel').className='leftpanelouter leftpanelouter_collapsed'; repos_canvas();" style="width: 128px; height: 32px; float: left; margin-left: 8px; padding: 0px; border: 0px; background-color: transparent; color: white; font-family: Lucida Grande; font-size: 8pt; text-align: left; text-shadow: 1px 1px 0px black;" />
				</div>
			</div>
			<div class="leftpanelinner leftpanelinner_collapsed">
				<div style="width: 32px; height: 32px; position: absolute; left: 4px; bottom: 12px;">
					<div class="panelbutton panelbutton_first panelbutton_pnllftmaxms panelbutton_double">
						<input type="button" value="" onclick="$('leftpanel').className='leftpanelouter leftpanelouter_expanded'; repos_canvas();" class="panelbutton_input" />
					</div>
				</div>
			</div>
		</div>
		
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
					<div class="panelbutton panelbutton_first panelbutton_pnlrtminms panelbutton_double">
						<input type="button" value="" onclick="$('rightpanel').className='rightpanelouter rightpanelouter_collapsed'; repos_canvas();" class="panelbutton_input" />
					</div>
					<input type="button" value="Collapse Location" onclick="$('rightpanel').className='rightpanelouter rightpanelouter_collapsed'; repos_canvas();" style="width: 128px; height: 32px; float: right; margin-right: 8px; padding: 0px; border: 0px; background-color: transparent; color: white; font-family: Lucida Grande; font-size: 8pt; text-align: right; text-shadow: 1px 1px 0px black;" />
				</div>
			</div>
			<div class="rightpanelinner rightpanelinner_collapsed">
				<div style="width: 32px; height: 32px; position: absolute; left: 4px; bottom: 12px;">
					<div class="panelbutton panelbutton_first panelbutton_pnlrtmaxms panelbutton_double">
						<input type="button" value="" onclick="$('rightpanel').className='rightpanelouter rightpanelouter_expanded'; repos_canvas();" class="panelbutton_input" />
					</div>
				</div>
			</div>
		</div>

		<!-- Old date control panel: currently hidden -->
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