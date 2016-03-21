<?php

    require_once 'header.php';

    if (!$loggedin) die();

    $error = "";    
    
    $newPw1 = sanitizeString($_POST['newPw1']);       
    $newPw2 = sanitizeString($_POST['newPw2']);  
    
    if (isset($_POST['code']) && isset($_POST['user']))
    {
        // This is a forgotten password reset request.
        // Validate user, code, and check for code expiration.
        
        $resetCode = sanitizeString($_POST['code']);
        $user = sanitizeString($_POST['user']);
        
        $result = queryPostgres(
            "SELECT code, expiration, usr FROM reset_requests WHERE code=$1 AND usr=$2",
            array($resetCode, $user));
        
        $invalidRequest = false;
        
        if (pg_num_rows($result) == 0) 
        {
            $invalidRequest = true;
        }
        else
        {
            $row = pg_fetch_array($result);
            $OneDayAgo = strtotime("-24 hours");
            $requestExpiration = strtotime($row['expiration']);
            
            if ($requestExpiration < $OneDayAgo)
            {
                $invalidRequest = true;
            }
        }
        
        if ($invalidRequest == true)
        {
            echo "<div class=diag>Invalid request. " .
                "Click <a href='forgotPassword.php'>here</a> to reset your password.</div>";
            exit();
        }
        
        validateAndChangePassword($user, $newPw1, $newPw2, $error);
    }
    
    if (isset($_POST['currentPw']))
    {
        // This is a request from a logged in user to change password. 
        
        $allegedPw = sanitizeString($_POST['currentPw']);
        
        $userToken = $_SESSION['userToken'];
        $allegedToken  = getToken($allegedPw);
        
        if ($allegedToken != $userToken)
        {
            $error = "Incorrect current password";
            $allegedPw = "";
        }
        else
        {
            $user = $_SESSION['user'];
            validateAndChangePassword($user, $newPw1, $newPw2, $error);
        }
    }    

    function validateAndChangePassword($user, $newPw1, $newPw2, &$error)
    {
        if ($newPw1 != $newPw2)
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
            header("Location: login.php?msg=Password change was successful. Please login with your new password.");
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