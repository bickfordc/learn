<?php
//select student_cards.card, students.first, students.last, student_cards.student from students join student_cards on students.id=student_cards.student where student_cards.card like '%651 5%';


//include the information needed for the connection to database server. 
// 
require_once 'functions.php';

$key=$_GET['key'];
$array = array();

//$result = queryPostgres("SELECT * FROM cards WHERE replace(replace(id, '-', ''), ' ', '') LIKE '%{$key}%' AND sold = 't'", array()); 

$query = "SELECT student_cards.card, students.first, students.last " .
         "FROM students JOIN student_cards ON students.id=student_cards.student " .
         "WHERE replace(replace(student_cards.card, '-', ''), ' ', '') LIKE '%{$key}%'";
         
$result = queryPostgres($query, array());

while($row = pg_fetch_array($result))
{
    $array[] = $row['card'] . "|" . $row['first'] . " " . $row['last'];
    //$array[$row['card']] = $row['first'] . " " . $row['last'];
}
echo json_encode($array);

?>