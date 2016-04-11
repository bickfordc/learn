<?php

require_once 'header.php';

if (!$loggedin) die();

    echo <<<_END
    <p class='pageMessage'>Select the .csv files received from King Soopers and
        Safeway. If the file is in another form such as .xls or .xlsx, open it 
        with Excel and save as "CSV (Comma delimited) (*.csv)"</p>
    <div class="form">
      <form method='post' action='reloads.php' enctype='multipart/form-data'> 
        <input type='file' name='file1' size='10'>   
        <input type='file' name='file2' size='10'>    
        <button type='submit'>Upload</button>   
      </form>
    </div>
_END;

?>
