<?php

  require_once 'header.php';
  
    echo <<<_END
    <div class="reset-page">
      <div class="form">
       <form id='resetForm' class='login-form' method='post' action='changePassword.php'>
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
