
function checkPasswordMatch() {
    var password = $("#pw1").val();
    var confirmPassword = $("#pw2").val();

    if (password != confirmPassword) {
        $("#info").html("&#x2718; Passwords do not match!");
        $("#info").css({"color":"red"});
    }
    else {
        $("#info").html("&#x2714; Passwords match.");
        $("#info").css({"color":"green"});
    }
}

$(document).ready(function () {
   $("#pw2").keyup(checkPasswordMatch);
});