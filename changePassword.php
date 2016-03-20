<?php

    require_once 'header.php';

    if (!$loggedin) die();

    $error = "";    
    
    if (isset($_POST['currentPw']))
    {
        $allegedPw = sanitizeString($_POST['currentPw']);
        $newPw1    = sanitizeString($_POST['newPw1']);       
        $newPw2    = sanitizeString($_POST['newPw2']);  
        
        $userToken = $_SESSION['userToken'];
        $allegedToken  = getToken($allegedPw);
        
        if ($allegedToken != $userToken)
        {
            $error = "Incorrect current password";
            $allegedPw = "";
        }
        else if ($newPw1 != $newPw2)
        {
            $error = "Passwords do not match.";
        }
        else if (strLen($newPw1) < 8)
        {
            $error = "New password must be at least 8 characters.";
        }
        else
        {
            $result = queryPostgres("UPDATE members SET pass=$1 WHERE usr=$2",
               array(getToken($newPw1), $user));
            
            destroySession();
            header("Location: login.php");
        }
 
    }    
    
    echo <<<_END
    <script src='validate.js'></script>
    <div class="reset-page">
      <div class="form">
       <form id='resetForm' class='reset-form' method='post' action='changePassword.php'>
        <a href='index.php' class='cancelX'>X</a>      
        <input id='current' type="password" placeholder="Current password" name='currentPw' value='$allegedPw'/>
        <input id='pw1' type="password" placeholder="New password" name='newPw1'/>
        <input id='pw2' type="password" placeholder="Reenter new password" name='newPw2'/>
        <div id='info' class=error>$error</div>
        <button type='submit' id='send'>Change password</button>
       </form>
      </div>
    </div>
_END;

?>

    <br></div>
  </body>
</html>