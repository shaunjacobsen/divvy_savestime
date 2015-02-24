<?php

// Load the data from the API via a URL

$mode = "transit";

	$xml = simplexml_load_file("http://maps.googleapis.com/maps/api/directions/xml?origin=41.893525,-87.634163&destination=41.866145,-87.607853&mode=transit&language=en") or die("Error: cannot create xml object.");
	foreach($xml->xpath('.//leg') as $leg) {
		$attributes = $leg->attributes();
		echo $attributes['duration'] . "\n";
	}

	if ($mode == "transit") {

		$transit_vehicle_array = array();
		$transit_line_array = array();
		$transit_type_array = array();

		// Get the transit directions (including walking) and add them to an array with the steps listed

		foreach($xml->xpath('route/leg/step/travel_mode') as $transit_steps) {
			array_push($transit_vehicle_array, $transit_steps);
			echo "Step method: " . $transit_steps . ", <br />";
		} 
		foreach($xml->xpath('route/leg/step/transit_details/line/name') as $line_name) {
			echo "Line: " . $line_name;
			array_push($transit_line_array, $line_name);
			}
		foreach($xml->xpath('route/leg/step/transit_details/line/vehicle/type') as $line_type) {
			echo "Line: " . $line_type;
			array_push($transit_type_array, $line_type);
			}
		
		// Count the number of steps in the transit directions (includes walking)

		$number_steps = count($transit_vehicle_array);
		echo $number_steps . " steps.<br />";

		// If there is only one step and it is not TRANSIT, set the transit vehicle type to WALK

		if ($number_steps == 1) {
			if ($transit_vehicle_array[0] !== "TRANSIT") { 	
			$transit_type = "WALK";
			}
		} else { echo "ok";
		// Remove all non-transit steps (basically only WALKING)
			for ($v=0;$v<$number_steps;$v++) {
				if (($vkey = array_search("WALKING", $transit_vehicle_array)) !== false) {
					unset($transit_vehicle_array[$vkey]);
			}
		}
			$transit_type = "TRANSIT";
		} 

	} else {

		$transit_vehicle_array = NULL;
	}

	foreach ($transit_vehicle_array as $vehicle) {
		echo $vehicle . ", ";
	}
	echo "<br>";
	foreach ($transit_line_array as $thisline) {
		echo $thisline . ", ";
	}
	echo "<br>";
	foreach ($transit_type_array as $thistype) {
		echo $thistype . ", ";
	}

	echo "<br /> Overll mode used: " . $transit_type;


?>