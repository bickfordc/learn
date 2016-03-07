<?php

  $dbUrl = getenv('DATABASE_URL');
  
  if ($dbUrl) {  
    $dbOpts = parse_url($dbUrl);
    $host        = "host=".$dbOpts['host']; 
    $port        = "port=".$dbOpts['port'];
    $dbname      = "dbname=".ltrim($dbOpts['path'], '/');        
    $credentials = "user=".$dbOpts['user']." password=".$dbOpts['pass'];    
  } else {
    die("Environment variable DATABASE_URL not found");
  }
  
  
  $appname = "Boosters"; 

  $db = pg_connect("$host $port $dbname $credentials");
  
  if (!$db) {
      die("Unable to open database : " . pg_last_error());
  } 
    
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
    $var = strip_tags($var);
    $var = htmlentities($var);
    return stripslashes($var);
  }

  function getToken($pw) 
  {
      $salt1="35=(%)";
      $salt2="Git76";    
      return hash("ripemd128", "$salt1$pw$salt2");
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
