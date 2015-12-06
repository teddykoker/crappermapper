<?php

$config = array(
	"database" => array(
		"host" => "localhost",
		"name" => "toiletfinder",
		"username" => "root",
		"password" => "toiletpaper"
	)
);

ini_set("display_errors", true);
error_reporting(E_ALL);

require("helpers.php");
Helpers::init($config);

?>