<?php
require_once "ApiRest/RestAPI.php";

header('Content-Type: application/json');
header('Access-Control-Allow-Headers: Content-Type, enctype, token');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

$restAPI = new RestAPI("localhost", "myDB", "root", "");
// You can add an argument to improve the security of your session like this :
//$restAPI = new RestAPI("localhost", "myDB", "root", "", "m*;rO)P7^)3'k[F'S~h0Lx7{zN%`6S");

Rest::configureUploadDir("rest-upload");
echo $restAPI->handleRequest();