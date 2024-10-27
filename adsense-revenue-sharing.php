<?php
/**
 * Plugin Name: ADS Revenue Sharing
 * Description: A plugin to share AdSense revenue with user-specific settings and multiple ad placements.
 * Version: 1.4.1
 * Author: Mahmoud Eid
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: adsense-revenue-sharing
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ADSREVSHARE_PLUGIN_VERSION', '1.4.1');
define('ADSREVSHARE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ADSREVSHARE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoload classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'AdsRevShare') !== false) {
        $class_file = ADSREVSHARE_PLUGIN_DIR . 'includes/class-' . strtolower(str_replace('_', '-', $class)) . '.php';
        if (file_exists($class_file)) {
            require_once $class_file;
        }
    }
});

// Initialize the plugin
function adsrevshare_init_plugin() {
    // Load text domain for translations
    load_plugin_textdomain('adsense-revenue-sharing', false, basename(dirname(__FILE__)) . '/languages');
    
    // Initialize classes
    $admin = new AdsRevShare_Admin();
    $public = new AdsRevShare_Public();
    $ads_txt = new AdsRevShare_Ads_Txt();
    $user_profile = new AdsRevShare_User_Profile();
    $footer_ad = new AdsRevShare_Footer_Ad();
}
add_action('plugins_loaded', 'adsrevshare_init_plugin');