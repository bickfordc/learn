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
      (usr varchar(32),
       pass varchar(32));
EOF;
  
  postgres_query($sql);
  
  $sql =<<<EOF
    CREATE TABLE IF NOT EXISTS messages 
      (id SERIAL PRIMARY KEY,
       auth varchar(32),
       recip varchar(32),
       pm char(1),
       time timestamp,
       message varchar(4096));
EOF;
  
  postgres_query($sql);
  
  $sql =<<<EOF
    CREATE TABLE IF NOT EXISTS friends 
      (usr varchar(32),
       friend varchar(32));
EOF;

  postgres_query($sql);
  
  $sql =<<<EOF
    CREATE TABLE IF NOT EXISTS profiles
      (usr varchar(32),
       text varchar(4096));    
EOF;
  
  postgres_query($sql);
  
    $sql =<<<EOF
    CREATE TABLE IF NOT EXISTS reset_requests
      (code char(8) PRIMARY KEY,
       usr varchar(32) NOT NULL,
       expiration timestamp NOT NULL DEFAULT NOW() + INTERVAL '20 minutes');    
EOF;
  
    postgres_query($sql);
    
    $sql =<<<EOF
    CREATE OR REPLACE FUNCTION delete_old_reset_requests() RETURNS trigger
      LANGUAGE plpgsql
      AS $$
      BEGIN
        DELETE FROM reset_requests WHERE expiration < NOW();
        RETURN NEW;
      END;
      $$;
EOF;
    
    postgres_query($sql);
    
    $sql =<<<EOF
    CREATE TRIGGER delete_old_reset_requests_trigger 
      AFTER INSERT ON reset_requests
      EXECUTE PROCEDURE delete_old_reset_requests();
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
  
?>

    <br>...done.
  </body>
</html>
