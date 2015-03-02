<?php

mysql_connect("localhost", "root", "root") or die(mysql_error()); mysql_select_db("divvy") or die(mysql_error());

$sql_get_rows = "SELECT id FROM 2014_distances LIMIT 10";
$query_get_rows = mysql_query($sql_get_rows);
$id_get_rows = mysql_fetch_row($query_get_rows);
$num_get_rows = mysql_num_rows($sql_get_rows);
$i = $id_get_rows[0];

echo $i;

foreach ($i=$i;$i<$num_get_rows;$i++) {

	$sql_get = "SELECT 2014_distances.id, 2014_distances.count, 2014_distances.station_pair, 2014_trips.station_pair, AVG(2014_trips.tripduration) as divvy_avg_time, 2014_distances.time_bike, 2014_distances.time_bike - AVG(2014_trips.tripduration) as diff
	FROM 2014_distances, 2014_trips
	WHERE 2014_distances.id = " . $i . " AND 2014_distances.station_pair = 2014_trips.station_pair";

	$query = mysql_query($sql_get);
	$id_row = mysql_fetch_row($query);
	$num_rows = mysql_num_rows($sql_get);

		echo "Divvy time: " . $id_row[4] . "<br />";
		echo "Bike time: " . $id_row[5] . "<br />";
		echo "Difference: " . $id_row[6] . "<br />";
		echo "Pair: " . $id_row[2] . " / " . $id_row[3] ."<br />";
		echo "ID: " . $id_row[0] . "<br />";
		echo "# trips: " . $id_row[1] . "<br />";

}

mysql_close(); 

?>