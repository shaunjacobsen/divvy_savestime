<html>
<head>
	<style type="text/css">
		html {
		font-family: "Helvetica Neue", "Helvetica", sans-serif;
		font-size: 11px;
		}
	</style>
</head>
<body>

<h1>Getting transit route IDs</h1>
<h3>This script iterates over each row in 2014_distances, gets the bus route name, matches it to the ctabuses table,<br />re-organizes the path into a string, and updates the 2014_distances table.</h3>

<?php

mysql_connect("localhost", "root", "root") or die(mysql_error()); mysql_select_db("divvy") or die(mysql_error());

$query = "SELECT id, transit_line FROM 2014_distances WHERE id < 10000";
$query_e = mysql_query($query);
$query_r = mysql_fetch_row($query_e);
$query_nr = mysql_num_rows($query_e);
$get_id = $query_r[0];

for ($i=0;$i<$query_nr;$i++) {
	$line_q = "SELECT id, transit_line FROM 2014_distances WHERE id = " . $i . "";
	$line_e = mysql_query($line_q);
	$line_r = mysql_fetch_row($line_e);
	$line_string = $line_r[1];
	$line_explode = explode(",", $line_string);
	if ($line_explode[0] != NULL) {
		$l1 = $line_explode[0];
	} else {
		$l1 = 0;
	}
	if ($line_explode[1] != NULL) {
		$l2 = $line_explode[1];
	} else {
		$l2 = 0;
	}

	echo "ID: " . $line_r[0] . " " . $l1;

	$sql_get_line_1 = "SELECT `ctabuses`.`id`, `ctabuses`.`route`, `ctabuses`.`name`, `ctabuses`.`color`, `2014_distances`.`id`, `2014_distances`.`transit_line` FROM ctabuses, 2014_distances WHERE `ctabuses`.`name` = '" . $l1 . "'";
	$get_line_1_e = mysql_query($sql_get_line_1);
	$get_line_1_r = mysql_fetch_row($get_line_1_e);
	$get_line_1_name = $get_line_1_r[1];
	$get_line_1_colour = $get_line_1_r[3];
	echo " (" . $get_line_1_name . ") then ";

	echo $l2;

	$sql_get_line_2 = "SELECT `ctabuses`.`id`, `ctabuses`.`route`, `ctabuses`.`name`, `ctabuses`.`color`, `2014_distances`.`id`, `2014_distances`.`transit_line` FROM ctabuses, 2014_distances WHERE `ctabuses`.`name` = '" . $l2 . "'";
	$get_line_2_e = mysql_query($sql_get_line_2);
	$get_line_2_r = mysql_fetch_row($get_line_2_e);
	$get_line_2_name = $get_line_2_r[1];
	$get_line_2_colour = $get_line_2_r[3];
	echo " (" . $get_line_2_name . ") = ";

	if ($l2 = 0) {
		$concat_line_names = $get_line_1_name;
		$concat_colours = $get_line_1_colour;
	} else {
		$concat_line_names = $get_line_1_name . "," . $get_line_2_name;
		$concat_colours = $get_line_1_colour . "," . $get_line_2_colour;
	}

	echo trim($concat_line_names,",") . ". ";


	$update_q = "UPDATE 2014_distances SET transit_line_short = '" . trim($concat_line_names,",") . "', colour = '" . $concat_colours . "' WHERE id = " . $i . "";
	if (mysql_query($update_q) === TRUE) {
	    	echo "<span style=\"color:#32CD32;\"> Successfully updated.</span><br />";
	    } else {
	    	echo "<span style=\"color:#ff0000;\"> Error updating row.</span>" . mysql_error() . "<br />";
	    }


}

mysql_close();

?>
</body>
</html>