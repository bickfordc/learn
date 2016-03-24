<?php

  require_once 'header.php';
  
  if (isset($_POST['email']))
  {
    $email = sanitizeString($_POST['email']);
    
    $result = queryPostgres("SELECT * FROM members WHERE usr=$1", array($email));

    if (pg_num_rows($result)) {
        
        require 'vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
        
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->SMTPDebug = 2; // 2
        $mail->Debugoutput = 'html';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('MB_EMAIL');
        $mail->Password = getenv('MB_EMAIL_PW');
        $mail->setFrom($mail->Username, 'Grocery Cards');
        //$mail->addReplyTo("Do not reply");
        $mail->addAddress($email);
        $mail->Subject = 'Password reset request';
        
        $resetCode = genResetCode();
        $targetUrl = getTargetUrl($resetCode, $email);
        $mail->Body = genHtmlMessage($targetUrl);      
        $mail->isHTML(true);
        $mail->AltBody = genPlainTextMessage($targetUrl);
        
        storeResetRequest($resetCode, $email);
        
        if (!$mail->send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
            echo "Message sent!";
        }
        
    } else {
        $error = "<span class='error'>That email does not belong to a registered user.</span><br>";
    }
    
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
  
  function genPlainTextMessage($targetUrl)
  {   
      $msg = "We received a request to reset your password at Windsor Music Boosters " .
             "Grocery Card Management site. Please follow the link below to continue.\n\n" .
             $targetUrl . "\n\nThe link is for one time use only and will expire after 20 minutes.";
      
      return $msg;
  }
  
  function genResetCode() 
  {
      $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
      $charsLen = strlen($chars);
      $codeLen = 6; 
      
      $code = "";
      for ($i = 0; $i < $codeLen; $i++)
      {
          $code .= $chars[mt_rand(0, $charsLen - 1)];
      }
      
      return $code;
  }
  
  function storeResetRequest($resetCode, $email)
  {
      queryPostgres("INSERT INTO reset_requests (code, usr) VALUES($1, $2)", array($resetCode, $email));
  }
  
  echo <<<_END
    <div class="forgot-page">
      <div class="form">
       <form id='emailForm' class='login-form' method='post' action='forgotPassword.php'>
        <p>A reset code will be sent to your email address.</p>
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
