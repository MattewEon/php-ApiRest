<?php
require_once "ApiRest/RestAPI.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Headers: Content-Type, enctype, token');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

$restAPI = new RestAPI("localhost", "myDB", "root", "");
Rest::configureUploadDir("rest-upload");
echo $restAPI->handleRequest();