jQuery(function ($) {
    var widget_uid = getOption.widget_uid;
    $('#ajax_form').on('submit', function (el) {
        // Check if widget_uid field exists before accessing it
        if (el.target.widget_uid && el.target.widget_uid.value !== undefined) {
            el.preventDefault();
            console.log("widget_uid", el.target.widget_uid.value);

            // Collect all form data
            var formData = {
                action: "my_ajax_form",
                widget_uid: el.target.widget_uid.value
            };

            // Add chatbot admin option
            if (el.target.chatbot_admin_enabled) {
                formData.chatbot_admin_enabled = el.target.chatbot_admin_enabled.checked ? '1' : '0';
            }

            // Add search widget data if fields exist
            if (el.target.search_widget_enabled) {
                formData.search_widget_enabled = el.target.search_widget_enabled.checked ? '1' : '0';
            }
            if (el.target.search_widget_id) {
                formData.search_widget_id = el.target.search_widget_id.value;
            }
            if (el.target.search_widget_type) {
                formData.search_widget_type = el.target.search_widget_type.value;
            }

            $.post(ajaxurl, formData, function (val) {
                window.location.reload();
            })
        }
        // If widget_uid doesn't exist, let the form submit normally (for settings page)
    })

})
