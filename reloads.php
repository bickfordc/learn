<?php

    require_once 'header.php';

    if (!$loggedin) die();

    $fatalError = false;
    
    if ($_FILES)  
    {   
        //print_r($_FILES);
        
        $names = array();
        $tmpNames = array();
        $types = array();
        $messages = array();
        $uploadError = false;
        
        for ($i = 0; $i <= 1; $i++) 
        {
            $messages[$i] = "OK";
            if ($_FILES['file']['error'][$i] === UPLOAD_ERR_OK)
            {
                $names[$i]    = $_FILES['file']['name'][$i];
                $tmpNames[$i] = $_FILES['file']['tmp_name'][$i];
                $types[$i]    = $_FILES['file']['type'][$i];
                if ($types[$i] != "text/csv" && $types[$i] != "application/vnd.ms-excel")
                {
                    $messages[$i] = "The file type was $types[$i], not text/csv.";
                    $uploadError = true;
                }
                if (!$uploadError && $i == 0)
                {
                    if (!validateKingSoopers($tmpNames[$i]))
                    {
                        $messages[$i] = "This does not appear to be a King Soopers statement.";
                        $uploadError = true;
                    }
                }
                if (!$uploadError && $i == 1)
                {
                    if (!validateSafeway($tmpNames[$i]))
                    {
                        $messages[$i] = "This does not appear to be a Safeway statement.";
                        $uploadError = true;
                    }
                }
            }
            else
            {
                $messages[$i] = "File not uploaded.";
                $uploadError = true; 
            }
        }
        
        if ($uploadError)
        {
            $pageMsg = "There was a problem uploading files.<br>" .
                       "File 1 : $messages[0]<br>File 2 : $messages[1]<br>" .
                       "Please try again.";
        }
        else
        {
            $ksCardTotals = processKingSoopers($tmpNames[0]);
            $ksCardsNotFound = array();
            $ksSoldCardTotal = 0;
            $ksUnsoldCardTotal = 0;
            $ksCardData = getCardData($ksCardTotals, $ksCardsNotFound, $ksSoldCardTotal, $ksUnsoldCardTotal);
                      
            $swCardTotals = processSafeway($tmpNames[1]);
            $swCardsNotFound = array();
            $swSoldCardTotal = 0;
            $swUnsoldCardTotal = 0;
            $swCardData = getCardData($swCardTotals, $swCardsNotFound, $swSoldCardTotal, $swUnsoldCardTotal);
            
            $cardsNotFound = array_merge($ksCardsNotFound, $swCardsNotFound);
            if (count($cardsNotFound > 0))
            {
                $pageMsg = "The following grocery cards were not found:<br>";
                foreach ($cardsNotFound as $val)
                {
                    $pageMsg .= $val . "<br>";
                }
            }
            
            //print_r($ksCardData);
            $students = groupCardsByStudent($ksCardData, "ks");
            $students = array_merge($students, groupCardsByStudent($swCardData, "sw"));
            
            genStudentReport($students);
            genNonStudentReport(array($ksCardData, $swCardData));
            
            // This will yield array elements like scripTotals['Carlton Bickford'] => 10.50
            //$scripTotals = processScrip($tmpNames[2]); 
            // Scrip student families not in the scrip_student table
            //$studentFamiliesNotFound = array(); 
            // ScripData records (indexed by studentFamily) include fields total, studentId
            //$scripData = getScripData($scripTotals, $studentFamiliesNotFound);
            
            
        }
        
        //move_uploaded_file($_FILES['file1']['tmp_name'], $name1);    
    }
    else
    {
        $pageMsg = "Select the .csv files received from King Soopers and
        Safeway.<br>If the file is in another form such as .xls or .xlsx, open it 
        with Excel and save as \"CSV (Comma delimited) (*.csv)\"";
    }
    
    function validateKingSoopers($tmpName)
    {
        $isValid = false;
        
        if (($file = fopen($tmpName, "r")) !== false)
        {
            $row = fgetcsv($file, 300, ",");
            $row = fgetcsv($file, 300, ",");
            $row = fgetcsv($file, 300, ","); // Get 3rd line, it is start of real data
            $numFields = count($row);
            $cardNumber = $row[1];           // 2nd field is card number.
            $match = preg_match("/^[0-9]{2}-[0-9]{4}-[0-9]{4}-[0-9]$/", $cardNumber);
            if ($numFields == 6 && $match == 1)
            {
                $isValid = true;
            }
            
            fclose($file);
        }
       
        return $isValid;
    }
    
    function validateSafeway($tmpName)
    {
        $isValid = false;
        
        if (($file = fopen($tmpName, "r")) !== false)
        {
            $row = fgetcsv($file, 300, ",");
            $row = fgetcsv($file, 300, ","); // Get 2nd line, it is start of real data
            $numFields = count($row);
            $cardNumber = $row[2];           // 3rd field is card number.
            $match = preg_match("/^[0-9]{19}$/", $cardNumber);
            if ($numFields == 8 && $match == 1)
            {
                $isValid = true;
            }
            
            fclose($file);
        }
       
        return $isValid;
    }
    
    function processKingSoopers($tmpName)
    {
        $cardTotals = array();
        if (($file = fopen($tmpName, "r")) !== false)
        {
            $line = 0;
            while(($row = fgetcsv($file, 300, ",")) !== false)
            {
                if (++$line < 3)
                {
                    // 3rd line is start of real data
                    continue;
                }
                $transactDate = $row[0];
                $cardNumber = $row[1];
                if ($cardNumber == "")
                {
                    // Ignore any line without a card number
                    continue;
                }
                $amount = handleCurrency($row[5]);
                $cardTotals[$cardNumber] += $amount;
            }     
        }
        else
        {
            $pageMsg = "Could not open file $tmpName";
        }
        return $cardTotals;
        
    }
    
    function processSafeway($tmpName)
    {
        $cardTotals = array();
        if (($file = fopen($tmpName, "r")) !== false)
        {
            $line = 0;
            while(($row = fgetcsv($file, 300, ",")) !== false)
            {
                if (++$line < 2)
                {
                    // 2nd line is start of real data
                    continue;
                }
                $transactDate = $row[4];
                $cardNumber = modifySafewayCardNumber($row[2]);
                if ($cardNumber == "")
                {
                    // Ignore any line without a card number
                    continue;
                }
                $amount = handleCurrency($row[3]);
                $cardTotals[$cardNumber] += $amount;
            }     
        }
        else
        {
            $pageMsg = "Could not open file $tmpName";
        }
        return $cardTotals;
    }
    
    
    // idea for later.  student_scripfamily table. Each student may have multiple scrip families.
    function getCardData($cardTotals, &$cardsNotFound, &$soldCardTotal, &$unsoldCardTotal)
    {
        $cards = array();
        $cardData = array();
        $notFoundCount = 0;
        foreach ($cardTotals as $key => $val)
        {
            $result = queryPostgres("SELECT * FROM cards where id=$1", array($key));
            if (pg_num_rows($result) == 0)
            {
                $cardsNotFound[$notFoundCount] = $key;
                $notFoundCount++;
                return $cards;
            }
            if (pg_num_rows($result) > 1)
            {
                // this should never happen.
                die("Card $key is not unique in database.");
            }
            else
            {
                $row = pg_fetch_array($result);
                $cardData["sold"] = $row["sold"];
                $cardData["cardHolder"] = $row["card_holder"];
                $cardData["total"] = $val;
                $cardData["cardNumber"] = $key;
                if ($cardData["sold"] == "t")
                {
                    $cardData["studentId"] = getStudentIdByCard($key);
                    $soldCardTotal += $val;
                }
                else
                {
                    $unsoldCardTotal += $val;
                }
                $cards[$i++] = $cardData;
            }
        }
        return $cards;  // includes both sold and unsold cards.
    }
    
    // given cards, an array of cardData arrays
    //   cards[0] => 
    //      sold => t
    //      card_holder => Grace Bickford
    //      total => 100.00
    //      cardNumber => 01-2345-6789-0
    //      studentId => 1156
    // produce students, an array keyed by student 'last first' (for ksort)
    // of studentData arrays.
    //   students[Bickford Emma] =>
    //      ksCards => an array of cardData arrays
    //      ksCardsTotal => 150.00
    //      swCards => an array of cardData arrays
    //      swCardsTotal => 300.00
    //      first   => Emma
    //      last    => Bickford
    function groupCardsByStudent($cards, $cardType)
    {
        $students = array();    
        $cardKey = $cardType . "Cards";
        
        foreach($cards as $value)
        {
            if ($value["sold"] == "t")
            {
                // Get data from students table
                $studentId = $value["studentId"];
                $result = queryPostgres("SELECT * FROM students WHERE id=$1", array($studentId));
                $row = pg_fetch_array($result);
                $first = $row["first"];
                $last = $row["last"];
                $studentKey = $last . " " . $first;
                
                if (array_key_exists($studentKey, $students))
                {
                    $students[$studentKey][$cardKey][] = $value;
                }
                else
                {
                    $studKsCards = array($value);
                    $studData = array($cardKey => $studKsCards, "first" => $first, "last" => $last);
                    $students[$studentKey] = $studData;
                }                        
            }
        }
        
        // Calculate student total by card type
        foreach($students as &$studData)
        {
            $sum = 0;
            $cardData = $studData[$cardKey];
            foreach($cardData as $card)
            {
                $sum += $card["total"];
            }
            
            $studData[$cardKey . "Total"] = $sum;
        }       
        
        return $students;
    }
    
    function genStudentReport($students)
    {        
        // print_r($students);
        ksort($students);
        $cardTypes = array("ks", "sw");
        $stores = array("King Soopers", "Safeway");
      
        foreach($students as $value)
        {
            $name = $value["first"] . " " . $value["last"];
            echo "<h2>$name</h2><br>";
            
            for($i=0; $i <=1; $i++)
            {
                $cardKey = $cardTypes[$i] . "Cards";
                $store = $stores[$i];

                if (count($value[$cardKey]) > 0)
                {
                    foreach ($value[$cardKey] as $cardsVal)
                    {
                        $cardNumber = $cardsVal["cardNumber"];
                        $cardHolder = $cardsVal["cardHolder"];
                        $total = $cardsVal["total"];
                        echo "$cardNumber $cardHolder $total <br>";
                    }
                    $key = $cardKey . "Total";
                    echo $store . " Card total for student: $value[$key] <br>";
                }
            }
        }
    }
    
    function genNonStudentReport($cards)
    {
        $storeNames = array("King Soopers", "Safeway");
        $i = 0;
        
        echo "<br><h2>Cards unassociated with a student</h2><br>";
        foreach($cards as $store)
        {
            $count = 0;
            $storeTotal = 0;
            $storeName = $storeNames[$i++];
            foreach($store as $cardData)
            {
                if ($cardData["sold"] == "f")
                {
                    $count++;
                    $cardNumber = $cardData["cardNumber"];
                    $cardHolder = $cardData["cardHolder"];
                    $total = $cardData["total"];
                    $storeTotal += $total;
                    echo "$cardNumber $cardHolder $total <br>";
                }
            }
            if ($count > 0)
            {
                echo "$storeName cards total: $storeTotal";
            }
        }
    }
    
    function getStudentIdByCard($cardNumber)
    {
        $result = queryPostgres("SELECT * FROM student_cards WHERE card=$1", array($cardNumber));
        if (($row = pg_fetch_array($result)) === false)
        {
            $pageMsg = "Card $cardNumber is marked as sold but is not associated with a student.";
            $fatalError = true;
        }
        else
        {
            return $row["student"];
        }
    }
    
    // ($150.75) => -150.75
    // $20       => 20
    // $1,100.25 => 1100.25
    //
    function handleCurrency($moneyString)
    {
        $isNegative = false;
        
        if (substr($moneyString, 0, 1) == "(")
        {
            $isNegative = true;
        }
        
        // strip off parens and $ from ends
        $moneyString = trim($moneyString, "($)");
        
        // strip out commas
        $amount = str_replace(",", "", $moneyString);
        
        if ($isNegative)
        {
            $amount *= -1.00;
        }
        
        return $amount;
    }
    
    /*
     * Safeway card numbers are 19 digits, but are really text, not numeric.
     * This causes a lot of grief in Excel. Excel will automatically use 
     * scientific notation to represent a number greater than 12 digits (if the 
     * cell is the default "General" format). Since these card numbers are often
     * used in Excel, we will avoid this problem by inserting a space after the 
     * 12th digit. Excel then treats the number as text.
     */
    function modifySafewayCardNumber($cardNumber)
    {
        $modifiedCardNumber = $cardNumber;
        
        // Only deal with 19 digit numbers. If input is not as expected,
        // we will simply return it unchanged.
        if (preg_match("/^[0-9]{19}$/", $cardNumber) == 1)
        {
            $prefix = substr($cardNumber, 0, 12);
            $suffix = substr($cardNumber, 12);
            $modifiedCardNumber = $prefix . " " . $suffix;
        }
        
        return $modifiedCardNumber;
    }
    
    echo "<p class='pageMessage'>$pageMsg</p>";
    
    if (!$fatalError)
    {
    echo <<<_END
    <div class="form">
      <form method='post' action='reloads.php' enctype='multipart/form-data'>
        King Soopers
        <input type='file' name='file[]' size='10'>
        Safeway
        <input type='file' name='file[]' size='10'>    
        <button type='submit'>Upload</button>   
      </form>
    </div>
_END;
    }

?>
