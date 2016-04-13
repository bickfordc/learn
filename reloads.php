<?php

    require_once 'header.php';

    if (!$loggedin) die();

    if ($_FILES)  
    {   
        //print_r($_FILES);
        
        $names = array();
        $types = array();
        $messages = array();
        $uploadError = false;
        
        for ($i = 0; $i <= 1; $i++) 
        {
            $messages[$i] = "OK";
            if ($_FILES['file']['error'][$i] === UPLOAD_ERR_OK)
            {
                $names[$i] = $_FILES['file']['name'][$i];
                $types[$i] = $_FILES['file']['type'][$i];
                if ($types[$i] != "text/csv" && $types[$i] != "application/vnd.ms-excel")
                {
                    $messages[$i] = "The file type was $types[$i], not text/csv.";
                    $uploadError = true;
                }
                if (!$uploadError && $i == 0)
                {
                    if (!validateKingSoopers($_FILES['file']['tmp_name'][$i]))
                    {
                        $messages[$i] = "This does not appear to be a King Soopers statement.";
                        $uploadError = true;
                    }
                }
                if (!$uploadError && $i == 1)
                {
                    if (!validateSafeway($_FILES['file']['tmp_name'][$i]))
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
            // process the files
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
