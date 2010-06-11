#!/usr/bin/php
<?php

//get a little bit from the start of the tdat file
$countries = file_get_contents("countryInfo.txt", false, null, 0, 10000);

//we'll be searching for the table parameters section
//which starts like this:
$param_start_search = "#ISO";

//and ends like this
$param_end_search = "
";

//find the start of the table params section
$param_start = strpos($countries, $param_start_search);
//not including the header
$param_start += strlen("#");

//find the end of the table params section
$param_end = strpos($countries, $param_end_search, $param_start);

//get the table parameters section of the tdat file
$countries = file_get_contents("countryInfo.txt", false, null, $param_start, $param_end - $param_start);

$countryfieldcolumns = explode("\t", $countries);
foreach ($countryfieldcolumns as $countryfieldcolumn_id => $countryfieldcolumn) {
	if ($countryfieldcolumn == "Country") {
		$countrynamecolumn_id = $countryfieldcolumn_id;
	}
	if ($countryfieldcolumn == "ISO") {
		$countryisocolumn_id = $countryfieldcolumn_id;
	}
}

if (!isset($countryisocolumn_id) || !isset($countrynamecolumn_id)) {
	die("could not decode country source file");
}

//find the start of the table params section
$param_start = $param_end;
//not including the header
$param_start += strlen($param_end_search);

$countries = file_get_contents("countryInfo.txt", false, null, $param_start);
$countries = trim($countries);
$countryrows = explode("\n", $countries);
foreach ($countryrows as $countryrow) {
	$countryrowcolumns = explode("\t", $countryrow);

	$countryiso = $countryrowcolumns[$countryisocolumn_id];
	$countryname = $countryrowcolumns[$countrynamecolumn_id];

	$countrynamesbyiso[$countryiso] = $countryname;
}

$create_table_sql = "
	CREATE TABLE locations (
		id BIGSERIAL PRIMARY KEY,
		name TEXT,
		country TEXT,
		population BIGINT,
		lat FLOAT,
		lon FLOAT
	);
	CREATE INDEX name_locations ON locations (name);
	CREATE INDEX population_locations ON locations (population);
	";


file_put_contents("create_location_database.sql", "");

file_put_contents("create_location_database.sql", "$create_table_sql", FILE_APPEND);

file_put_contents("create_location_database.sql", "\n", FILE_APPEND);
file_put_contents("create_location_database.sql", "COPY locations (name, country, population, lat, lon) FROM stdin WITH DELIMITER '|' NULL AS '';\n", FILE_APPEND);


$cities = file_get_contents("cities15000.txt");
$cities = trim($cities);
$cityrows = explode("\n", $cities);
foreach ($cityrows as $cityrow) {
	$citycolumns = explode("\t", $cityrow);

	$cityname = $citycolumns[1];
/* 	$cityname = str_replace("'", "\\'", $cityname); */

	$citylat = $citycolumns[4];
	$citylon = $citycolumns[5];

	$citycountryiso = $citycolumns[8];
	$citycountry = $countrynamesbyiso[$citycountryiso];

	$citypopulation = $citycolumns[14];

	file_put_contents("create_location_database.sql", "{$cityname}|{$citycountry}|{$citypopulation}|{$citylat}|{$citylon}\n", FILE_APPEND);

}

file_put_contents("create_location_database.sql", "\\.\n", FILE_APPEND);




?>