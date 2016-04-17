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
            $ksCardData = getCardData($ksCardTotals, $ksCardsNotFound);
           
            //print_r($ksCardData);
            genStudentReport($ksCardData, NULL, NULL);
            
            $swCardTotals = processSafeway($tmpNames[1]);
            $swCardsNotFound = array();
            $swCardData = getCardData($swCardTotals, $swCardsNotFound);
            
            $cardsNotFound = array_merge($ksCardsNotFound, $swCardsNotFound);
            if (count($cardsNotFound > 0))
            {
                $pageMsg = "The following grocery cards were not found:<br>";
                foreach ($cardsNotFound as $val)
                {
                    $pageMsg .= $val . "<br>";
                }
            }
            
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
        
        return $cardTotals;
    }
    
    // build an array of students. Each student has an array of cards.
    // each card has a total.
    // idea for later.  student_scripfamily table. Each student may have multiple scrip families.
    function getCardData($cardTotals, &$cardsNotFound)
    {
        $cards = array();
        $cardData = array();
        $notFoundCount = 0;
        $i = 0;
        foreach ($cardTotals as $key => $val)
        {
            // Add logic here to recognize safeway card and add space.
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
                }
                $cards[$i++] = $cardData;
            }
        }
        return cards;  // includes both sold and unsold cards.
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
    //      swCards => an array of cardData arrays
    //      first   => Emma
    //      last    => Bickford
    function genStudentReport($ksCards, $swCards, $scripData)
    {
        $students = array();      
        
        foreach($ksCards as $value)
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
                    $students[$studentKey]["ksCards"][] = $value;
                }
                else
                {
                    $studKsCards = array($value);
                    $studData = array("ksCards" => $studKsCards, "first" => $first, "last" => $last);
                    $students[$studentKey] = $studData;
                }                        
            }
        }
        
        // print_r($students);
        ksort($students);
        foreach($students as $value)
        {
            $name = $value["first"] . " " . $value["last"];
            echo "<h2>$name</h2><br>";
            foreach ($value["ksCards"] as $cardsVal)
            {
                $cardNumber = $cardsVal["cardNumber"];
                $cardHolder = $cardsVal["cardHolder"];
                $total = $cardsVal["total"];
                echo "$cardNumber $cardHolder $total <br>";
            }
        }
        
        
    }
    
    function genNonStudentReport($ksCardData, $swCardData)
    {
        
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
