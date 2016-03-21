<?php

  require_once 'header.php';
  
  if (isset($_GET['code']) && isset($_GET['user']))
  {
      $resetCode = sanitizeString($_GET['code']);
      echo $resetCode;
      
      $allegedUser = sanitizeString($_GET['user']);
      echo $allegedUser;
  }
  else 
  {
      echo "<div class=diag>Invalid request. " .
           "Click <a href='forgotPassword.php'>here</a> to reset your password.</div>";
      exit();
  }
    echo <<<_END
    <div class="reset-page">
      <div class="form">
       <form id='resetForm' class='login-form' method='post' action='changePassword.php?code=$resetCode&user=$allegedUser'>
        <div id='info'>$error</div>
        <input id='pw1' type="password" placeholder="New password" name='password1' value='$password1'/>
        <input id='pw2' type="password" placeholder="Reenter new password" name='password2' value='$password2'/>
        <button type='submit' id='send'>Reset password</button>
       </form>
      </div>
    </div>
 
_END;
?>

    <br></div>
  </body>
</html>
