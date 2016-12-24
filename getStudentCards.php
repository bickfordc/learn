<?php

//include the information needed for the connection to database server. 
// 
require_once 'functions.php';

// get the id passed automatically to the request
$id = $_GET['id'];

if (!preg_match("/^\d+$/", $id)) {
    die( "Student id '$id' is not numeric.\n");
}

$sql = "SELECT card FROM student_cards WHERE student=$1 ORDER BY card";
$result = queryPostgres($sql, array($id));

// Set the appropriate header information. 
header("Content-type: text/xml;charset=utf-8");


echo "<?xml version='1.0' encoding='utf-8'?>"; 
echo "<rows>"; 
while($row = pg_fetch_array($result)) {
    echo "<row>"; 
    echo "<cell>". $row['card']."</cell>"; 
    echo "</row>"; 
}
echo "</rows>"; 