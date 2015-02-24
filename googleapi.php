<html>
<head>
</head>
<body>

<?php

// Establish variables for call to Google Directions API

$i = 1;						// default value for i, in case it is not established below.
$deptime = 1426507200; 		// the departure time (in UNIX timestamp, i.e. seconds since 01/01/1970 – this value must be after today's date)
$mode = "walking";			// mode to get directions in (walking, bicycling, transit, driving)
$mode_mod = "walk";		// set to the same as $mode but with the following: walk, bike, transit, drive

// Connect to database

	mysql_connect("localhost", "root", "root") or die(mysql_error()); mysql_select_db("divvy") or die(mysql_error());

	echo "<strong>Beginning Update...</strong><br /><br />*****<br /><br />";

// Start the loop to get all information

//for ($i=200;$i<=100;$i++) {

	$sql_getid = "SELECT km_" . $mode_mod . ", time_" . $mode_mod . ", id FROM 2014_distances WHERE km_" . $mode_mod . " IS NULL ORDER BY id";
	$query_id = mysql_query($sql_getid);
    $id_row = mysql_fetch_row($query_id);
    $i = $id_row[2];


if ($i>1) {


// Find the coordinates of the from/to stations based on the id variable $i

    $sql_findcoord = "SELECT id, station_from_id, station_to_id, station_from_coord, station_to_coord FROM 2014_distances WHERE id = " . $i . "";
    $handler = mysql_query($sql_findcoord);
    $row = mysql_fetch_row($handler);
    $selected_id = $row[0];
    $station_from = $row[1];
    $station_to = $row[2];
    $station_from_coord = $row[3];
    $station_to_coord = $row[4];

// Load the data from the API via a URL

	$xml = simplexml_load_file("https://maps.googleapis.com/maps/api/directions/xml?origin=" . $station_from_coord . "&destination=" . $station_to_coord . "&departure_time=" . $deptime . "&mode=" . $mode . "&language=en&key=AIzaSyC5luicTwTrB6k8PWJCN2GPsSjS9p2K6do") or die("Error: cannot create xml object.");
	foreach($xml->xpath('.//leg') as $leg) {
		$attributes = $leg->attributes();
		echo $attributes['duration'] . "\n";
	}
// Get the status of the API connection by checking for loaded XML nodes

	echo "Status of API connection: ";
	echo $xml->status . "<br />";

// Double check the mode selected

	echo "Mode: ";
	echo $mode . "/" . $mode_mod . "<br />";

// Get the information from the database, to ensure the SQL query is correct

	echo $selected_id . " is the ID.<br />";
	echo $station_from . " > ";
	echo $station_to . "<br />";
	echo $station_from_coord . " > ";
	echo $station_to_coord . "<br />";

// Google Directions API data

	// Get the duration, in text (e.g. 18 minutes), and saves as $duration_name

		$dur_text = $xml->xpath('route/leg/duration/text');
		foreach($dur_text as $duration_name) {
			echo "Duration: " . $duration_name . " (";
		}

	// Get the duration, in seconds, and saves as $duration_seconds

		$dur_secs = $xml->xpath('route/leg/duration/value');
		foreach($dur_secs as $duration_seconds) {
			echo "" . $duration_seconds . " s)<br />";
		}

	// Get the distance, in text (e.g. 1.5 miles), and saves as $distance_name

		$dist_text = $xml->xpath('route/leg/distance/text');
		foreach($dist_text as $distance_name) {
			echo "Distance: " . $distance_name . " (";
		}

	// Get the distance, in metres, and saves as $distance_metres

		$dist_val = $xml->xpath('route/leg/distance/value');
		foreach($dist_val as $distance_metres) {
			echo "" . $distance_metres . " m)<br />";
		}

// Update the database

    $sql = "UPDATE `2014_distances` SET km_" . $mode_mod . " = " . $distance_metres. ", time_" . $mode_mod . " = " . $duration_seconds . " WHERE id =" . $i . "";
    
    if (mysql_query($sql) === TRUE) {
    	echo "<span style=\"color:#32CD32;\">Successfully updated.</span><br />";
    } else {
    	echo "<span style=\"color:#ff0000;\">Error updating row.</span><br />" . mysql_error() . "";
    }

echo "<br />***<br /><br />";

// usleep(600000);

} else {
	echo "FATAL ERROR.";
}

mysql_close();


/*

for ($i=0;$i<=10;$i++) {

	// find the coordinates of the from/to stations based on the id variable $i
    $sql_findcoord = "SELECT id, station_from_id, station_to_id, station_from_coord, station_to_coord FROM 2014_distances WHERE id=" . mysql_real_escape_string($i). "";
    $handler = mysql_query($sql_findcoord);
    $row = mysql_fetch_row($handler);
    $station_from_coord = $row[3];
    $station_to_coord = $row[4];


$json = file_get_contents('http://maps.googleapis.com/maps/api/directions/json?origin='.$station_from_coord.'&destination=' .$station_to_coord. '&departure_time=1426507200&mode=walking&language=en&key=api-project-866079145084');
$obj = json_decode($json);
echo $obj->start_address;
echo $obj->end_address;
echo $obj->duration;
echo $obj->distance;
echo $station_from_coord;
echo $station_to_coord;


foreach ($obj->distance as $dist) {
    $kmdist = $dist->value;
    $kmtext = $dist->text;

     // Connects to your database 
    $sql_findsim = "SELECT id, station_from_id, station_to_id, station_from_coord, station_to_coord FROM 2014_distances WHERE id=" . mysql_real_escape_string($i). "";
    $handle = mysql_query($sql_findsim);
    $row = mysql_fetch_row($handle);
    $sql = "UPDATE 2014_distances SET km_walk = " . mysql_real_escape_string($kmdist). ", km_walk_text = " . mysql_real_escape_string($kmtext). "";
    $result = mysql_query($sql);

}

foreach ($obj->duration as $dur) {
    $secdur = $dur->value;
    $sectext = $dur->text;

    $sql_findsim1 = "SELECT id, station_from_id, station_to_id, station_from_coord, station_to_coord FROM 2014_distances WHERE id=" . mysql_real_escape_string($i). "";
    $handle1 = mysql_query($sql_findsim1);
    $row1 = mysql_fetch_row($handle1);
    $sql1 = "UPDATE 2014_distances SET time_walk = " . mysql_real_escape_string($secdur). ", time_walk_text = " . mysql_real_escape_string($sectext). "";
    $result1 = mysql_query($sql1);

};
mysql_close();


}

*/

?>
<p>*****</p>
<p>End update.</p>
<p>*****</p>
</body>
</html>