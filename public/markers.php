<?php
require(__DIR__ . "/../includes/config.php");

//get all markers from database
$markers = Helpers::query("SELECT * FROM markers");


header("Content-type: application/json");
print(json_encode($markers, JSON_PRETTY_PRINT));

?>