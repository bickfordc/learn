<?php

    require_once 'header.php';
    
    $successMsg;
    $errorMsg;
    $formError;
    $numCardsAdded;
    
    if ($_FILES)  {    
        
        try {
            $name = $_FILES['filename']['tmp_name']; 
            if (empty($name)) {
                throw new Exception("Please select a file.");
            }

            $type = $_FILES['filename']['type'];
            if ($type != "text/csv" && $type != "application/vnd.ms-excel" && $type != "text/plain")
            {
                throw new Exception("That file type was $type, not text/csv.");
            }

            $file = fopen($name, "r");
            if ($file == NULL)
            {
                throw new Exception("Could not open file $name");
            }

            $cards = array();
            while (!feof($file))
            {
                $row = fgetcsv($file, 300, ",");

                $cardNumber = $row[0];

                if ($cardNumber == NULL) {
                    continue;
                }

                // strip anything but digits
                $strippedCardNumber = preg_replace("/[^0-9]/", "", $cardNumber);
                if (strlen($strippedCardNumber) != 19) {
                    throw new Exception("Card number " . $cardNumber . " is invalid. Card numbers must have 19 digits.");
                    break;
                }

               if (calculateCardType($strippedCardNumber) == NULL) {
                    throw new Exception("Card number " . $cardNumber . " is invalid. King Soopers cards begin with 6006,"
                            . " and Safeway cards begin with 6039.");
                }

                $cards[] = $strippedCardNumber;
            }
            fclose($file);

            $numCardsAdded = commitCards($cards);
            $successMsg = "Successfully addded " . $numCardsAdded . " cards.";            
        } 
        catch(Exception $e) {
            $errorMsg = $e->getMessage() . "<br>" . "No cards were added.";
        }
    } 
    
    if (!empty($errorMsg)) {
        echo "<p class='errorMessage pageMessage'>$errorMsg</p>";
    }
    else if (!empty($successMsg)) {
        echo "<p class='successMessage pageMessage'>$successMsg</p>";
    }
    
    echo <<<_END
    <div class="container">
        <div style="float:left;margin:10px">
          <img src='img/excel-example.png'>
        </div>
        <div style="float:left;width:467px;height:823px;margin:10px"> 
          <p>To add new cards in bulk, use Excel to create a single column of 
             card numbers as shown on the left. Save the file as 
             CSV (Comma delimited) (*.csv)<br><br>
             Excel has an annoying habit of changing any string of digits with 
             more than 12 digits to scientific notation. To avoid this, add a space
             after the 12th digit of a Safeway card number like this;<br>
             603953500106 2607985<br><br>
             With King Soopers cards, enter them with spaces just as they 
             appear on the card like this;<br>
             6006495903 177 095 385<br><br>
             Instead of Excel, you may also use any text editor and place one card number 
             on each line. Save the file as .csv
          </p>
        </div>
        <div style="float:left;width:467px;height:423px;margin:10px"> 
          <div class="form" >
            <p style="text-align:center">Select the .csv file</p>
            <p class='error'>$formError</p>
            <form method='post' action='newCards.php' enctype='multipart/form-data'>    
              <input type='file' name='filename' size='10'>    
              <button type='submit'>Upload</button>   
            </form>
          </div>
        </div>
    </div>
_END;
    
    function calculateCardType($cardNumber) {
        
        $cardType = NULL;
        
        if (substr($cardNumber, 0, 4) == "6006") {
                $cardType = "KS";  
        }
        elseif (substr($cardNumber, 0, 4) == "6039") {
                $cardType = "SW";  
        }
        
        return $cardType;
    }

    function formatCardNumber($cardNumber, $cardType) {
        
        $formattedCardNumber = $cardNumber;
        
        // Format King Soopers cards as 10 digits, space, 3 digits, space, 3 digits, space, 3 digits
        // 6006495903 177 095 385
        if ($cardType == "KS") {
            $formattedCardNumber = substr($cardNumber, 0, 10) . " " .
                                   substr($cardNumber, 10, 3) . " " .
                                   substr($cardNumber, 13, 3) . " " .
                                   substr($cardNumber, 16, 3);
        }
        // Format Safeway cards as 12 digits, space, 7 digits
        // 603953500106 5471470
        elseif ($cardType == "SW") {
            $formattedCardNumber = substr($cardNumber, 0, 12) . " " .
                                   substr($cardNumber, 12, 7);
        }
         
        return $formattedCardNumber;  
    }
    
    function commitCards($cards) {
        
        $numCardsAdded = 0;
        $isSold = 'f';
        
        if (!pg_query("BEGIN")) {
            throw new Exception(pg_last_error());
        }
        
        foreach ($cards as $card) {

            $donorCode = calculateCardType($card);
            $formattedCardNumber = formatCardNumber($card, $donorCode);

            if (!pg_query_params("INSERT INTO cards (id, sold, donor_code) VALUES ($1, $2, $3)",
                    array($formattedCardNumber, $isSold, $donorCode))) {
                
                $error = pg_last_error();
                pg_query("ROLLBACK");
                throw new Exception($error);
            }
            $numCardsAdded++;
        }
        if (!pg_query("COMMIT")) {
            throw new Exception(pg_last_error());
        }
        
        return $numCardsAdded;
    }
?>

    <br></div>
  </body>
</html>

