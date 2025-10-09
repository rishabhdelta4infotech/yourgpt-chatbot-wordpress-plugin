<?php
add_action('wp_ajax_save_ygc_settings_ajax', 'ygc_save_settings_ajax');

function ygc_save_settings_ajax() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ygc_settings_action')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
    }

    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Insufficient permissions'));
        wp_die();
    }

    // Save widget_uid
    if (isset($_POST['widget_uid'])) {
        update_option('widget_uid', sanitize_text_field($_POST['widget_uid']));
    }

    // Save chatbot admin enabled (from checkbox directly)
    $chatbot_admin_enabled = isset($_POST['chatbot_admin_enabled']) && $_POST['chatbot_admin_enabled'] === '1' ? '1' : '0';
    update_option('chatbot_admin_enabled', $chatbot_admin_enabled);

    // Save search widget ID
    if (isset($_POST['search_widget_id'])) {
        update_option('search_widget_id', sanitize_text_field($_POST['search_widget_id']));
    }

    // Save search widget type
    if (isset($_POST['search_widget_type'])) {
        $allowed_types = array('floating', 'inplace', 'click');
        $widget_type = sanitize_text_field($_POST['search_widget_type']);
        if (in_array($widget_type, $allowed_types)) {
            update_option('search_widget_type', $widget_type);
        }
    }

    // Save search admin enabled (from checkbox directly)
    $search_admin_enabled = isset($_POST['search_admin_enabled']) && $_POST['search_admin_enabled'] === '1' ? '1' : '0';
    update_option('search_admin_enabled', $search_admin_enabled);

    // Return success response
    wp_send_json_success(array(
        'message' => 'Settings saved successfully!',
        'saved_values' => array(
            'widget_uid' => get_option('widget_uid'),
            'chatbot_admin_enabled' => get_option('chatbot_admin_enabled'),
            'search_widget_id' => get_option('search_widget_id'),
            'search_widget_type' => get_option('search_widget_type'),
            'search_admin_enabled' => get_option('search_admin_enabled')
        )
    ));

    wp_die();
}
