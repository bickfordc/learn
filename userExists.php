<?php 
  require_once 'functions.php';

  if (isset($_POST['user']))
  {
    $user   = sanitizeString($_POST['user']);
    $result = queryPostgres("SELECT * FROM members WHERE usr=$1", array($user));

    if (pg_num_rows($result)) {
        echo "true";
    } else {
        echo "false";
    }
  }
?>
