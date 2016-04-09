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
        echo "Uploaded image '$name'<br><img src='$name'>";  
        
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