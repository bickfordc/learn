<?php // Example 26-7: login.php
  require_once 'header.php';
  //echo "<div class='main'><h3>Please enter your details to log in</h3>";
  $error = $user = $pass = "";

  if (isset($_POST['user']))
  {
    $user = sanitizeString($_POST['user']);
    $pass = sanitizeString($_POST['pass']);
    
    if ($user == "" || $pass == "")
        $error = "Not all fields were entered<br>";
    else
    {
      $token = getToken($pass);
      $result = queryPostgres("SELECT usr,pass FROM members
        WHERE usr=$1 AND pass=$2", array($user, $token));

      if (pg_num_rows($result) == 0)
      {
        $error = "<span class='error'>Username/Password
                  invalid</span><br><br>";
      }
      else
      {
        // Login succeded. Set session wide variables and go to the members page
        $_SESSION['user'] = $user;
        $_SESSION['pass'] = $pass;
        header("Location: members.php?view=$user");
      }
    }
  }

  echo <<<_END
    <div class="login-page">
     <div class="form">
      <form class="login-form" method='post' action='login.php'>$error
       <input type="text" placeholder="email" name='user' value='$user'/>
       <input type="password" placeholder="password" name='pass' value='$pass'/>
       <button>login</button>
       <p class="message"><a href="#">I forgot my password</a></p>
      </form>
     </div>
    </div>
_END;
//    <form method='post' action='login.php'>$error
//    <span class='fieldname'>Username</span><input type='text'
//      maxlength='16' name='user' value='$user'><br>
//    <span class='fieldname'>Password</span><input type='password'
//      maxlength='16' name='pass' value='$pass'>
//_END;
?>

<!--    <br>
    <span class='fieldname'>&nbsp;</span>
    <input type='submit' value='Login'>
    </form>-->
    <br></div>
  </body>
</html>
