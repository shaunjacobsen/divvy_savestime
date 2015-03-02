<?php
    $username = "root"; 
    $password = "root";   
    $host = "localhost";
    $database = "divvy";
    
    $server = new PDO('mysql:host=localhost;dbname=divvy', $username, $password);

    // Query for trips
    

    $myquery = "
    SELECT id,count,station_pair AS stn_pr,station_from_id AS stn_f, station_to_id AS stn_t, km_transit, time_transit, transit_type, transit_line, km_bike, median_time_bike as time_bike, savings_transit_real as savings_transit, savings_transit_real * count AS total_savings
    FROM 2014_distances
	WHERE id < 5001
    LIMIT 9000
    ";

    

    // Query for stations

    /*

    $myquery = "
    SELECT id, name, latitude, longitude
    FROM 2014_stations
    WHERE id > 0
    ";

    */

    // Query for bus routes

    /*

    $myquery = "
    SELECT route, name, color
    FROM ctabuses
    WHERE id > 0
    ";

    */

    $result = $server->query($myquery);
    
    if ( ! $result ) {
        echo 'error';
        die;
    }
    
    $data = array();


    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $result->fetch(PDO::FETCH_ASSOC);
    }

    $popData = array_pop($data);
    
    $jsondata = json_encode($data, JSON_NUMERIC_CHECK);

    echo stripslashes($jsondata);

    /*

    $sqlInsert = "INSERT INTO queries (d1, d2, ip) VALUES (:d1, :d2, :ip)";

    $q = $server->prepare($sqlInsert);
    $q->execute(array(':d1'=>$con1,
                      ':d2'=>$con2,
                      ':ip'=>$ipaddress));

    $result->closeCursor();

    $server = null;

    */

?>