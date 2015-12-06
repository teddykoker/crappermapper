<?php

require(__DIR__ . "/../includes/config.php");

//Data is posted as JSON through AngularJS's $http.post method
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

//check if variables are set
if(!isset($request->lat, $request->lng, $request->name, $request->directions, $request->notes))
{
	http_response_code(400);
	exit;
}
//check lat and long exist and are floats
else if(!is_float($request->lat) || !is_float($request->lng))
{
	http_response_code(400);
	exit;
}
//if any of the fields are empty
else if($request->name == "" || $request->directions == "" || $request->notes == "")
{
	http_response_code(400);
	exit;
}

else
{
	//insert new toilet with the given info
	Helpers::query("INSERT INTO markers (lat, lng, name, directions, notes) VALUES(?, ?, ?, ?, ?)", $request->lat, $request->lng, $request->name, $request->directions, $request->notes);
}


?>

