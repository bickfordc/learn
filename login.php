<?php 
  require_once 'header.php';
 
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
        $_SESSION['userToken'] = $token;
        header("Location: members.php?view=$user");
      }
    }
  }

  // May have arrived here due to a password reset or change. 
  // If so, the query string will have a message to be displayed.
  $msg = $_GET['msg'];

  echo <<<_END
    <div class="login-page">
     <div class="form">
      <form class="login-form" method='post' action='login.php'>$error
       <p>$msg</p>
       <input type="text" placeholder="email" name='user' value='$user'/>
       <input type="password" placeholder="password" name='pass' value='$pass'/>
       <button>login</button>
       <p class="message"><a href="forgotPassword.php">Forgot password?</a></p>
      </form>
     </div>
    </div>
_END;

?>

    <br></div>
  </body>
</html>
