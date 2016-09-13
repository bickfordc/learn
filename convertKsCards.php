<?php

require_once 'functions.php';

// drop the constraint;
$sql = "ALTER TABLE student_cards DROP CONSTRAINT student_cards_card_fkey";
$result = queryPostgres($sql, array());

// get all the KS cards
$sql = "select * from cards where donor_code = 'KS' order by id";
$result = queryPostgres($sql, array());

while($row = pg_fetch_array($result)) {
    
    $oldcard = $row['id'];
    // only process the 'short' ones
    if (strlen($oldcard) >= 19) {
        continue;
    }
    
    $newcard = modifyKingSoopersCardNumber($oldcard);
    
    $sql = "update cards set id = $1 where id = $2";
    queryPostgres($sql, array($newcard, $oldcard));
    
    // if the card has not been sold (has no entry in student_cards) this will have no effect
    $sql = "update student_cards set card = $1 where card = $2";
    queryPostgres($sql, array($newcard, $oldcard));
    
}
// put the constraint back
$sql = "ALTER TABLE student_cards ADD CONSTRAINT student_cards_card_fkey FOREIGN KEY (card) REFERENCES cards (id)";
$result = queryPostgres($sql, array());

function modifyKingSoopersCardNumber($cardNumber) 
{
    $modifiedCardNumber = $cardNumber;

    // strip everything but digits
    $strippedCardNumber = preg_replace("/[^0-9]/", "", $cardNumber);

    // Add the prefix 
    $strippedCardNumber = "60064959" . $strippedCardNumber;

    if (strlen($strippedCardNumber) == 19) 
    {
        $modifiedCardNumber = formatCardNumber($strippedCardNumber, "KS");
    }

    return $modifiedCardNumber;
}
