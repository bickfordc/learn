<!DOCTYPE html>
<html>
  <head>
    <title>Setting up database</title>
  </head>
  <body>

    <h3>Setting up...</h3>

<?php // Example 26-3: setup.php
  require_once 'functions.php';

  $sql =<<<EOF
    CREATE TABLE IF NOT EXISTS members 
      (usr varchar(16),
       pass varchar(16));
EOF;
  
  postgres_query($sql);
  
  $sql =<<<EOF
    CREATE TABLE IF NOT EXISTS messages 
      (id SERIAL PRIMARY KEY,
       auth varchar(16),
       recip varchar(16),
       pm char(1),
       time timestamp,
       message varchar(4096));
EOF;
  
  postgres_query($sql);
  
  $sql =<<<EOF
    CREATE TABLE IF NOT EXISTS friends 
      (usr varchar(16),
       friend varchar(16));
EOF;

  postgres_query($sql);
  
  $sql =<<<EOF
    CREATE TABLE IF NOT EXISTS profiles
      (usr varchar(16),
       text varchar(4096));    
EOF;
  
  postgres_query($sql);
  
  function postgres_query($sql) {
    $ret = pg_query($sql);
    if(!$ret){
        echo pg_last_error($db);
    } else {
      echo "$sql<br>Success.<br>";
    }
  }
  
//  createTable('members',
//              'usr varchar(16), pass varchar(16)');
//              
//  createTable('messages',
//              'id SERIAL PRIMARY KEY,
//              auth varchar(16),
//              recip varchar(16),
//              pm char(1),
//              time timestamp,
//              message varchar(4096)');
//  
//  createTable('friends',
//              'usr varchar(16),
//              friend varchar(16)');
//  
//  createTable('profiles',
//              'usr varchar(16),
//              text varchar(4096)');

?>

    <br>...done.
  </body>
</html>
