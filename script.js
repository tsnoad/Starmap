//Variables that need greater scope

//latitude and longitude of the observer
var obslat = -35;
var obslon = 149;

//latitude offset as adjusted with the controls
var latadj = 0;
//longitude offset as adjusted with the controls
var lonadj = 0;
//zoom as adjusted with the controls
var zmadj = 450;
//roatation as adjusted with the controls
var rotadj = 0;

//right ascencion offset as set by the dateadj() function, that updates sidereal time
var raadj = 0;

//date offset as adjusted with the clock controls
var dateadj = 0;

//keep track of rotation state past the equator
var flipped = false;

//keep track of how much the page has been rotated by
var rotadj_actual = 0;


/*
 * Convert degrees to radians
 */
function deg2rad(deg) {
	return (deg * Math.PI) / 180;
}

/*
 * Start putting the page together
 */
function init() {
	//set the clock
	adjdatedis();

	//center the starmap on screen
	repos_canvas();

	//start keeping track of sidereal time
	rottime_repeat();

	//draw the starmap
	draw();

	//be sure to re-center the starmap if the browser is resized
	window.onresize = function () {
		repos_canvas();
	}
}

/*
 * Update the sidereal time to match actual time
 * Sidereal time is the measurement of how much the stardome has rotated for a given time of day
 */
function rottime() {
	//See http://web.archive.org/web/20070707051859/http://aa.usno.navy.mil/faq/docs/GAST.html
	//for in-depth explanation of sidereal time
	var giraffe = new Date();
	//seconds since the unix epoch
	var unix_seconds = giraffe.getTime() / 1000;
	//add the date offset
	unix_seconds += dateadj;
	//days since the unix epoch for the offset date
	var unix_days = unix_seconds / 86400;
	//number of days since the start of the julian calendar
	var jd_now = unix_days + 2440587.5;
	//number of days since the start of the julian j2000 epoch
	var j2000_now = jd_now - 2451545.0;
	//grenich mean sidereal time: hours since the start of the julian j2000 epoch
	var gmst = 18.697374558 + (24.06570982441908 * j2000_now);
	//hours since the start of the sidereal day
	var gmst_hrs = gmst % 24;
	//hours expressed in degrees
	var gmst_deg = gmst_hrs / 24 * 360;

	//store sidereal time in raadj
	raadj = gmst_deg;
}

/*
 * Call rottime every second
 */
function rottime_repeat() {
	//perform the sidereal time calculations
	rottime();

	//redraw the starmap
	draw();

	//rerun every second
	setTimeout("rottime_repeat();", 1000);
}

/*
 * Center the stardome on the screen.
 * This is used on load and on resize
 */
function repos_canvas() {
	//find the canvas element
	var canvas = document.getElementById('tutorial');
	//center horizontally
	canvas.style.left = ((self.innerWidth - 3600) / 2) + "px";
	//center vertically
	canvas.style.top = (((self.innerHeight - 1800) / 2) + 0) + "px";
}


/*
 * When an adjust button is pressed
 * Start doing things so the button can be held down
 */
function newadj(o, adjustme, adjustamount) {
	//create an object in which we can store stuff
	var adj_timeout_obj = new Object;

	//store the object of the button that was pressed
	adj_timeout_obj.button = o;
	//store the name of the variable that will be changed
	adj_timeout_obj.adjustme = adjustme;
	//store the increment by which the variable will be adjusted
	adj_timeout_obj.adjustamount = adjustamount;
	//store a boolean saying wheather the button has been held down long enough to start repeating
	//to start off this is false, but will be set true after the button has been held down for half a second
	adj_timeout_obj.repeated = false;

	//wait half a second then start repeating the adjustment
	//store the pointer for the timeout in the object, so that we can cancel the timeout
	adj_timeout_obj.adj_timeout = setTimeout(function () {
		newadj_repeatactuate(adj_timeout_obj);
	}, 500);

	//cancel on mouseup
	o.onmouseup = function () {
		newadj_cancel(adj_timeout_obj);
	}
	//and on mouseout
	o.onmouseout = function () {
		newadj_cancel(adj_timeout_obj);
	}
}
			
/*
 * After the button has been held down the adjustment should start to repeat in an animated fashion
 */
function newadj_repeatactuate(adj_timeout_obj) {
	//set so we know that animation has started
	//so we know what to do when the adjustment is cancelled
	adj_timeout_obj.repeated = true;

	//perform the adjustment and redraw the stardome
	newadj_actuate(adj_timeout_obj);

	//repeat the adjustment ten times every second
	adj_timeout_obj.adj_timeout = setTimeout(function () {
		newadj_repeatactuate(adj_timeout_obj);
	}, 100);
}
			
/*
 * Perform the adjustment, and redraw the skydome
 */
function newadj_actuate(adj_timeout_obj) {
	//add the increment to the specified variable
	window[adj_timeout_obj.adjustme] += adj_timeout_obj.adjustamount;

	//update the latitude and longitude displays
	$('locdis_lat').value = latadj + obslat;
	$('locdis_lon').value = lonadj + obslon;

	//recalculate sidereal time
	rottime();

	//redraw the skydome
	draw();
}
			
/*
 * Cancel the adjustment. Behaves depending on wheather animation has begun or not
 */
function newadj_cancel(adj_timeout_obj) {
	//clear the animation timeout to stop anything new from happening
	clearTimeout(adj_timeout_obj.adj_timeout);

	//if animation has not begun...
	if (!adj_timeout_obj.repeated) {
		//... then simply perform the adjustment as if it was a normal button click
		newadj_actuate(adj_timeout_obj);
	}

	//clear the onmouseup and onmousedown events
	adj_timeout_obj.button.onmouseup = function () {
		return false;
	}
	adj_timeout_obj.button.onmouseout = function () {
		return false;
	}
}

/*
 * Keep the clock display up to date
 */
function adjdatedis() {
	//create a date object with the current date
	var curdate = new Date();

	//create a date element with the current date PLUS the date offset
	var specdate = new Date(curdate.getTime() + (dateadj * 1000));

	//update year, month, day, hour, etc. clock display
	$('datedis_year').value = specdate.getFullYear();
	$('datedis_month').value = specdate.getMonth() + 1;
	$('datedis_day').value = specdate.getDate();
	
	$('datedis_hour').value = specdate.getHours();
	$('datedis_min').value = specdate.getMinutes();
	$('datedis_sec').value = specdate.getSeconds();
	
	//update again in 0.1 seconds
	setTimeout("adjdatedis();", 100);
}

/*
 * Start the location change process
 * called when someone clicks on the change location button
 */
function location_change_init() {
	//clear the old location options
	$('location_input').value = "";
	location_change_list();

	//show the new location input
	$('location_change_container').style.display = "block";
	//hide the current location
	$('location_current_container').style.display = "none";

	//focus the location input, so the user can start typing
	$('location_input').focus();

	//after something has been typed in the location input
	//get a list of possible locations using ajax
	$('location_input').onkeyup = location_change_list;
}

/*
 * Get possible location options that match what the user has typed
 */
function location_change_list() {
	new Ajax.Updater('locations_list', 'list_locations_ajax.php', {
		method: 'post',
		parameters: {location: $('location_input').value}
	});
}

/*
 * Set the current location
 * called when the user clicks on one of the location options
 */
function location_change(location_name, location_country, location_lat, location_lon) {
	//show the current location
	$('location_current_container').style.display = "block";
	//hide the new location input
	$('location_change_container').style.display = "none";

	//update the current location labels
	$('location_current_city').innerHTML = location_name;
	$('location_current_country').innerHTML = location_country;

	//reset lat/lon adjustments
	latadj = 0;
	lonadj = 0;

	//update the observer latitude and longitude
	obslat = location_lat;
	obslon = location_lon;

	//update the latitude and longitude displays
	$('locdis_lat').value = latadj + obslat;
	$('locdis_lon').value = lonadj + obslon;

	//redraw the starmap
	draw();
}

/*
 * Draw the starmap
 */
function draw() {
	//what is the right ascencion and declination of the point we're looking at on the starmap
	var obsra_tmp = lonadj - obslon - raadj + 90;
	var obsdec_tmp = latadj + obslat;

	var canvas = document.getElementById('tutorial');
	if (canvas.getContext) {
		var ctx = canvas.getContext('2d');

		//clear everything off the canvvas
		ctx.clearRect(0, 0, 3600, 1800);

		//Because of the equations we use we can't just rotate from above the equator to below:
		//Whenever we cross the equator we have to rotate the entire screen, to get the right image
		//we also need add 180 to the longitude offset, but that's taken care of at the point when we draw individual stars

		//if we have a latitude less than zero
		//AND the screen hasn't already been flipped
		if (obsdec_tmp < 0 && !flipped) {
			//then flip the screen
			ctx.translate(1800, 900);
			ctx.rotate(Math.PI);
			ctx.translate(-1800, -900);
		
			//and remember that it is flipped
			flipped = true;

		//if we have a latitude over zero
		//AND the screen has already been flipped
		} else if (obsdec_tmp >= 0 && flipped) {
			//then UNflip the screen
			ctx.translate(1800, 900);
			ctx.rotate(Math.PI);
			ctx.translate(-1800, -900);
		
			//and remember that it's NOT flipped
			flipped = false;
		}
	
/*
		//code to rotate the stardome around the center of the screen
		if (rotadj != rotadj_actual) {
			rotadj_tmp = rotadj_actual - rotadj;
		
			ctx.translate(1800, 900);
			ctx.rotate((rotadj_tmp * Math.PI) / 180);
			ctx.translate(-1800, -900);
		
			rotadj_actual = rotadj;
		}
*/

		//sphererad: the radius of the stardome
		//this is the direct manifestation of the zoom
		var sphererad = zmadj;

		var viewlat = obsdec_tmp;
		var viewlat_rad = deg2rad(viewlat);

/*
		//bright sky during daytime
		ctx.fillStyle = ("#c0ccff");
		ctx.fillRect(0, 0, 3600, 1800);
*/

/*
		//glow around edge of stardome
		var sky_grad = ctx.createRadialGradient(1800, 900, 0, 1800, 900, sphererad);
		sky_grad.addColorStop(0, "#000000");
		sky_grad.addColorStop(0.75, "#000000");
		sky_grad.addColorStop(1, "#010206");

		ctx.fillStyle = sky_grad;
		ctx.arc(1800, 900, sphererad, 0, 2 * Math.PI, false);
		ctx.fill();
*/

		//Draw circle around hemisphere
		ctx.strokeStyle = "rgba(255, 255, 255, 0.05)";
		ctx.lineWidth = 2;
		ctx.beginPath();
		
		for (var i = 0; i <= 360; i += 1) {
			var moo = ((i * Math.PI) / 180);
		
			var height = sphererad;
			var width = sphererad;
		
/* 			var xpos = 1800 + (width * Math.cos(moo) * Math.cos(deg2rad(0))) - (width * Math.sin(moo) * Math.sin(deg2rad(0))); */
			var xpos = 1800 + (width * Math.cos(moo));
/* 			var ypos = 900 + (height * Math.cos(moo) * Math.sin(deg2rad(0))) + (height * Math.sin(moo) * Math.cos(deg2rad(0))); */
			var ypos = 900 + (height * Math.sin(moo));

			if (i == 0) {
				ctx.moveTo(xpos, ypos);
			} else {
				ctx.lineTo(xpos, ypos);
			}
		}
		ctx.closePath();
		ctx.stroke();


		//for each star
		for (var starid in stars) {
			//javascript puts other silly things like the array length into the object
			//skip them
			if (!stars[starid]['r']) continue;

			//the declination of the star
			var j = stars[starid]['y'];
			//the right ascencion of the star
			var i = stars[starid]['x'];
	
			//reverse right ascenction
/* 			i = 360 - i; */
				i += 180;
	
	
			//Whenever we cross the equator and have to rotate the entire screen
			//we need add 180 to the longitude offset, so the stars arn't upside down
			if (obsdec_tmp < 0) {
				j *= -1;
			} else {
				i += 180;
			}
				
						i += obsra_tmp;
				
						var i_rad = deg2rad(i);
						var j_rad = deg2rad(j);
				
						var width = sphererad * Math.cos(j_rad);
				
						var height = width * Math.sin(viewlat_rad);
				
						var alt = sphererad * Math.sin(j_rad);
						var altpersp = alt * Math.cos(viewlat_rad);
				
/* 						var xpos = 1800 + (width * Math.cos(i_rad) * Math.cos(deg2rad(0))) - (width * Math.sin(i_rad) * Math.sin(deg2rad(0))); */
						var xpos = 1800 + (width * Math.cos(i_rad));
/* 						var ypos = 900 - altpersp + (height * Math.cos(i_rad) * Math.sin(deg2rad(0))) + (height * Math.sin(i_rad) * Math.cos(deg2rad(0))); */
						var ypos = 900 - altpersp + (height * Math.sin(i_rad));
				
				
						var vislim = (Math.atan(Math.sin(((i - 180) * Math.PI) / 180) / Math.tan(viewlat_rad)) * 2 * 90) / Math.PI;
				
						if (vislim > j) {
							continue;
						}

/*
						//scale stars with zoom					
						stars[starid]['r'] = stars[starid]['r'] * (zmadj / 450);
*/
							
/*
						//use shadow to show a glow around stars
						ctx.shadowColor   = 'rgba(255, 255, 255, 1)';
						ctx.shadowOffsetX = 0;
						ctx.shadowOffsetY = 0;
						ctx.shadowBlur    = 5;
*/
					
						//draw the star
						ctx.fillStyle = stars[starid]['c'];
						ctx.beginPath();
						ctx.arc(xpos, ypos, stars[starid]['r'], 0, Math.PI * 2, true);
						ctx.closePath();
						ctx.fill();

/*
						//reset the shadow, so nothing else shows up with a glow
						ctx.shadowColor   = '';
						ctx.shadowBlur    = 0;
*/

						//if the star is named, then pass it's location to the planets array
						//so that we know where to put it's label
						if (stars[starid]['n']) {
							planets[stars[starid]['n']]['x'] = xpos;
							planets[stars[starid]['n']]['y'] = ypos;
						}

						if (stars[starid]['mw']) {
							var milkyway_index = milkyway.length;

							milkyway[milkyway_index] = new Array();

							milkyway[milkyway_index]['action'] = stars[starid]['mw'];
							milkyway[milkyway_index]['x'] = xpos;
							milkyway[milkyway_index]['y'] = ypos;
						}
					}
					
					

					
					//Draw latitude lines
					for (var j = -90; j < 90; j += 15) {
						ctx.strokeStyle = "rgba(255, 255, 255, 0.05)";
						ctx.lineWidth = 2;
						ctx.beginPath();
					
						for (var i = 0; i <= 360; i += 1) {
							var moo = ((i * Math.PI) / 180);
					
							var width = sphererad * Math.cos(deg2rad(j));
					
							var height = width * Math.sin(deg2rad(viewlat));
					
							var alt = sphererad * Math.sin(deg2rad(j));
							var altpersp = alt * Math.cos(deg2rad(viewlat));
					
/* 							var xpos = 1800 + (width * Math.cos(moo) * Math.cos(deg2rad(0))) - (width * Math.sin(moo) * Math.sin(deg2rad(0))); */
							var xpos = 1800 + (width * Math.cos(moo));
/* 							var ypos = 900 - altpersp + (height * Math.cos(moo) * Math.sin(deg2rad(0))) + (height * Math.sin(moo) * Math.cos(deg2rad(0))); */
							var ypos = 900 - altpersp + (height * Math.sin(moo));
					
					
							var vislim = (Math.atan(Math.sin(((i - 180) * Math.PI) / 180) / Math.tan((viewlat * Math.PI) / 180)) * 2 * 90) / Math.PI;
					
							if (vislim > j || i == 0) {
								ctx.moveTo(xpos, ypos);
							} else {
								ctx.lineTo(xpos, ypos);
							}
						}
						ctx.stroke();
					}



/*
ctx.shadowColor = 'rgba(191, 191, 255, 0.1)';
ctx.fillStyle = "rgba(255, 255, 255, 1)";
ctx.beginPath();

for (var i in milkyway) {
	if (!milkyway[i]['action']) continue;

	if (flipped) {
		milkyway[i]['x'] += 3600;
	} else {
		milkyway[i]['x'] -= 3600;
	}
	milkyway[i]['y'] -= 18;

	if (milkyway[i]['action'] == "M") {
		ctx.moveTo(milkyway[i]['x'], milkyway[i]['y']);
	} else {
		ctx.lineTo(milkyway[i]['x'], milkyway[i]['y']);
	}
}

ctx.closePath();
ctx.fill();
ctx.shadowOffsetX = 3600;
ctx.shadowOffsetY = 18;
ctx.shadowBlur = 50;
*/

if (planets) {
	if (flipped) {
		//then UNflip the screen
		ctx.translate(1800, 900);
		ctx.rotate(Math.PI);
		ctx.translate(-1800, -900);
	}


	for (var i in planets) {
		if (!planets[i]['x']) continue;

		ctx.fillStyle = "#000000";
		ctx.font = "8pt Lucida Grande, Lucida Sans, Lucida, Verdana";
		ctx.textAlign = "center";
		ctx.textBaseline = "middle";

		var skoon = planets[i]['n'];

		if (skoon == "Sun" || skoon == "Moon") {
			skoon = "The "+skoon;
		}


		if (flipped) {
			var skoox = 3600 - planets[i]['x'];
			var skooy = 1800 - planets[i]['y'] + 30;
		} else {
			var skoox = planets[i]['x'];
			var skooy = planets[i]['y'] + 30;
		}

		planets[i]['x'] = null;
		planets[i]['y'] = null;
		
		var skoowid = ctx.measureText(skoon);
		skoowid = skoowid.width;
		skoowid += 20;
		skoowid += 1;
		skoowid /= 2;
		
		var skoohgt = 24;
		skoohgt += 1;
		skoohgt /= 2;
		
		var skoorad = 5;
		
		var skooarr = 8;
		
		ctx.fillStyle = "rgba(0, 0, 0, 0.75);";
		ctx.strokeStyle = "rgba(255, 255, 255, 0.15);";
		ctx.lineWidth = 2;
		ctx.beginPath();
		ctx.arc(skoox - skoowid + skoorad, skooy - skoohgt + skoorad, skoorad, (180 * Math.PI) / 180, (270 * Math.PI) / 180, false);
		ctx.lineTo(skoox - skooarr, skooy - skoohgt);
		ctx.lineTo(skoox - 0, skooy - skoohgt - skooarr);
		ctx.lineTo(skoox + skooarr, skooy - skoohgt);
		ctx.arc(skoox + skoowid - skoorad, skooy - skoohgt + skoorad, skoorad, (270 * Math.PI) / 180, 0, false);
		ctx.arc(skoox + skoowid - skoorad, skooy + skoohgt - skoorad, skoorad, 0, (90 * Math.PI) / 180, false);
		ctx.arc(skoox - skoowid + skoorad, skooy + skoohgt - skoorad, skoorad, (90 * Math.PI) / 180, (180 * Math.PI) / 180, false);
		ctx.closePath();
		ctx.fill();
		ctx.stroke();
	
		ctx.fillStyle = "rgba(255, 255, 255, 0.5);";
		ctx.font = "8pt Lucida Grande, Lucida Sans, Lucida, Verdana";
		ctx.textAlign = "center";
		ctx.textBaseline = "middle";
		ctx.fillText(skoon, skoox, skooy);
	}
	
	if (flipped) {
		//then UNflip the screen
		ctx.translate(1800, 900);
		ctx.rotate(Math.PI);
		ctx.translate(-1800, -900);
	}
}

				}
			}
