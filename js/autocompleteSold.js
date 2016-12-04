
var MIN_LENGTH = 3;

$( document ).ready(function() {
    
    $("#card").keyup(function() {
            var keyword = $("#card").val();
            if (keyword.length >= MIN_LENGTH) {
                    $.get( "searchSoldCard.php", { key: keyword } )
                    .done(function( data ) {
                        $('#cardresults').html('');
                        var results = jQuery.parseJSON(data);
                        console.log(results);
                        $(results).each(function(key, value) {
                            res = value.split("|");
                            $('#cardresults').append('<div class="carditem" data-student="' + res[1] + '">' + res[0] + '</div>');
                        })

                        $('.carditem').click(function() {
                            var cardNumber = $(this).html();
                            $('#card').val(cardNumber);
                            var studentName = $(this).attr('data-student');
                            $('#student').val(studentName);
                        })

                    });
            } else {
                    $('#cardresults').html('');
            }
    });
    
    $("#card").blur(function(){
            $("#cardresults").fadeOut(500);
    })
    .focus(function() {		
        $("#cardresults").show();
    });

});
