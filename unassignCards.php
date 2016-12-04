<?php

require_once 'header.php';

if (!$loggedin) 
{
  header("Location: login.php");
}

$error = "";
$pageMsg = "Begin typing a card number to search. <em>TIP: Try typing " .
           "just the last 3 or 4 digits of a card.</em>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cardNumber = sanitizeString($_POST[ 'card' ]);
    
    if (!empty($cardNumber)) {  
        
        if (unassignCard($cardNumber, $errorMsg)) {
            $pageMsg = "Successfully unassigned card " . $cardNumber;
        } else {
            $pageMsg = "Card unassignment failed.<br>" . $errorMsg;
        }
    } else {
        $error = "Provide a card number.";
    }
}

echo <<<_END
<script src="js/autocompleteSold.js"></script>

<p class='pageMessage'>$pageMsg</p>
        
<div class="form">
      <form method='post' action='unassignCards.php' autocomplete='off'>
        <div id='info' class='error'>$error</div>
        <input type='text' placeholder='card number' id='card' name='card' autocomplete='off'>
        <div id="cardresults" class='searchresults'></div>
        <input type='text' placeholder='student' id='student' name='student' autocomplete='off' readonly>
        <button type='submit'>Unassign card</button>
      </form>
    </div>
</body>
</html>
_END;

function unassignCard($cardNumber, &$errorMsg) {
    
    $returnVal = true;
    $errorMsg = "";
    pg_query("BEGIN");
    
    $result = pg_query_params("UPDATE cards SET sold='f' WHERE id=$1", array($cardNumber)); 
    if (!$result) {
       $errorMsg = pg_last_error();
       $returnVal = false;
    }
    
    $result = pg_query_params("UPDATE cards SET card_holder=NULL WHERE id=$1", array($cardNumber));
    if (!$result) {
        $errorMsg .= pg_last_error();
        $returnVal = false;
    }
    
    $result = pg_query_params("DELETE FROM student_cards WHERE card=$1", array($cardNumber));
    if (!$result) {
        $errorMsg .= pg_last_error();
        $returnVal = false;
    }
    
    $result = pg_query("COMMIT");
    if (!$result) {
        $errorMsg .= pg_last_error(); 
        $returnVal = false;
    } 
    
    return $returnVal;
}

