$(function () {
    $("#list").jqGrid({
        url: "getCardData.php",
        datatype: "xml",
        mtype: "GET",
        colNames: ["Card Number", "Sold", "Card Holder", "Notes", "Active", "Card Type", "Student First", "Student Last"],
        colModel: [
            { name: "id", width: 100, editable:true },
            { name: "sold", width: 50, editable: true, formatter: "checkbox",
                edittype: "checkbox", align: "center",
                editoptions: { value: "true:false"} 
            },
            { name: "card_holder", width: 100, editable: true },
            { name: "notes", width: 40, editable: true },
            { name: "active", width: 50, editable: true, formatter: "checkbox", 
              edittype: "checkbox", align: "center",
              editoptions: { value: "true:false"} 
            },
            { name: "donor_code", width: 60 },
            { name: "first", width: 60 },
            { name: "last", width: 60 }
        ],
        editurl: "editCardData.php",
        pager: "#pager",
        rowNum: 15,
        // rowList: [10, 20, 30],
        sortname: "id",
        sortorder: "asc",
        viewrecords: true,
        gridview: true,
        autoencode: true,
        caption: "Store Cards",
        height: "auto",
        autowidth: true,
        loadError: function(jqXHR, textStatus, errorThrown) {
               alert('HTTP status code: ' + jqXHR.status + '<br>' +
              'textStatus: ' + textStatus + '<br>' +
              'errorThrown: ' + errorThrown + '<br>' +
              'HTTP message body:<br><br>' + jqXHR.responseText);
        }
    }); 
    
    $("#list").jqGrid('navGrid', "#pager", 
        {alerttext: "No row is selected"}, // general navigator parameters
        {editCaption: "Edit card"},     // modal edit   window parameters
        {addCaption: "Add a card"},     // modal add    window parameters
        {caption: "Delete card",        // modal del    window parameters
         width:500, msg: "Delete selected card?"},  
        {width:600},                       // modal search window parameters
        {}                                 // modal view   window parameters
    );
    //jQuery("#mysearch").jqGrid('filterGrid','#list',options);
}); 
