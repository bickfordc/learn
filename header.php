<?php 
  session_start();

  echo "<!DOCTYPE html>\n<html><head>";

  require_once 'functions.php';

  if (isset($_SESSION['user']))
  {
    $user     = $_SESSION['user'];
    $loggedin = TRUE;
  }
  else $loggedin = FALSE;
  
  echo "<title>$appname</title><link rel='stylesheet' " .
       "href='styles.css' type='text/css'>" .
       "</head><body><center><canvas id='logo' width='624' "    .
       "height='200'>$appname</canvas></center>"                .
       "<script src='https://code.jquery.com/jquery-2.2.1.min.js'></script>" .
       "<script src='javascript.js'></script>";

  if ($loggedin)
  {
        echo "<nav>" .
         "<ul class='nav'>" .
  	   "<li><a href='index.php'>CARDS</a>" .
             "<ul><li><a href='newCards.php'>Add new unsold cards</a></li>" .
             "<li><a href='sellCards.php'>Assign a card to a student</a></li>" .
             "<li><a href='unassignCards.php'>Unassign a card</a></li>" .
             "<li><a href='cardData.php'>Edit cards</a></li>" .
             "</ul></li>" .
           "<li><a href='index.php'>STUDENTS</a>" .
             "<ul><li><a href='studentData.php'>Edit students</a></li>" .
             "</ul></li>" .
           "<li><a href='index.php'>REPORTS</a>" .
             "<ul><li><a href='reloads.php'>Record monthly card reloads</a></li>" .
             "</ul></li>" .
  	   "<li><a href='#'>$user</a>"  .
             "<ul><li><a href='changePassword.php'>Change Password</a></li>" .
             "<li><a href='logout.php'>Logout</a></li></ul></li>" .
  	 "</ul>" .
         "</nav>";
  }
?>
