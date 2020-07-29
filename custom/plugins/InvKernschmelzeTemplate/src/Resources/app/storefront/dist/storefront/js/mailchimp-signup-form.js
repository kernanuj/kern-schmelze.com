$(document).ready(function(){
    console.log('MC signup Script loaded.');

    if ($('#mce-EMAIL-cust').length) {
        $('#mce-EMAIL-cust')
            .keyup(function() {
                var href = $('#signupurl').val();
                var value = $(this).val();
                $('#signupformlink').attr('href',href + value);
            })
            .keyup();
    }
});
