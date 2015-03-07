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

<?php

// Establish global variables and variables for call to Google Directions API

$i = 1;						// default value for i, in case it is not established below.
$deptime = 1426528800; 		// the departure time (in UNIX timestamp, i.e. seconds since 01/01/1970 – this value must be after today's date)
$mode = "bicycling";			// mode to get directions in (walking, bicycling, transit, driving)
$mode_mod = "bike";		// set to the same as $mode but with the following: walk, bike, transit, drive

// Google Developer API key

//$apiKey = "AIzaSyC5luicTwTrB6k8PWJCN2GPsSjS9p2K6do";
$apiKey = "AIzaSyDWV2CbYHKq48Up0XbYoksn-wElbkPixt8";

// Connect to database

	mysql_connect("localhost", "root", "root") or die(mysql_error()); mysql_select_db("divvy") or die(mysql_error());

	echo "<h1>Calculating Directions</h1><h3>This script gets transit directions between two points (Divvy stations)<br />and updates the 2014_distances database with details of the transit directions.<br />It will run until the specified id, or until the Google Developer API request limit is reached.</h3><br /><br />*****<br /><br />";

// Start the loop to get all information

for ($i=0;$i<=30000;$i++) {

	$sql_getid = "SELECT km_" . $mode_mod . ", time_" . $mode_mod . ", id, transit_type, transit_line FROM 2014_distances WHERE km_" . $mode_mod . " IS NULL ORDER BY id";
	$query_id = mysql_query($sql_getid);
    $id_row = mysql_fetch_row($query_id);
    $i = $id_row[2];

//if ($i>1) {


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

	$xml = simplexml_load_file("https://maps.googleapis.com/maps/api/directions/xml?origin=" . $station_from_coord . "&destination=" . $station_to_coord . "&departure_time=" . $deptime . "&mode=" . $mode . "&language=en&key= " . $apiKey . "") or die("Error: cannot create xml object.");
	foreach($xml->xpath('.//leg') as $leg) {
		$attributes = $leg->attributes();
		echo $attributes['duration'] . "\n";
	}

	// If getting transit directions, find the types and line/route of each leg of the journey

	if ($mode == "transit") {

		$transit_vehicle_array = array();
		$transit_line_array = array();
		$transit_type_array = array();

		// Get the transit directions (including walking) and add them to an array with the steps listed

		echo "Step method: ";

		foreach($xml->xpath('route/leg/step/travel_mode') as $transit_steps) {
			array_push($transit_vehicle_array, $transit_steps);
			echo $transit_steps . ", ";
		} 
		foreach($xml->xpath('route/leg/step/transit_details/line/name') as $line_name) {
			array_push($transit_line_array, $line_name);
			}
		foreach($xml->xpath('route/leg/step/transit_details/line/vehicle/type') as $line_type) {
			array_push($transit_type_array, $line_type);
			}

		echo "<br />";
		
		// Count the number of steps in the transit directions (includes walking)

		$number_steps = count($transit_vehicle_array);
		echo $number_steps . " steps.";

		// If there is only one step and it is not TRANSIT, set the transit vehicle type to WALK

		if ($number_steps == 1) {
			if ($transit_vehicle_array[0] !== "TRANSIT") { 	
			$transit_type = "WALK";
			}
		} else {
		// Remove all non-transit steps (basically only WALKING)
			for ($v=0;$v<$number_steps;$v++) {
				if (($vkey = array_search("WALKING", $transit_vehicle_array)) !== false) {
					unset($transit_vehicle_array[$vkey]);
			}
		}
			$transit_type = "TRANSIT";
		}

	// Split the arrays

	$insert_vehicles = implode(",",$transit_line_array);
	echo "<br />Line(s): " . $insert_vehicles;

	$insert_types = implode(",",$transit_type_array);
	echo "<br />Method(s): " . $insert_types;

	if($transit_type == "WALK") {
		$insert_types = "WALK";
	}

	echo "<br /> Overll mode used: " . $transit_type . "<br />";

	} else {
		// If the entire trip is not transit, it ignores the arrays and ensures existing transit directions are not overwritten.
		$transit_vehicle_array = NULL;
		$insert_types = $id_row[3];
		$insert_vehicles = $id_row[4];
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

	// Only if the API server is responding will the values update.

	if ($xml->status != "OK") {

		$duration_name = NULL;
		$duration_seconds = NULL;
		$distance_name = NULL;
		$distance_metres = NULL;
		echo "<br /><span style=\"color:#FFA500;\">Could not connect to API; record not updated.</span>";

		exit(); // exits the script

	} else {

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

	    $sql = "UPDATE `2014_distances` SET km_" . $mode_mod . " = " . $distance_metres. ", time_" . $mode_mod . " = " . $duration_seconds . ", transit_line = \"" . addslashes($insert_vehicles) . "\", transit_type = \"" . $insert_types . "\" WHERE id =" . $i . "";
	    
	    if (mysql_query($sql) === TRUE) {
	    	echo "<span style=\"color:#32CD32;\">Successfully updated.</span><br />";
	    } else {
	    	echo "<span style=\"color:#ff0000;\">Error updating row.</span><br />" . mysql_error() . "";
	    }

	}



echo "<br />***<br /><br />";

usleep(500000);

//} else {
//	echo "FATAL ERROR.";
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