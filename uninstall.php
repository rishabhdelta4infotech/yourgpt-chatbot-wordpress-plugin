<?php


// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete the yacb_widget_uid option when the plugin is uninstalled
delete_option('yacb_widget_uid');