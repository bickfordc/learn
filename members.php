<?php 
  require_once 'header.php';

  if (!$loggedin) die();

  echo "<div class='main'>";

  if (isset($_GET['view']))
  {
    $view = sanitizeString($_GET['view']);
    
    if ($view == $user) $name = "Your";
    else                $name = "$view's";
    
    echo "<h3>$name Profile</h3>";
    showProfile($view);
    echo "<a class='button' href='messages.php?view=$view'>" .
         "View $name messages</a><br><br>";
    die("</div></body></html>");
  }

  if (isset($_GET['add']))
  {
    $add = sanitizeString($_GET['add']);

    $result = queryPostgres("SELECT * FROM friends WHERE usr=$1 AND friend=$2", array($add, $user));
    if (pg_num_rows($result) == 0)
      queryPostgres("INSERT INTO friends VALUES ($1, $2)", array($add, $user));
  }
  elseif (isset($_GET['remove']))
  {
    $remove = sanitizeString($_GET['remove']);
    queryPostgres("DELETE FROM friends WHERE usr=$1 AND friend=$2", array($remove, $user));
  }

  $result = queryPostgres("SELECT usr FROM members ORDER BY usr", array());
  $num    = pg_num_rows($result);

  echo "<h3>Other Members</h3><ul>";

  for ($j = 0 ; $j < $num ; ++$j)
  {
    $row = pg_fetch_array($result);
    if ($row['usr'] == $user) continue;
    
    echo "<li><a href='members.php?view=" .
      $row['usr'] . "'>" . $row['usr'] . "</a>";
    $follow = "follow";

    $result1 = queryPostgres("SELECT * FROM friends WHERE
      usr=$1 AND friend=$2", array($row['usr'], $user));
    $t1      = pg_num_rows($result1);
    $result1 = queryPostgres("SELECT * FROM friends WHERE
      usr=$1 AND friend=$2", array($user, $row['usr']));
    $t2      = pg_num_rows($result1);

    if (($t1 + $t2) > 1) echo " &harr; is a mutual friend";
    elseif ($t1)         echo " &larr; you are following";
    elseif ($t2)       { echo " &rarr; is following you";
      $follow = "recip"; }
    
    if (!$t1) echo " [<a href='members.php?add="   .$row['usr'] . "'>$follow</a>]";
    else      echo " [<a href='members.php?remove=".$row['usr'] . "'>drop</a>]";
  }
?>

    </ul></div>
  </body>
</html>
