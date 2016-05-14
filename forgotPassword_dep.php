<?php

  require_once 'header.php';
  
  if (isset($_POST['email']))
  {
    $email = sanitizeString($_POST['email']);
             
    $resetCode = genResetCode();
        
    // Store request and send email only if user with that email exists.
    if (storeResetRequest($resetCode, $email))
    {
        require 'vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
        
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->SMTPDebug = 2; // 0 for production, 2 for debug
        $mail->Debugoutput = 'html';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('MB_EMAIL');
        $mail->Password = getenv('MB_EMAIL_PW');
        $mail->setFrom($mail->Username, 'Grocery Cards');
        $mail->addAddress($email);
        $mail->Subject = 'Password reset request';
        $mail->Body = genPlainTextMessage($resetCode);
            
        if (!$mail->send()) 
        {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } 
    }
    // Go to next page regardless. Give no indication of whether the user exists.  
    header("Location: resetPassword.php?email=$email");
  }

  function getTargetUrl($resetCode, $email) 
  {
      $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      $subUrl = substr($url, 0, strrpos($url, '/') + 1);

      return $subUrl . "changePassword.php?code=$resetCode&user=$email";
  }
  
  function genHtmlMessage($targetUrl)
  {   
      $msg = "<!DOCTYPE html><html><head></head><body>" .
             "<p>We received a request to reset your password at Windsor Music Boosters " .
             "Grocery Card Management site. Please follow the link below to continue.</p>" .
             "<a href='$targetUrl'>$targetUrl</a>" .
             "<p>The link is for one time use only and will expire after 20 minutes.</p>" .
             "</body></html>";
      
      return $msg;
  }
  
  function genPlainTextMessage($resetCode)
  {   
      $msg = "We received a request to reset your password at Windsor Music Boosters " .
             "Grocery Card Management site. Please use the following code.\n" .
             "The code is for one time use only and will expire after 20 minutes.\n\n\t" .
             $resetCode;
      
      return $msg;
  }
  
  function genResetCode() 
  {
      $chars = "ABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
      $charsLen = strlen($chars);
      $codeLen = 8; 
      
      $code = "";
      for ($i = 0; $i < $codeLen; $i++)
      {
          $code .= $chars[mt_rand(0, $charsLen - 1)];
      }
      
      return $code;
  }
  
  function storeResetRequest($resetCode, $email)
  {
      $userExists = false;
      
      // Do we have a user with that email?
      $result = queryPostgres("SELECT * FROM members WHERE usr=$1", array($email));
      if (pg_num_rows($result) > 0) 
      { 
        // Yes. Add the request to the database.
        // A row added to this table automatically gets an expiration 20 minutes from now.
        // It also triggers deletion of any expired rows.
        queryPostgres("INSERT INTO reset_requests (code, usr) VALUES($1, $2)", array($resetCode, $email));
        
        // Store the requesting email address in a session variable
        // so we can validate later when they enter the reset code.
        $_SESSION["userRequestingReset"] = $email;
        
        $userExists = true;
      }
      
      return $userExists;
  }
  
  echo <<<_END
    <p class='pageMessage'>Enter the email address used as your account on this site.<br>
    A message with a reset code will be sent to the email address.</p>
    <div class="forgot-page">
      <div class="form">
       <form id='emailForm' class='login-form' method='post' action='forgotPassword.php'>
        <div id='info'>$error</div>
        <input id='email' type="text" placeholder="email" name='email' value='$email'/>
        <button type='submit' id='send'>Send</button>
       </form>
      </div>
    </div>
 
_END;

?>

    <br></div>
  </body>
</html>
