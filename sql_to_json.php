<?php
    $username = "root"; 
    $password = "root";   
    $host = "localhost";
    $database = "divvy";
    
//    $server = new PDO('mysql:host=localhost;dbname=divvy', $username, $password);

    mysql_connect("localhost", "root", "root") or die(mysql_error()); mysql_select_db("divvy") or die(mysql_error());

    // Query for trips
    
    

    $myquery = "
    SELECT id,count,station_pair AS stn_pr,station_from_id AS stn_f, station_to_id AS stn_t, time_transit as time_t, transit_type, transit_line, transit_line_short, colour, km_bike, median_time_bike as time_bike, savings_transit_real as savings_transit, savings_transit_real * count AS total_savings
    FROM 2014_distances
	WHERE id < 24731 AND savings_transit_real > -600
    ";

    

    // Query for stations

    /*

    $myquery = "
    SELECT id, name, latitude AS lat, longitude AS lon
    FROM 2014_stations
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

    /*

    $result = $server->query($myquery);
    
    if ( ! $result ) {
        echo 'error';
        die;
    }
    
    $data = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $result->fetch(PDO::FETCH_ASSOC);
    }
    
    $jsondata = json_encode($data);

    echo $jsondata;

    */

    $result = mysql_query($myquery) or die('Could not query');

    if(mysql_num_rows($result)){
        echo '[';

        $first = true;
        $row=mysql_fetch_assoc($result);
        while($row=mysql_fetch_array($result, MYSQL_ASSOC)){
            //  cast results to specific data types

            if($first) {
                $first = false;
            } else {
                echo ',';
            }
            echo json_encode($row, JSON_NUMERIC_CHECK);
        }
        echo ']';
    } else {
        echo '[]';
    }

mysql_close();

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