<?php
add_action('wp_ajax_my_ajax_form','plugin_ajax_action');
function plugin_ajax_action(){
    if(isset($_POST['action']) && isset($_POST['widget_uid'])){
        update_option('widget_uid',sanitize_text_field($_POST['widget_uid']));

        // Save chatbot admin option
        if(isset($_POST['chatbot_admin_enabled'])){
            update_option('chatbot_admin_enabled', $_POST['chatbot_admin_enabled'] === '1' ? '1' : '0');
        } else {
            update_option('chatbot_admin_enabled', '0');
        }

        // Save search widget options if provided
        if(isset($_POST['search_widget_enabled'])){
            update_option('search_widget_enabled', $_POST['search_widget_enabled'] === '1' ? '1' : '0');
        } else {
            update_option('search_widget_enabled', '0');
        }

        if(isset($_POST['search_widget_id'])){
            update_option('search_widget_id', sanitize_text_field($_POST['search_widget_id']));
        }

        if(isset($_POST['search_widget_type'])){
            $allowed_types = array('floating', 'inplace', 'click');
            $widget_type = sanitize_text_field($_POST['search_widget_type']);
            if(in_array($widget_type, $allowed_types)){
                update_option('search_widget_type', $widget_type);
            }
        }

        if(isset($_POST['search_show_in_admin'])){
            update_option('search_show_in_admin', $_POST['search_show_in_admin'] === '1' ? '1' : '0');
        } else {
            update_option('search_show_in_admin', '0');
        }

        echo "success";
    }else{
        echo "failed";
    }
    wp_die();
}
