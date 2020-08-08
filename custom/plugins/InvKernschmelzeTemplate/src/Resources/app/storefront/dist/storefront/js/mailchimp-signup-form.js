$(document).ready(function() {
    var mceEmailCust= $('#mce-EMAIL-cust');

    if (mceEmailCust.length) {
        mceEmailCust
            .keyup(function() {
                var href = $('#signupurl').val();
                var value = $(this).val();
                $('#signupformlink').attr('href',href + value);
            })
            .keyup();
    }
});
