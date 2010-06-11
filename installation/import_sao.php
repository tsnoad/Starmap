#!/usr/bin/php
<?php

//get a little bit from the start of the tdat file
$saodata = file_get_contents("heasarc_sao.tdat", false, null, 0, 10000);

//we'll be searching for the table parameters section
//which starts like this:
$param_start_search = "#
# Table Parameters
#
";

//and ends like this
$param_end_search = "
#
parameter_defaults = ";

//find the start of the table params section
$param_start = strpos($saodata, $param_start_search);
//not including the header
$param_start += strlen($param_start_search);

//find the end of the table params section
$param_end = strpos($saodata, $param_end_search);

//get the table parameters section of the tdat file
$saodata = file_get_contents("heasarc_sao.tdat", false, null, $param_start, $param_end - $param_start);

file_put_contents("create_sao_database.sql", "");

file_put_contents("create_sao_database.sql", "CREATE TABLE stars (\n", FILE_APPEND);

file_put_contents("create_sao_database.sql", "\tid BIGSERIAL PRIMARY KEY,\n", FILE_APPEND);

$saofieldtypetranslate = array("char" => "TEXT", "int" => "INT", "float" => "FLOAT");

$saofieldrows = explode("\n", $saodata);
foreach ($saofieldrows as $saofieldrow) {
	preg_match_all("/field\[([^\]]+)\]\ =\ (char|int|float)/", $saofieldrow, &$matches);

	$saofieldname = $matches[1][0];
	$saofieldtype = $matches[2][0];

	$saofieldtype = $saofieldtypetranslate[$saofieldtype];

	file_put_contents("create_sao_database.sql", "\t{$saofieldname} {$saofieldtype},\n", FILE_APPEND);

	$saofields[$saofieldname] = true;
}

file_put_contents("create_sao_database.sql", "\tignoreme text\n", FILE_APPEND);
$saofields["ignoreme"] = true;

file_put_contents("create_sao_database.sql", ");\n", FILE_APPEND);

if ($saofields["ra"]) {
	file_put_contents("create_sao_database.sql", "create index ra_stars on stars (ra);\n", FILE_APPEND);
}
if ($saofields["dec"]) {
	file_put_contents("create_sao_database.sql", "create index dec_stars on stars (dec);\n", FILE_APPEND);
}
if ($saofields["vmag"]) {
	file_put_contents("create_sao_database.sql", "create index vmag_stars on stars (vmag);\n", FILE_APPEND);
}



file_put_contents("create_sao_database.sql", "\n", FILE_APPEND);
file_put_contents("create_sao_database.sql", "COPY stars (".implode(", ", array_keys($saofields)).") FROM stdin WITH DELIMITER '|' NULL AS '';\n", FILE_APPEND);

$data_start_search = "<DATA>";

$param_end_search = "<END>";

$param_start = shell_exec("grep -n '{$data_start_search}' heasarc_sao.tdat | cut -d: -f1");
$param_start = trim($param_start);
$param_start += 1;

$param_end = shell_exec("grep -n '{$param_end_search}' heasarc_sao.tdat | cut -d: -f1");
$param_end = trim($param_end);
$param_end -= 1;

shell_exec("sed -n -e '{$param_start},{$param_end}p' heasarc_sao.tdat >> create_sao_database.sql");

file_put_contents("create_sao_database.sql", "\\.\n", FILE_APPEND);


file_put_contents("create_sao_database.sql", "ALTER TABLE stars DROP COLUMN ignoreme;\n", FILE_APPEND);

?>