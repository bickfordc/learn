<?php

    require_once 'header.php';
    
    $pageMsg;
    
    if ($_FILES)  
    {    
        $name = $_FILES['filename']['tmp_name']; 
        
        $type = $_FILES['filename']['type'];
        if ($type != "text/csv" && $type != "application/vnd.ms-excel")
        {
            echo "That file type was $type, not text/csv.";
            exit();
        }
                  
        $file = fopen($name, "r");
        if ($file == NULL)
        {
            die("Could not open file $name");
        }
 
        $firstLine = true;
        
        $cards = array();
        $error = "";
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
                $error = "Card number " . $cardNumber . " is invalid. Card numbers must have 19 digits.";
                break;
            }
            
           if (calculateCardType($strippedCardNumber) == NULL) {
                $error = "Card number " . $cardNumber . " is invalid. King Soopers cards begin with 6006,"
                        . " and Safeway cards begin with 6039.";
            }
            
            $cards[] = $strippedCardNumber;
        }
        fclose($file);
            
        $isSold = 'f';
        foreach ($cards as $card) {
            
            $donorCode = calculateCardType($card);
            $formattedCardNumber = formatCardNumber($card, $donorCode);
            
            queryPostgres("INSERT INTO cards (id, sold, donor_code) VALUES ($1, $2, $3)",
                    array($formattedCardNumber, $isSold, $donorCode));
        }
               
    } 
    
    
    echo <<<_END
    <div class="container">
        <div style="float:left;margin:10px">
          <img src='img/excel-example.png'>
        </div>
        <div style="float:left;width:467px;height:423px;margin:10px"> 
          <p>To add new cards in bulk, use Excel to create a single column of 
             card numbers as shown on the left. Save the file as 
             CSV (Comma delimited) (*.csv)<br><br>
             You may also use any text editor and place one card number on each 
             line. Save the file as .csv
          </p>
        </div>
        <div style="float:left;width:467px;height:423px;margin:10px"> 
          <div class="form" >
            <p style="text-align:center">Select the .csv file</p>
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
        
        if (substr($strippedCardNumber, 0, 4) == "6006") {
                $cardType = "KS";  
        }
        elseif (substr($strippedCardNumber, 0, 4) == "6039") {
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
?>

    <br></div>
  </body>
</html>

