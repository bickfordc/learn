var createCsvFromGrid = function(gridID, filename) {
    var grid = $('#' + gridID);
    // The grid data ids are the card id from each row of data that was 
    // displayed in the grid. 
    var rowIDList = grid.getDataIDs();
    var row = grid.getRowData(rowIDList[0]); 
    var colNames = [];
    var i = 0;
    var csvData = "";
    for(var cName in row) {
        // Create a header of column names, delimited with ',' 
        csvData += cName + ',';  
        // Capture Column Names for later use
        colNames[i++] = cName; 
        
    }
    csvData += '\r\n';
    
    for(var j=0;j<rowIDList.length;j++) {
        // Get the next row of card data that was displayed in the grid
        row = grid.getRowData(rowIDList[j]); 
        for(var i = 0 ; i<colNames.length ; i++ ) {
            // write the value from each columns, delimited with ','
            csvData += row[colNames[i]] + ','; 
        }
        csvData += '\r\n';
    }
    csvData += '\r\n';

    var a         = document.createElement('a');
    a.id = 'csvDownload';
    // Use encodeURIComponent to preserve line feeds in the string
    a.href        = 'data:text/csv,' + encodeURIComponent(csvData);
    a.download    = filename ? filename + ".csv" : 'data.csv';
    document.body.appendChild(a);
    a.click(); // Downloads the excel document
    document.getElementById('csvDownload').remove();
}

