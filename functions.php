<?php // Example 26-1: functions.php
  $host        = 'host=localhost'; 
  $port        = 'port=5432';
  $dbname      = 'dbname=robin';        
  $credentials = 'user=postgres password=hpinvent';    
     
  $appname = "Boosters"; 

  $db = pg_connect("$host $port $dbname $credentials");
  
  if (!$db) {
      die("Unable to open database : " . pg_last_error());
  } 
  
  //$connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
  //if ($connection->connect_error) die($connection->connect_error);

  function createTable($name, $columns)
  {
    queryPostgres("CREATE TABLE IF NOT EXISTS $1($2)", array($name, $columns));
    echo "Table '$name' created or already exists.<br>";
  }

  function createIndex($table, $columns)
  {
      queryPostgres("CREATE INDEX ON $1($2)", array($table, $columns));
      echo "Index '$table.$columns' created.<br>";
  }
//  function queryMysql($query)
//  {
//    global $connection;
//    $result = $connection->query($query);
//    if (!$result) die($connection->error);
//    return $result;
//  }

//  function queryPostgresNoParam($query)
//  {
//      $result = pg_query($query);
//      if (!$result) die (pg_last_error());
//      return $result;
//  }
  
  function queryPostgres($query, $params)
  {
      $result = pg_query_params($query, $params);
      if (!$result) die (pg_last_error());
      return $result;
  }
  
  function destroySession()
  {
    $_SESSION=array();

    if (session_id() != "" || isset($_COOKIE[session_name()]))
      setcookie(session_name(), '', time()-2592000, '/');

    session_destroy();
  }

  function sanitizeString($var)
  {
    //global $connection;
    $var = strip_tags($var);
    $var = htmlentities($var);
    return stripslashes($var);
    //return pg_escape_literal($var);
  }

  function showProfile($user)
  {
    if (file_exists("$user.jpg"))
      echo "<img src='$user.jpg' style='float:left;'>";

    $result = queryPostgres("SELECT * FROM profiles WHERE usr=$1", array($user));

    if (pg_num_rows($result))
    {
      $row = pg_fetch_array($result);
      echo stripslashes($row['text']) . "<br style='clear:left;'><br>";
    }
  }
?>
