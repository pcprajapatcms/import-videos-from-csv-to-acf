<?php
/**
 * Plugin Name: Import Videos From CSV to ACF
 * Description: A plugin to import Videos from CSV data into Advanced Custom Fields.
 * Version: 1.0
 * Author: CMS MINDS
 * Author URI: https://cmsminds.com
 */

// Hook into admin_init to check and deactivate the plugin if ACF is not active
add_action('admin_init', 'impcsvtoacf_check_acf_activation');

function impcsvtoacf_check_acf_activation() {
    if (!in_array('advanced-custom-fields-pro/acf.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        // ACF is not active, deactivate this plugin
        impcsvtoacf_deactivate_self();
    }
}

function impcsvtoacf_deactivate_self() {
    deactivate_plugins(plugin_basename(__FILE__));
    // Optionally display a notice
    add_action('admin_notices', 'acf_not_activated_notice');

    function acf_not_activated_notice() {
        echo '<div class="error"><p>Import Videos From CSV to ACF plugin requires Advanced Custom Fields Pro plugin to be activated.</p></div>';
    }
    // Hide plugin activated notice
    if (isset($_GET['activate'])) {
        unset($_GET['activate']);
    }
}
  
// Initialize the plugin only if ACF is active
if (in_array('advanced-custom-fields-pro/acf.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // Include the main plugin class
    require_once plugin_dir_path(__FILE__) . 'includes/class-impcsvtoacf-import-csv-to-acf.php';

    // Instantiate the plugin
    $import_csv_to_acf = new ImpCSVtoACF_Import_CSV_To_ACF();
    $import_csv_to_acf->init();

    // Add settings link
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'impcsvtoacf_settings_link');

    function impcsvtoacf_settings_link($links) {
        $settings_link = '<a href="admin.php?page=import-csv-to-acf">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}
?>
