<?php

    require_once 'header.php';

    if (!$loggedin) die();

    if ($_FILES)  
    {   
        //print_r($_FILES);
        
        $names = array();
        $tmpNames = array();
        $types = array();
        $messages = array();
        $uploadError = false;
        
        for ($i = 0; $i <= 0; $i++) // no safeway.  change to <= 1 later
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
            $kingSoopersCardTotals = array();
            processKingSoopers($tmpNames[0], $kingSoopersCardTotals);
            
            print_r($kingSoopersCardTotals);
            
            processSafeway($tmpNames[1]);
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
    
    function processKingSoopers($tmpName, &$cardTotals)
    {
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
    }
    
    function processSafeway($tmpName)
    {
        
    }
    
    function getCardOwners($cardTotals, &$cardOwners)
    {
        $numCards = count($cardTotals);
        foreach ($cardTotals as $key => $val)
        {
            // is the card sold?
            $card = queryPostgres("SELECT * FROM cards where id=$1", array($key)
            $cardOwners[$key] = 2;
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
    
    echo <<<_END
    <p class='pageMessage'>$pageMsg</p>
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

?>
