jQuery(function ($) {
    // Get ajaxurl from localized script or use WordPress default
    var ajaxurl = getOption.ajaxurl || window.ajaxurl || '/wp-admin/admin-ajax.php';

    $('#ajax_form').on('submit', function (e) {
        e.preventDefault(); // Always prevent default form submission

        console.log('=== FORM SUBMIT START ===');

        // Get the form element
        var form = e.target;

        // Collect all form data
        var formData = {
            action: "save_ygc_settings_ajax",
            nonce: $('#ygc_settings_nonce').val(),
            widget_uid: form.widget_uid ? form.widget_uid.value : '',
            chatbot_admin_enabled: form.chatbot_admin_enabled && form.chatbot_admin_enabled.checked ? '1' : '0'
        };

        console.log('Form Data:', formData);
        console.log('AJAX URL:', ajaxurl);

        // Show loading state - find the submit button that was clicked
        var submitButton = $(e.originalEvent.submitter || $(form).find('button[type="submit"]:visible').first());
        var originalButtonText = submitButton.text();
        submitButton.prop('disabled', true).text('Saving...');

        // Remove any existing notices
        $('.ygc-notice').remove();

        // Send AJAX request
        $.post(ajaxurl, formData, function (response) {
            console.log('Response:', response);

            if (response.success) {
                console.log('Saved values:', response.data.saved_values);
                console.log('✅ Settings saved successfully!');

                // Update button to show success state
                submitButton.text('✓ Saved! Reloading...').css({
                    'background': '#46b450',
                    'border-color': '#46b450'
                });

                // Show success message briefly before reload
                var successHtml = '<div class="ygc-notice notice notice-success is-dismissible" style="margin: 20px 0;"><p>' + response.data.message + '</p></div>';
                $('#ajax_form').before(successHtml);

                // Scroll to top to show the success message
                $('html, body').animate({ scrollTop: 0 }, 300);

                // Reload page after 1.5 seconds to show the success message
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                // Show error message
                submitButton.prop('disabled', false).text(originalButtonText);
                var errorHtml = '<div class="ygc-notice notice notice-error is-dismissible" style="margin: 20px 0;"><p>' + response.data.message + '</p></div>';
                $('#ajax_form').before(errorHtml);
                console.error('❌ Error:', response.data.message);
            }
        }).fail(function(xhr, status, error) {
            submitButton.prop('disabled', false).text(originalButtonText);
            var errorHtml = '<div class="ygc-notice notice notice-error is-dismissible" style="margin: 20px 0;"><p>Error saving settings. Please try again.</p></div>';
            $('#ajax_form').before(errorHtml);
            console.error('❌ AJAX Error:', error);
        });
    });
})
