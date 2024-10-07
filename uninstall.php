<?php

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Perform cleanup tasks, e.g., deleting options or custom post types
//delete_option( 'my_awesome_plugin_option' );
