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

//timezone offset of the observer's location
var tmzofs = 36000;

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
 * Get a named cookie from the browser
 */
function get_cookie(name) {
	//make sure that the browser has some cookies that belong to us
	if (document.cookie.length > 1) {
		c_start = document.cookie.indexOf(name+"=");
		
		if (c_start != -1) {
			c_start += name.length + 1;
			c_end = document.cookie.indexOf(";", c_start);
			
			if (c_end == -1) c_end = document.cookie.length;
			
			return unescape(document.cookie.substring(c_start, c_end));
		}
	}
}

/*
 * Start putting the page together
 */
function init() {
	//get city and county name from the cookie.
	var location_name = get_cookie("location_name");
	var location_country = get_cookie("location_country");
	//get observer's timezone offset from cookie. if none exists default in tmzofs variable will be used
	var location_timezone = get_cookie("location_timezone");
	//get observer's lat/lon from cookie. if none exists default in obslat/obslon variables will be used
	var location_lat = get_cookie("location_lat");
	var location_lon = get_cookie("location_lon");

	//if cookie exists
	if (location_name) {
		//update the current location labels
		$('location_current_city').innerHTML = location_name;
		$('location_current_country').innerHTML = location_country;
	
		//reset lat/lon adjustments
		latadj = 0;
		lonadj = 0;
	
		//update the observer latitude and longitude
		obslat = parseFloat(location_lat);
		obslon = parseFloat(location_lon);

		//update the observer's timezone offser
		tmzofs = location_timezone;
	}

	//set the clock
	adjdatedis();

	//center the starmap on screen
	repos_canvas();

	//start keeping track of sidereal time
	rottime_repeat();

	//initial size of the skydome should match the size of the browser
	zmadj = (Math.min(self.innerWidth, self.innerHeight) / 2) * 1.1;

	//update the latitude and longitude displays
	$('locdis_lat').innerHTML = (latadj + obslat).toString() + "\u00B0";
	$('locdis_lon').innerHTML = (lonadj + obslon).toString() + "\u00B0";

	//draw the starmap
	draw();

	if((navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))) {
	  window.document.body.className = 'ios';
	}

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

	//add the timezone offset
	unix_seconds += parseFloat(tmzofs);

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

	var paneloffset = 0;

	if ($('leftpanel').className == 'leftpanelouter leftpanelouter_collapsed') {
		paneloffset -= (250 - 42) / 2;
	}

	if ($('rightpanel').className == 'rightpanelouter rightpanelouter_collapsed') {
		paneloffset += (250 - 42) / 2;
	}

	//find the canvas element
	var canvas = document.getElementById('tutorial');
	//center horizontally
	canvas.style.left = (((self.innerWidth - 3600) / 2) + paneloffset) + "px";
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
	$('locdis_lat').innerHTML = (latadj + obslat).toString() + "\u00B0";
	$('locdis_lon').innerHTML = (lonadj + obslon).toString() + "\u00B0";

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

	//create a date element with:
	//the current timestamp, which will be the same, no matter where we are in the world
	//plus the offset for observer's current timezone.
	//this will give us a freakish local timestamp, that will give the time/date for the observer's location, if we retrieve it with getUTC
	//yes, I know it's horrible, but since we can't set a locale in javascript, this is the only way to dynamically change timezones
	//plus the date adjustment offset - or how much the date has been manually changed by the user
	var specdate = new Date(curdate.getTime() + (tmzofs * 1000) + (dateadj * 1000));

	//so we can show month names, I hope these don't change anytime soon
	var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];

	//update year, month, day, hour, etc. clock display
	$('datedis_year').innerHTML = specdate.getUTCFullYear();
	$('datedis_month').innerHTML = monthNames[specdate.getUTCMonth()];
	$('datedis_day').innerHTML = specdate.getUTCDate();

	$('datedis_mrd').innerHTML = (specdate.getUTCHours() < 12 ? "AM" : "PM");
	
	$('datedis_hour').innerHTML = specdate.getUTCHours();
	//display minutes and seconds with leading zeros
	//fuck, I hate javascript
	$('datedis_min').innerHTML = (specdate.getUTCMinutes() < 10 ? "0" : "") + specdate.getUTCMinutes();
	$('datedis_sec').innerHTML = (specdate.getUTCSeconds() < 10 ? "0" : "") + specdate.getUTCSeconds();
	
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
function location_change(location_name, location_country, location_timezone, location_lat, location_lon) {
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

	//update the observer's timezone offset
	tmzofs = location_timezone;

	//update the latitude and longitude displays
	$('locdis_lat').innerHTML = (latadj + obslat).toString() + "\u00B0";
	$('locdis_lon').innerHTML = (lonadj + obslon).toString() + "\u00B0";

	//recalculate observer's sidereal time
	rottime();

	//redraw the starmap
	draw();

	//the cookies that we're about to write should last for a year (too long?)
	var cookie_date = new Date();
	cookie_date.setTime(cookie_date.getTime()+(365*24*60*60*1000));
	var cookie_expires = "expires="+cookie_date.toGMTString()+";";

	//store the location in a cookie
	document.cookie = "location_name="+escape(location_name)+"; "+cookie_expires+" path=/";
	document.cookie = "location_country="+escape(location_country)+"; "+cookie_expires+" path=/";
	document.cookie = "location_timezone="+escape(location_timezone)+"; "+cookie_expires+" path=/";
	document.cookie = "location_lat="+escape(location_lat)+"; "+cookie_expires+" path=/";
	document.cookie = "location_lon="+escape(location_lon)+"; "+cookie_expires+" path=/";
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
		
		//we'll draw the circle by drawing a line with 360 points
		for (var i = 0; i <= 360; i += 1) {
			var i_rad = deg2rad(i);
		
			var height = sphererad;
			var width = sphererad;
		
			//work out the x/y coordinates of this point
			var xpos = 1800 + (width * Math.cos(i_rad));
			var ypos = 900 + (height * Math.sin(i_rad));

			//move to the first point
			if (i == 0) {
				ctx.moveTo(xpos, ypos);

			//then start drawing
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
			
			//add the viewing longitude to the star's right ascencion
			i += obsra_tmp;
	
			//convert ra and dec from degrees to radians
			var i_rad = deg2rad(i);
			var j_rad = deg2rad(j);

			//for this viewing lat/lon, calculate the declination the star would have have to be on the visible half of the skydome 
			var vislim = (Math.atan(Math.sin(((i - 180) * Math.PI) / 180) / Math.tan(viewlat_rad)) * 180) / Math.PI;
			//if this star isn't on the visible half of the skydomw, skip it
			if (vislim > j) {
				continue;
			}
				
			//needs documentation
			var width = sphererad * Math.cos(j_rad);
			var height = width * Math.sin(viewlat_rad);
	
			//needs documentation
			var alt = sphererad * Math.sin(j_rad);
			var altpersp = alt * Math.cos(viewlat_rad);
	
			//needs documentation
			var xpos = 1800 + (width * Math.cos(i_rad));
			var ypos = 900 - altpersp + (height * Math.sin(i_rad));

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
			ctx.shadowColor   = 'rgba(255, 255, 255, 0)';
			ctx.shadowBlur    = 0;
*/

			//if the star is named, then pass it's location to the planets array
			//so that we know where to put it's label
			if (stars[starid]['n']) {
				planets[stars[starid]['n']]['x'] = xpos;
				planets[stars[starid]['n']]['y'] = ypos;
			}

			//if the star is actually a point in the milky way outline
			if (stars[starid]['mw']) {
				//we need to put this points coordinates into an array, so we can draw it later

				//what's the next available key in the milkyway array
				var milkyway_index = milkyway.length;

				//create a new subarray where we can store all the details
				milkyway[milkyway_index] = new Array();

				//store everything we'll need
				milkyway[milkyway_index]['action'] = stars[starid]['mw'];
				milkyway[milkyway_index]['x'] = xpos;
				milkyway[milkyway_index]['y'] = ypos;
			}
		}

		//if we're supposed to be drawing an ecliptic line
		if ($('option_show_ecliptic').checked) {
			//Draw the ecliptic line
			ctx.strokeStyle = "rgba(63, 127, 255, 0.25)";
			ctx.lineWidth = 2;
			ctx.beginPath();
		
			//we'll draw the ecliptic by drawing a line with 360 points around it's circumference
			for (var i = 0; i <= 360; i += 1) {
				//the latitude of this point
				//if the ecliptic went around the equator the latitude of this point would always be zero
				//but since the ecliptic is tilted at 22.5 degrees we need to know how high this point is at this longitude
				//this equation is very similar to the one we use to get vislim, I recommend punching it into a graphing calculator and playing around with the variables
				var j = (Math.atan(Math.sin(((i - 180) * Math.PI) / 180) / Math.tan(deg2rad(-90 + 22.5))) * 2 * 90) / Math.PI;

				//add the viewing longitude to the point's longitude
				var i_tmp = i + obsra_tmp;
		
				//convert point's lat and lon from degrees to radians
				var i_rad = deg2rad(i_tmp);
				var j_rad = deg2rad(j);
		
				//needs documentation
				var width = sphererad * Math.cos(j_rad);
				var height = width * Math.sin(viewlat_rad);
		
				//needs documentation
				var alt = sphererad * Math.sin(j_rad);
				var altpersp = alt * Math.cos(viewlat_rad);
		
				//needs documentation
				var xpos = 1800 + (width * Math.cos(i_rad));
				var ypos = 900 - altpersp + (height * Math.sin(i_rad));

				//for this viewing lat/lon, calculate the longitude the point would have have to be on the visible half of the skydome 
				var vislim = (Math.atan(Math.sin(((i_tmp - 180) * Math.PI) / 180) / Math.tan((viewlat * Math.PI) / 180)) * 2 * 90) / Math.PI;
		
				//if this point isn't on the visible half of the skydome, don't draw it
				if (vislim > j || i_tmp == 0) {
					ctx.moveTo(xpos, ypos);

				//if it is, do draw it
				} else {
					ctx.lineTo(xpos, ypos);
				}
			}
			ctx.stroke();
		}

		//if we're supposed to be drawing the grid
		if ($('option_show_grid').checked) {
			//Draw latitude lines
			//we need to draw a series of circles from -90deg lat to +90deg lat, at 15deg points
			for (var j = -90; j < 90; j += 15) {
				ctx.strokeStyle = "rgba(255, 255, 255, 0.05)";
				ctx.lineWidth = 2;
				ctx.beginPath();
			
				//for this circle
				//we'll draw the circle by drawing a line with 360 points around the circle's circumference
				for (var i = 0; i <= 360; i += 1) {
					//convert point's lat and lon from degrees to radians
					var i_rad = deg2rad(i);
					var j_rad = deg2rad(j);
			
					//needs documentation
					var width = sphererad * Math.cos(j_rad);
					var height = width * Math.sin(viewlat_rad);
			
					//needs documentation
					var alt = sphererad * Math.sin(deg2rad(j));
					var altpersp = alt * Math.cos(deg2rad(viewlat));
			
					//needs documentation
					var xpos = 1800 + (width * Math.cos(i_rad));
					var ypos = 900 - altpersp + (height * Math.sin(i_rad));
			
					//for this viewing lat/lon, calculate the longitude the point would have have to be on the visible half of the skydome 
					var vislim = (Math.atan(Math.sin(((i - 180) * Math.PI) / 180) / Math.tan((viewlat * Math.PI) / 180)) * 2 * 90) / Math.PI;
			
					//if this point isn't on the visible half of the skydome, don't draw it
					if (vislim > j || i == 0) {
						ctx.moveTo(xpos, ypos);

					//if it is, do draw it
					} else {
						ctx.lineTo(xpos, ypos);
					}
				}
				ctx.stroke();
			}
	
/*
			//draw longitude lines
			for (var j = 0; j < 180; j += 15) {
				ctx.strokeStyle = "rgba(255, 255, 255, 0.05)";
				ctx.lineWidth = 2;
				ctx.beginPath();
		
				var rot = j + .01;
				rot += obsra_tmp;
		
				for (var i = 0; i <= 360; i += 1) {
					var moo = (i * Math.PI / 180);
		
					var height = sphererad * Math.cos(deg2rad(viewlat));
					var width = sphererad * Math.cos(deg2rad(rot));
		
					var boxtopperspheight = sphererad * Math.sin(deg2rad(viewlat));
					var nonperspheight = sphererad * Math.sin(deg2rad(rot)) * 2;
					var perspheight = (nonperspheight / (sphererad * 2)) * boxtopperspheight;
					var perspang = perspheight / (width);
		
					var xpos = 1800 + (width * Math.cos(moo) * Math.cos(deg2rad(0))) - (width * Math.sin(moo) * Math.sin(deg2rad(0)));
					var ypos = 900 + (height * Math.cos(moo) * Math.sin(deg2rad(0))) + (height * Math.sin(moo) * Math.cos(deg2rad(0))) + ((xpos - 1800) * perspang);
		
		
					var vislim = -1 * (Math.atan(Math.sin(((rot - 180) * Math.PI) / 180) / Math.tan((viewlat * Math.PI) / 180)) * 2 * 90) / Math.PI;
		
					if (i - 180 < vislim && i > vislim) {
						ctx.moveTo(xpos, ypos);
					} else {
						ctx.lineTo(xpos, ypos);
					}
				}
				ctx.stroke();
			}
*/
		}


/*
		//draw the milky way
		ctx.shadowColor = 'rgba(191, 191, 255, 0.1)';
		ctx.fillStyle = "rgba(255, 255, 255, 1)";
		ctx.beginPath();
		
		//for each of the points we stored ealier
		for (var i in milkyway) {
			//skip all the javascript array cruft
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

		//if we're supposed to be showing labels for planets, bright stars, nebula, etc.
		if ($('option_show_labels').checked) {
			//and there are planets, bright stars, nebula, etc. to put labels on
			if (planets) {
				//if the the canvas has been flipped
				//we'll have to unflip and reflip the canvas so the labels aren't upside down
				if (flipped) {
					//then UNflip the screen
					ctx.translate(1800, 900);
					ctx.rotate(Math.PI);
					ctx.translate(-1800, -900);
				}
			
				//for each object that's going to have a label
				for (var i in planets) {
					//skip javascript array cruft
					if (!planets[i]['x']) continue;
			
					ctx.fillStyle = "#000000";
					ctx.font = "8pt Lucida Grande, Lucida Sans, Lucida, Verdana";
					ctx.textAlign = "center";
					ctx.textBaseline = "middle";
			
					//get the object name from array
					var pla_n = planets[i]['n'];

					//Moon and Sun should be The Moon and The Sun
					if (pla_n == "Sun" || pla_n == "Moon") {
						pla_n = "The "+pla_n;
					}
			
					//if the screen is flipped then we'll need to reverse the x and y coordinates
					if (flipped) {
						var pla_x = 3600 - planets[i]['x'];
						var pla_y = 1800 - planets[i]['y'] + 30;
					} else {
						var pla_x = planets[i]['x'];
						var pla_y = planets[i]['y'] + 30;
					}
			
					//remove coordinates from the objects array, so that the object's location will be updated if it moves
					planets[i]['x'] = null;
					planets[i]['y'] = null;
					
					//how many pixels wide will the text for the name be
					//this is used to tell how wide the box, that contains the text, will be
					var pla_wid = ctx.measureText(pla_n);
					pla_wid = pla_wid.width;
					//plus margins
					pla_wid += 20;
					//add one pixel, so that the border will not render across multiple pixels
					pla_wid += 1;
					//divide by two: half on the left half on the right
					pla_wid /= 2;
					
					//box will always be 24px high
					var pla_hgt = 24;
					pla_hgt += 1;
					pla_hgt /= 2;
					
					//box corner radius
					var pla_rad = 5;
					
					//the box has a little arrow that points to the object
					//this is it's height
					var pla_arr = 8;
					
					//draw the box
					ctx.fillStyle = "rgba(0, 0, 0, 0.75);";
					ctx.strokeStyle = "rgba(255, 255, 255, 0.15);";
					ctx.lineWidth = 2;
					ctx.beginPath();
					//top left corner
					ctx.arc(pla_x - pla_wid + pla_rad, pla_y - pla_hgt + pla_rad, pla_rad, (180 * Math.PI) / 180, (270 * Math.PI) / 180, false);
					//arrow
					ctx.lineTo(pla_x - pla_arr, pla_y - pla_hgt);
					ctx.lineTo(pla_x - 0, pla_y - pla_hgt - pla_arr);
					ctx.lineTo(pla_x + pla_arr, pla_y - pla_hgt);
					//top right corner
					ctx.arc(pla_x + pla_wid - pla_rad, pla_y - pla_hgt + pla_rad, pla_rad, (270 * Math.PI) / 180, 0, false);
					//bottom right corner
					ctx.arc(pla_x + pla_wid - pla_rad, pla_y + pla_hgt - pla_rad, pla_rad, 0, (90 * Math.PI) / 180, false);
					//bottom left corner
					ctx.arc(pla_x - pla_wid + pla_rad, pla_y + pla_hgt - pla_rad, pla_rad, (90 * Math.PI) / 180, (180 * Math.PI) / 180, false);
					ctx.closePath();
					ctx.fill();
					ctx.stroke();
				
					//draw the text
					ctx.fillStyle = "rgba(255, 255, 255, 0.5);";
					ctx.font = "8pt Lucida Grande, Lucida Sans, Lucida, Verdana";
					ctx.textAlign = "center";
					ctx.textBaseline = "middle";
					ctx.fillText(pla_n, pla_x, pla_y);
				}

				//if the screen was flipped
				//we now need to unflip it so that the stars are the right way up				
				if (flipped) {
					//then UNflip the screen
					ctx.translate(1800, 900);
					ctx.rotate(Math.PI);
					ctx.translate(-1800, -900);
				}
			}
		}
	}
}
