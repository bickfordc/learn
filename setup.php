<!DOCTYPE html>
<html>
  <head>
    <title>Setting up database</title>
  </head>
  <body>

    <h3>Setting up...</h3>

<?php // Example 26-3: setup.php
  require_once 'functions.php';

//  createTable('members',
//              'user VARCHAR(16),
//              pass VARCHAR(16),
//              INDEX(user(6))');

  createTable('members',
              'usr varchar(16), pass varchar(16)');
              
  //createIndex('members','usr');

  
//  createTable('messages', 
//              'id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
//              auth VARCHAR(16),
//              recip VARCHAR(16),
//              pm CHAR(1),
//              time INT UNSIGNED,
//              message VARCHAR(4096),
//              INDEX(auth(6)),
//              INDEX(recip(6))');

  createTable('messages',
              'id SERIAL PRIMARY KEY,
              auth varchar(16),
              recip varchar(16),
              pm char(1),
              time timestamp,
              message varchar(4096)');
  
  //createIndex('messages', 'auth');
  //createIndex('messages', 'recip');
  
//  createTable('friends',
//              'user VARCHAR(16),
//              friend VARCHAR(16),
//              INDEX(user(6)),
//              INDEX(friend(6))');

  createTable('friends',
              'usr varchar(16),
              friend varchar(16)');
  
  //createIndex('friends', 'usr');
  //createIndex('friends', 'friend');
  
//  createTable('profiles',
//              'user VARCHAR(16),
//              text VARCHAR(4096),
//              INDEX(user(6))');
  
  createTable('profiles',
              'usr varchar(16),
              text varchar(4096)');
  
  //createIndex('profiles', 'usr');

?>

    <br>...done.
  </body>
</html>
