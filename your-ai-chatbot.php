<?php
/*
 * Plugin Name:       Your AI Chatbot
 * Plugin URI:        https://yourgpt.ai/chatbot
 * Description:       YourGPT chatbot for your WordPress Website. Take your WordPress Site to the next level with AI Chatbot - 24/7 AI Assistant Chatbot for Customer Support!
 * Version:           1.0.3
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            YourGPT Team
 * Author URI:        https://yourgpt.ai
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://yourgpt.ai/chatbot/
 * Text Domain:       YourGPT Chatbot
 */
if (!defined('ABSPATH')) {
    header("Location: /wordpress");
    die("");
}
define('PLUGIN_PATH', plugin_dir_path(__FILE__));
// include PLUGIN_PATH . "includes/activation.php";
include PLUGIN_PATH . "includes/ajax.php";
add_action("wp_enqueue_scripts", "my_js_script");
add_action("admin_enqueue_scripts", "my_admin_js_script");

function my_js_script()
{
    wp_enqueue_script("jquery");
    wp_enqueue_style("test_css", plugin_dir_url(__FILE__) . "assets/css/style.css");
    wp_enqueue_script("test_js", plugin_dir_url(__FILE__) . "assets/js/custom.js", array(), '1.2.1', false);
    wp_localize_script(
        'test_js',
        'getOption',
        array(
            'widget_uid' => get_option("widget_uid")
        )
    );
}
;
function my_admin_js_script()
{
    wp_enqueue_script("test_js", plugin_dir_url(__FILE__) . "assets/js/custom.js", array('jquery'), '1.2.2', false);
    wp_enqueue_style("test_css", plugin_dir_url(__FILE__) . "assets/css/style.css", array(), '1.2.5', false);
    wp_localize_script(
        'test_js',
        'getOption',
        array(
            'widget_uid' => get_option("widget_uid"),
            'ajaxurl' => admin_url('admin-ajax.php')
        )
    );
}
function add_html_script_to_frontend()
{
    // Load main chatbot widget
    echo '<script>
  window.YGC_WIDGET_ID="'.esc_js(get_option("widget_uid")).'";
  (function(){
    var script=document.createElement("script");
    script.src="https://widget.yourgpt.ai/script.js";
    script.id="yourgpt-chatbot";
    document.body.appendChild(script);
  })();
  </script>';

    // Load search widget if Widget UID is provided
    $search_widget_id = get_option('search_widget_id');
    $search_widget_type = get_option('search_widget_type') ? get_option('search_widget_type') : 'floating';

    if (!empty($search_widget_id)) {
        echo '<script>
  window.YGC_SEARCH_WIDGET = {
    id: "'.esc_js($search_widget_id).'",
    type: "'.esc_js($search_widget_type).'"
  };
  (function(){
    var script=document.createElement("script");
    script.src="https://search-widget.yourgpt.ai/script.js";
    script.id="ygc-search-widget-script";
    document.body.appendChild(script);
  })();
  </script>';
    }
}

function add_html_script_to_admin()
{
    // Load chatbot widget if enabled for admin
    $chatbot_admin_enabled = get_option('chatbot_admin_enabled');
    if ($chatbot_admin_enabled == '1') {
        echo '<script>
  window.YGC_WIDGET_ID="'.esc_js(get_option("widget_uid")).'";
  (function(){
    var script=document.createElement("script");
    script.src="https://widget.yourgpt.ai/script.js";
    script.id="yourgpt-chatbot";
    document.body.appendChild(script);
  })();
  </script>';
    }

    // Load search widget if enabled for admin and Widget UID is provided
    $search_admin_enabled = get_option('search_admin_enabled');
    $search_widget_id = get_option('search_widget_id');
    $search_widget_type = get_option('search_widget_type') ? get_option('search_widget_type') : 'floating';

    if ($search_admin_enabled == '1' && !empty($search_widget_id)) {
        echo '<script>
  window.YGC_SEARCH_WIDGET = {
    id: "'.esc_js($search_widget_id).'",
    type: "'.esc_js($search_widget_type).'"
  };
  (function(){
    var script=document.createElement("script");
    script.src="https://search-widget.yourgpt.ai/script.js";
    script.id="ygc-search-widget-script";
    document.body.appendChild(script);
  })();
  </script>';
    }
}

add_action('admin_footer', 'add_html_script_to_admin');
add_action("wp_footer", "add_html_script_to_frontend");
function my_openai_update_notice()
{
    global $pagenow;
    if (!esc_html(get_option("widget_uid")) && $pagenow === 'plugins.php') {
        ?>
        <div class="add-apiKey">
            <h2 class="title">Please setup your chatbot</h2>
            <div>
                <a class="add-api-btn" href="options-general.php?page=add-apiKey">setup now</a>
            </div>
        </div>
        <?php
    }
}
add_action('admin_notices', 'my_openai_update_notice');

add_action('admin_menu', 'plugin_menu');
add_action('admin_menu', 'plugin_menu_process');
function plugin_menu()
{

    add_submenu_page('options-general.php', 'YourGPT Chatbot', 'YourGPT Chatbot', 'manage_options', 'add-apiKey', 'plugin_menu_option_func');
}
;

function plugin_menu_process()
{
    register_setting('plugin_option_group', 'plugin_option_name');
    if (isset($_POST['action']) && $_POST['action'] === 'save_ygc_settings' && current_user_can('manage_options')) {
        // Verify nonce
        if (!isset($_POST['ygc_settings_nonce']) || !wp_verify_nonce($_POST['ygc_settings_nonce'], 'ygc_settings_action')) {
            wp_die('Security check failed');
        }

        // Get and validate the active tab
        $active_tab = isset($_POST['active_tab']) ? sanitize_text_field($_POST['active_tab']) : 'chatbot';
        $valid_tabs = array('chatbot', 'search');
        if (!in_array($active_tab, $valid_tabs)) {
            $active_tab = 'chatbot';
        }

        // Save widget_uid (always save, even if empty)
        if (isset($_POST['widget_uid'])) {
            update_option('widget_uid', sanitize_text_field($_POST['widget_uid']));
        }

        // Save chatbot admin display option (use hidden field value)
        $chatbot_admin_value = isset($_POST['chatbot_admin_enabled_hidden']) ? sanitize_text_field($_POST['chatbot_admin_enabled_hidden']) : '0';
        update_option('chatbot_admin_enabled', $chatbot_admin_value);

        // Save search widget options (always save all fields)
        if (isset($_POST['search_widget_id'])) {
            update_option('search_widget_id', sanitize_text_field($_POST['search_widget_id']));
        }

        if (isset($_POST['search_widget_type'])) {
            $allowed_types = array('floating', 'inplace', 'click');
            $widget_type = sanitize_text_field($_POST['search_widget_type']);
            if (in_array($widget_type, $allowed_types)) {
                update_option('search_widget_type', $widget_type);
            }
        }

        // Save search admin enabled (use hidden field value)
        $search_admin_value = isset($_POST['search_admin_enabled_hidden']) ? sanitize_text_field($_POST['search_admin_enabled_hidden']) : '0';
        update_option('search_admin_enabled', $search_admin_value);

        // Redirect back to the same tab with success message
        $redirect_url = add_query_arg(
            array(
                'page' => 'add-apiKey',
                'tab' => $active_tab,
                'settings-updated' => 'true'
            ),
            admin_url('options-general.php')
        );
        wp_safe_redirect($redirect_url);
        exit;
    }
}
;

function plugin_menu_option_func()
{
    // Get and validate active tab
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'chatbot';
    $valid_tabs = array('chatbot', 'search');
    if (!in_array($active_tab, $valid_tabs)) {
        $active_tab = 'chatbot';
    }

    // Show success message if redirected after save
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
        echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
    }
    ?>
    <?php settings_errors(); ?>
    <div class="outer">
        <div class="animated-background"></div>
        <div class="container">
            <h2 class="heading">YourGPT Plugin Settings</h2>

            <!-- Tab Navigation -->
            <div style="margin-bottom: 25px; border-bottom: 3px solid #e5e5e5;">
                <button type="button" onclick="switchTab('chatbot')" class="tab-button" data-tab="chatbot"
                   style="border: none; cursor: pointer; text-decoration: none; padding: 12px 30px; margin-right: 5px; display: inline-block; background: <?php echo $active_tab == 'chatbot' ? '#2271b1' : '#f6f7f7'; ?>; color: <?php echo $active_tab == 'chatbot' ? '#fff' : '#50575e'; ?>; border-radius: 5px 5px 0 0; font-weight: 600; margin-bottom: -3px; border-bottom: 3px solid <?php echo $active_tab == 'chatbot' ? '#2271b1' : 'transparent'; ?>;">
                    💬 Chatbot Widget
                </button>
                <button type="button" onclick="switchTab('search')" class="tab-button" data-tab="search"
                   style="border: none; cursor: pointer; text-decoration: none; padding: 12px 30px; display: inline-block; background: <?php echo $active_tab == 'search' ? '#2271b1' : '#f6f7f7'; ?>; color: <?php echo $active_tab == 'search' ? '#fff' : '#50575e'; ?>; border-radius: 5px 5px 0 0; font-weight: 600; margin-bottom: -3px; border-bottom: 3px solid <?php echo $active_tab == 'search' ? '#2271b1' : 'transparent'; ?>;">
                    🔍 Search Widget
                </button>
            </div>

            <form id="ajax_form" method="post">
                <?php wp_nonce_field('ygc_settings_action', 'ygc_settings_nonce'); ?>
                <input type="hidden" name="action" value="save_ygc_settings">

                <!-- Chatbot Tab Content -->
                <div id="chatbot-tab" class="tab-content" style="display: <?php echo $active_tab == 'chatbot' ? 'block' : 'none'; ?>;">
                <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 10px 0; color: #2271b1; font-size: 20px;">💬 AI Chatbot Widget</h3>
                    <p style="color: #666; margin: 0 0 25px 0;">Configure your AI-powered chatbot to engage with your website visitors 24/7</p>

                    <div class="input-group" style="margin-bottom: 25px;">
                        <label for="widgetUID" class="label" style="display: block; font-weight: 600; margin-bottom: 8px;">Widget UID <span style="color: #d63638;">*</span></label>
                        <input type="text" id="widgetUID" class="input-text" placeholder="Paste your chatbot widget UID here" name="widget_uid" value= "<?php echo esc_attr(get_option("widget_uid")) ?>" style="width: 100%; max-width: 600px; padding: 10px;">
                        <p style="font-size: 12px; color: #666; margin: 8px 0 0 0;">Get your Widget UID from: YourGPT Dashboard → Integrations → Copy Widget UID</p>
                    </div>

                    <div style="background: #f6f7f7; padding: 20px; border-radius: 6px; margin-bottom: 25px;">
                        <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 600;">Display Settings</h4>
                        <label style="display: flex; align-items: flex-start; cursor: pointer;">
                            <input type="checkbox" name="chatbot_admin_enabled" value="1" <?php checked(get_option('chatbot_admin_enabled'), '1'); ?> style="margin: 2px 10px 0 0;">
                            <div>
                                <span style="font-weight: 500; display: block; margin-bottom: 4px;">Show chatbot on admin pages</span>
                                <span style="font-size: 12px; color: #666;">Enable to display the chatbot in WordPress admin area</span>
                            </div>
                        </label>
                    </div>

                    <div style="background: #e7f5ec; padding: 15px; border-radius: 6px; border-left: 4px solid #4caf50; margin-bottom: 25px;">
                        <p style="margin: 0; font-size: 13px; color: #2e7d32;"><strong>💡 Note:</strong> The chatbot will appear on all frontend pages automatically when you save the Widget UID.</p>
                    </div>

                    <button type="submit" name="submit" class="button button-primary button-large" style="font-size: 14px;">
                        Save Chatbot Settings
                    </button>
                </div>
                </div>

                <!-- Search Widget Tab Content -->
                <div id="search-tab" class="tab-content" style="display: <?php echo $active_tab == 'search' ? 'block' : 'none'; ?>;">
                <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 10px 0; color: #2271b1; font-size: 20px;">🔍 AI Search Widget</h3>
                    <p style="color: #666; margin: 0 0 25px 0;">Add intelligent AI-powered search to help visitors find content quickly</p>

                    <div class="input-group" style="margin-bottom: 25px;">
                        <label for="searchWidgetID" class="label" style="display: block; font-weight: 600; margin-bottom: 8px;">Search Widget UID <span style="color: #d63638;">*</span></label>
                        <input type="text" id="searchWidgetID" class="input-text" placeholder="Paste your search widget UID here" name="search_widget_id" value="<?php echo esc_attr(get_option('search_widget_id')) ?>" style="width: 100%; max-width: 600px; padding: 10px;">
                        <p style="font-size: 12px; color: #666; margin: 8px 0 0 0;">Get your Widget UID from: YourGPT Dashboard → Integrations → Copy Widget UID</p>
                    </div>

                    <div style="background: #f6f7f7; padding: 20px; border-radius: 6px; margin-bottom: 25px;">
                        <h4 style="margin: 0 0 18px 0; font-size: 14px; font-weight: 600;">Display Settings</h4>

                        <div style="margin-bottom: 20px;">
                            <label for="searchWidgetType" style="display: block; font-weight: 500; margin-bottom: 8px;">Display Type</label>
                            <select id="searchWidgetType" name="search_widget_type" class="input-text" style="width: 100%; max-width: 400px; padding: 8px;">
                                <option value="floating" <?php selected(get_option('search_widget_type'), 'floating'); ?>>Floating</option>
                                <option value="inplace" <?php selected(get_option('search_widget_type'), 'inplace'); ?>>Inplace</option>
                                <option value="click" <?php selected(get_option('search_widget_type'), 'click'); ?>>Click</option>
                            </select>
                            <div id="search-widget-type-note" style="font-size: 12px; color: #666; margin: 12px 0 0 0; padding: 12px; background: #fff; border-radius: 4px; border: 1px solid #ddd;">
                            </div>
                        </div>

                        <label style="display: flex; align-items: flex-start; cursor: pointer;">
                            <input type="checkbox" name="search_admin_enabled" value="1" <?php checked(get_option('search_admin_enabled'), '1'); ?> style="margin: 2px 10px 0 0;">
                            <div>
                                <span style="font-weight: 500; display: block; margin-bottom: 4px;">Show search widget on admin pages</span>
                                <span style="font-size: 12px; color: #666;">Enable to display the search widget in WordPress admin area</span>
                            </div>
                        </label>
                    </div>

                    <div style="background: #e7f5ec; padding: 15px; border-radius: 6px; border-left: 4px solid #4caf50; margin-bottom: 25px;">
                        <p style="margin: 0; font-size: 13px; color: #2e7d32;"><strong>💡 Note:</strong> The search widget will appear on your website when you save the Widget UID. Leave UID empty to disable the search widget.</p>
                    </div>

                    <button type="submit" name="submit" class="button button-primary button-large" style="font-size: 14px;">
                        Save Search Settings
                    </button>
                </div>
                </div>
            </form>

            <br />
            <div style="display: flex; justify-content: space-between; padding: 15px 0;">
                <a href='https://yourgpt.ai/blog/growth/how-to-setup-yourgpt-chatbot-in-wordpress' target="_blank" class="help" style="color: #0073aa; text-decoration: none;">📖 How to setup</a>
                <a href='https://yourgpt.ai/contact' target="_blank" class="help" style="color: #0073aa; text-decoration: none;">❓ Need help?</a>
            </div>
        </div>
    </div>

    <script>
    // Tab switching without page reload
    function switchTab(tabName) {
        // Hide all tab contents
        var tabContents = document.getElementsByClassName('tab-content');
        for (var i = 0; i < tabContents.length; i++) {
            tabContents[i].style.display = 'none';
        }

        // Reset all tab button styles
        var tabButtons = document.getElementsByClassName('tab-button');
        for (var i = 0; i < tabButtons.length; i++) {
            tabButtons[i].style.background = '#f6f7f7';
            tabButtons[i].style.color = '#50575e';
            tabButtons[i].style.borderBottom = '3px solid transparent';
        }

        // Show selected tab content
        var selectedTab = document.getElementById(tabName + '-tab');
        if (selectedTab) {
            selectedTab.style.display = 'block';
        }

        // Highlight selected tab button
        var selectedButton = document.querySelector('[data-tab="' + tabName + '"]');
        if (selectedButton) {
            selectedButton.style.background = '#2271b1';
            selectedButton.style.color = '#fff';
            selectedButton.style.borderBottom = '3px solid #2271b1';
        }

        // Update the active tab hidden field
        var activeTabInput = document.getElementById('active_tab');
        if (activeTabInput) {
            activeTabInput.value = tabName;
        }

        // Update the URL without page reload
        var newUrl = new URL(window.location.href);
        newUrl.searchParams.set('tab', tabName);
        window.history.pushState({tab: tabName}, '', newUrl);

        // Update search widget note if on search tab
        if (tabName === 'search') {
            updateSearchWidgetNote();
        }
    }

    // Update search widget type notes
    function updateSearchWidgetNote() {
        var noteDiv = document.getElementById('search-widget-type-note');
        if (!noteDiv) return;

        var type = document.getElementById('searchWidgetType').value || 'floating';
        var notes = {
            'floating': '<strong>Floating Button:</strong> Widget appears as a floating button that visitors can click anytime.',
            'inplace': '<strong>Embed Inplace:</strong> Widget embeds at a specific location. Add <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">&lt;div id="ygc-search-widget-root"&gt;&lt;/div&gt;</code> where you want it.',
            'click': '<strong>Click to Open:</strong> Hidden until triggered. Use: <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">window.$YGC_SEARCH_WIDGET.open()</code>, <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">close()</code>, <code style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">toggle()</code>'
        };
        noteDiv.innerHTML = notes[type];
    }

    if (document.getElementById('searchWidgetType')) {
        document.getElementById('searchWidgetType').addEventListener('change', updateSearchWidgetNote);
        updateSearchWidgetNote();
    }
    </script>
    <?php
}

function my_plugin_activation()
{
    if (!esc_html(get_option("widget_uid"))) {
        delete_option('widget_uid');
    }
    add_option('widget_uid');

    // Initialize chatbot admin display option
    add_option('chatbot_admin_enabled', '0');

    // Initialize search widget options
    add_option('search_widget_id', '');
    add_option('search_widget_type', 'floating');
    add_option('search_admin_enabled', '0');
}

register_activation_hook(__FILE__, 'my_plugin_activation');

function my_plugin_deactivation()
{
    delete_option('widget_uid');
    delete_option('chatbot_admin_enabled');

    // Clean up search widget options
    delete_option('search_widget_id');
    delete_option('search_widget_type');
    delete_option('search_admin_enabled');
}

register_deactivation_hook(__FILE__, 'my_plugin_deactivation');
