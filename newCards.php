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
        
        while (!feof($file))
        {
            $row = fgetcsv($file, 300, ",");
              
            $cardNumber = $row[0];
            
            if ($cardNumber == NULL)
            {
                continue;
            }
            
            $donorCode = (strlen($cardNumber) > 15) ? 'SW' : 'KS'; 
            $isSold = 'f';
            
            queryPostgres("INSERT INTO cards (id, sold, donor_code) VALUES ($1, $2, $3)",
                    array($cardNumber, $isSold, $donorCode));
        }
        
        fclose($file);
    } 
    
    echo <<<_END
    <p class='pageMessage'>$pageMsg</p>
    <p id='msg' class='pageMessage'>Select the new cards .csv file.</p>
    <div class="form">
      <form method='post' action='newCards.php' enctype='multipart/form-data'>    
        <input type='file' name='filename' size='10'>    
        <button type='submit'>Upload</button>   
      </form>
    </div>
_END;
    

?>

    <br></div>
  </body>
</html>

