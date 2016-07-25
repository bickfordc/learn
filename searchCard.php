<?php

//include the information needed for the connection to database server. 
// 
require_once 'functions.php';

$key=$_GET['key'];
$array = array();

//$result = queryPostgres("SELECT * FROM cards WHERE id LIKE '%{$key}%' AND sold = 'f'", array()); 
$result = queryPostgres("SELECT * FROM cards WHERE replace(replace(id, '-', ''), ' ', '') LIKE '%{$key}%' AND sold = 'f'", array()); 

while($row = pg_fetch_array($result))
{
    $array[] = $row['id'];
}
echo json_encode($array);

?>