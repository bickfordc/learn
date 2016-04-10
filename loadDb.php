<?php

    require_once 'header.php';
    
    if ($_FILES)  
    {    
        $name = $_FILES['filename']['name']; 
        
        $type = $_FILES['filename']['type'];
        if ($type != "text/csv" && $type != "application/vnd.ms-excel")
        {
            echo "That file type was $type, not text/csv.";
            exit();
        }
            
        move_uploaded_file($_FILES['filename']['tmp_name'], $name);    
        //echo "Uploaded image '$name'<br><img src='$name'>"; 
        
        $file = fopen($name, "r");
        if ($file == NULL)
        {
            die("Could not open file $file");
        }
 
        $firstLine = true;
        $lastAcctMarker = -1;
        
        while (!feof($file))
        {
            $row = fgetcsv($file, 300, ",");
            if ($firstLine)
            {
                $firstLine = false;
                continue;
            }
              
            $cardNumber = $row[1];
            if ($cardNumber == NULL)
            {
                continue;
            }
            
            $cardHolder = $row[0];
            $isSold =  $row[2] == 'y' ? 't' : 'f';

            queryPostgres("INSERT INTO cards (id, sold, card_holder) VALUES ($1, $2, $3)",
                    array($cardNumber, $isSold, $cardHolder));
            
            if ($isSold == 't')
            {
                $firstName  = $row[3];
                $lastName   = $row[4];
                if ($lastName == NULL)
                {
                    continue;
                }
                
                $isStudentActive = $row[6] == 'y' ? 't' : 'f';
                
                $acctMarker = $row[5];
                if ($lastAcctMarker != $acctMarker) 
                {
                    $newAccount = true;
                    $lastAcctMarker = $acctMarker;
                    
                    queryPostgres("INSERT INTO students (first, last, active) VALUES ($1, $2, $3)",
                        array($firstName, $lastName, $isStudentActive));
                }
                
                // get the auto incremented id from the student account 
                $result = queryPostgres("SELECT id FROM students WHERE first=$1 and last=$2",
                        array($firstName, $lastName));
                
                if (pg_num_rows($result) > 0) 
                {
                    $theRow = pg_fetch_array($result);
                    $studentId = $theRow['id'];
                }
                
                queryPostgres("INSERT INTO student_cards (student, card) VALUES ($1, $2)",
                        array($studentId, $cardNumber));
            }

        }
        
        fclose($file);
        
    } 
    
    echo <<<_END
    <p class='pageMessage'>Select a csv file to use as the initial database load.</p>
    <div class="form">
      <form method='post' action='loadDb.php' enctype='multipart/form-data'>    
        <input type='file' name='filename' size='10'>    
        <button type='submit'>Upload</button>   
      </form>
    </div>
_END;
    

?>

    <br></div>
  </body>
</html>