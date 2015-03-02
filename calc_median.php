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

<h1>Calculating Medians</h1>
<h3>This script calculates the median trip duration from 2014_trips and returns it based on the station pair.<br />The median value is then updated in the 2014_distances database.<br />Each calculation takes approximately 3 seconds.</h3>

<?php

$start_at = 2005;
$number_to_update = 8000;

$time_required = time() + ($number_to_update * 3.1);
$time_now = date("Y-m-d H:i:s");
$end_time = date("Y-m-d H:i:s", $time_required);

echo "Process should end around " . $end_time . " (GMT+1).<br /><br />";

mysql_connect("localhost", "root", "root") or die(mysql_error()); mysql_select_db("divvy") or die(mysql_error());

$get_distance_record_q = "SELECT id, station_pair FROM 2014_distances WHERE id > 0";
$get_distance_record_e = mysql_query($get_distance_record_q);
$get_distance_record_r = mysql_fetch_row($get_distance_record_e);
$a = $get_distance_record_r[0];
$get_distance_num_rows = mysql_num_rows($get_distance_record_e);

// echo $get_distance_num_rows . " records<br /><br />";

for ($i=$start_at; $i<($start_at + $number_to_update); $i++) {
	$sql_q = "SELECT id, station_pair FROM 2014_distances WHERE id = " . $i . "";
	$sql_e = mysql_query($sql_q);
	$sql_r = mysql_fetch_row($sql_e);
	$b = $sql_r[0];
	$sp = $sql_r[1];
	echo $b . " (" . $sp . "): ";


$query = "SELECT avg(t1.tripduration) as divvy_median_time  FROM (
			SELECT @rownum:=@rownum+1 as `row_number`, d.tripduration
			FROM 2014_trips as d, (SELECT @rownum:=0) r
			WHERE d.station_pair = \"" . $sp . "\"
			ORDER BY d.tripduration
		) as t1, (
			SELECT count(*) as total_rows
			FROM 2014_trips d
			WHERE d.station_pair = \"" . $sp . "\"
		) as t2
		WHERE t1.row_number in ( floor((total_rows+1)/2), floor((total_rows+2)/2) )";

$run_query = mysql_query($query);
$id_row = mysql_fetch_row($run_query);
$c = $id_row[0];

echo $c;

$update_q = "UPDATE 2014_distances SET median_time_bike = " . $c . " WHERE id = " . $b . "";
if (mysql_query($update_q) === TRUE) {
	    	echo "<span style=\"color:#32CD32;\"> Successfully updated.</span><br />";
	    } else {
	    	echo "<span style=\"color:#ff0000;\"> Error updating row.</span><br />" . mysql_error() . "";
	    }
}

echo "<br />";


mysql_close();

?>

<br /><br /><h2>End update.</h2>
</body>
</html>