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
        $mail->SMTPDebug = 0; // 2
        $mail->Debugoutput = 'html';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;
        $mail->Username = 'wmbgrocerycards@gmail.com';
        $mail->Password = '----';
        $mail->setFrom('wmbgrocerycards@gmail.com', 'Grocery Cards');
        //$mail->addReplyTo($address)
        $mail->addAddress($email);
        $mail->Subject = 'Testing 1,2,3...';
        $mail->isHTML(false);
        //$mail->msgHTML(file_get_contents('monthlymail.html'));
        $mail->Body = "Hey you.";
        $mail->AltBody = 'This is a plain-text message body';
        $mail->addAttachment('carlton.jpg');
        
        if (!$mail->send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
            echo "Message sent!";
        }
        
    } else {
        $error = "<span class='error'>That email does not belong to a registered user.</span><br>";
    }
    
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
