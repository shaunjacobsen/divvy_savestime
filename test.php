<?php

$number_to_update = 800;

$time_required = time() + ($number_to_update * 3);
$time_now = date("Y-m-d H:i:s");
$end_time = date("Y-m-d H:i:s", $time_required);

echo $end_time;

?>