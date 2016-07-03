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
            
        //move_uploaded_file($_FILES['filename']['tmp_name'], $name);    

        
        $file = fopen($name, "r");
        if ($file == NULL)
        {
            die("Could not open file $file");
        }
 
        $firstLine = true;

        
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
            
            // Does scrip family already exist?
            //
            $result = queryPostgres("SELECT * FROM scrip_families WHERE first=$1 AND last=$2", 
                    array($scripFirst, $scripLast));
            
            if (pg_num_rows($result) == 0) 
            {
                // Write to scrip_families table
                //
                queryPostgres("INSERT INTO scrip_families (first, last) VALUES ($1, $2)", 
                    array($scripFirst, $scripLast));
            }
            
            if ($studentLast == NULL)
            {
                // Not associated with a student
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
            
            // Does student - scrip record already exist?
            $result = queryPostgres("SELECT * FROM student_scrip_families WHERE student=$1 AND scrip_first=$2 AND scrip_last=$3",
                    array($studentId, $scripFirst, $scripLast));
            
            if (pg_num_rows($result) == 0) 
            {
                // Write to student_scrip_families table
                //
                queryPostgres("INSERT INTO student_scrip_families (student, scrip_first, scrip_last) VALUES ($1, $2, $3)", 
                    array($studentId, $scripFirst, $scripLast));
            }
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