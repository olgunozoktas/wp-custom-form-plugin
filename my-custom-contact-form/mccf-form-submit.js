jQuery(document).ready(function($) {
    $('.mccf_form').submit(function(event) {
        event.preventDefault();

        var formData = $(this).serialize();

        $.post(form_submit.ajax_url, {
            action: 'mccf_process_form',
            nonce: form_submit.nonce,
            data: formData
        }, function(response) {
            if (response === 'success') {
                $('.mccf_form').before('<p class="mccf_success_message">Thank you for your submission!</p>');  // Add the class here
                $('.mccf_form').hide();
            }
        });
    });
});
