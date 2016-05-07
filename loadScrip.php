<?php

    require_once 'header.php';
    
    $pageMsg;
    
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
        //$lastAcctMarker = -1;
        
        while (!feof($file))
        {
            $row = fgetcsv($file, 300, ",");
            if ($firstLine)
            {
                $firstLine = false;
                continue;
            }
              
            $scripFirst = $row[0];
            $scripLast = $row[1];
            $studentFirst = $row[3];
            $studentLast = $row[4];            
            
            if ($scripFirst == NULL)
            {
                continue;
            }
            
            // lookup student id
            //
            $studentId;
            $result = queryPostgres("SELECT * FROM students WHERE first=$1 AND last=$2", 
                    array($studentFirst, $studentLast));
            
            if (pg_num_rows($result) == 0) 
            {
                $pageMsg = "Student $studentFirst $studentLast does not exist";
            }
            else 
            {
                $row = pg_fetch_array($result);
                $studentId = $row["id"];
            }
            
            // Write to scrip_family table
            //
            queryPostgres("INSERT INTO scrip_families (first, last) VALUES ($1, $2)", 
                    array($scripFirst, $scripLast));
            
            // Write to student_scrip_families table
            //
            queryPostgres("INSERT INTO student_scrip_families (student, scrip_first, scrip_last) VALUES ($1, $2, $3)", 
                    array($studentId, $scripFirst, $scripLast));
        }
        
        fclose($file);
        
    } 
    
    echo <<<_END
    <p class='pageMessage'>$pageMsg</p>
    <p id='msg' class='pageMessage'>Select the scripFamilies.csv file.</p>
    <div class="form">
      <form method='post' action='loadScrip.php' enctype='multipart/form-data'>    
        <input type='file' name='filename' size='10'>    
        <button type='submit'>Upload</button>   
      </form>
    </div>
_END;
    

?>

    <br></div>
  </body>
</html>