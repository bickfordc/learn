<?php // Example 26-6: checkuser.php
  require_once 'functions.php';

  if (isset($_POST['user']))
  {
    $user   = sanitizeString($_POST['user']);
    $result = queryPostgres("SELECT * FROM members WHERE usr=$1", array($user));

    if (pg_num_rows($result))
      echo  "<span class='taken'>&nbsp;&#x2718; " .
            "This username is taken</span>";
    else
      echo "<span class='available'>&nbsp;&#x2714; " .
           "This username is available</span>";
  }
?>
