<?php
$server="localhost";
$user="u776997507_linya_user";
$pass="Linyathesis2025";
$dbname="u776997507_linya_db";
$conn = new mysqli($server,$user,$pass,$dbname);

if($conn->connect_error){
  die("Unable to Connect to the Server ".$conn->connect_error);
}