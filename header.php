<?php 
  session_start();

  echo "<!DOCTYPE html>\n<html><head>";

  require_once 'functions.php';

  $userstr = ' (Guest)';

  if (isset($_SESSION['user']))
  {
    $user     = $_SESSION['user'];
    $loggedin = TRUE;
  }
  else $loggedin = FALSE;

  echo "<title>$appname$userstr</title><link rel='stylesheet' " .
       "href='styles.css' type='text/css'>"                     .
       "</head><body><center><canvas id='logo' width='624' "    .
       "height='200'>$appname</canvas></center>"                .
       "<script src='https://code.jquery.com/jquery-2.2.1.min.js'></script>" .
       "<script src='javascript.js'></script>";

  if ($loggedin)
  {
    echo "<div class='navigation'>" .
         "<ul class='nav'>"         .
  	   "<li><a href='members.php?view=$user'>Home</a></li>" .
           "<li><a href='members.php'>Members</a></li>" .
           "<li><a href='friends.php'>Friends</a></li>" .
           "<li><a href='messages.php'>Messages</a></li>" .
  	   "<li><a href='#'>$user</a>"  .
  	     "<ul><li><a href='profile.php'>Edit Profile</a></li>" .
             "<li><a href='changePassword.php'>Change Password</a></li>" .
             "<li><a href='logout.php'>Logout</a></li></ul></li>" .
  	 "</ul>" .
         "</div>";

  }
?>
