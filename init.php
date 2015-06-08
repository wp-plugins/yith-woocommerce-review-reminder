<?php
/*
Plugin Name: YITH WooCommerce Review Reminder
Plugin URI: http://yithemes.com/themes/plugins/yith-woocommerce-review-reminder
Description: Send a review reminder to the customers over WooCommerce.
Author: Yithemes
Text Domain: ywrr
Version: 1.0.3
Author URI: http://yithemes.com/
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

if ( ! function_exists( 'WC' ) ) {
    function ywrr_install_woocommerce_admin_notice() {
        ?>
        <div class="error">
            <p><?php _e( 'YITH WooCommerce Review Reminder is enabled but not effective. It requires Woocommerce in order to work.', 'ywrr' ); ?></p>
        </div>
    <?php
    }
    add_action( 'admin_notices', 'ywrr_install_woocommerce_admin_notice' );
    return;
}

if ( defined( 'YWRR_PREMIUM' ) ) {
    function ywrr_install_free_admin_notice() {
        ?>
        <div class="error">
            <p><?php _e( 'You can\'t activate the free version of YITH WooCommerce Review Reminder while you are using the premium one.', 'ywrr' ); ?></p>
        </div>
    <?php
    }

    add_action( 'admin_notices', 'ywrr_install_free_admin_notice' );

    deactivate_plugins( plugin_basename( __FILE__ ) );
    return;
}

if ( defined( 'YWRR_VERSION' ) ) {
    return;
} else {
    define( 'YWRR_VERSION', '1.0.3' );
}

if ( ! defined( 'YWRR_FREE_INIT' ) ) {
    define( 'YWRR_FREE_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YWRR_FILE' ) ) {
    define( 'YWRR_FILE', __FILE__ );
}

if ( ! defined( 'YWRR_DIR' ) ) {
    define( 'YWRR_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YWRR_URL' ) ) {
    define( 'YWRR_URL', plugins_url( '/', __FILE__ ) );
}

if ( ! defined( 'YWRR_ASSETS_URL' ) ) {
    define( 'YWRR_ASSETS_URL', YWRR_URL . 'assets/' );
}

if ( ! defined( 'YWRR_TEMPLATE_PATH' ) ) {
    define( 'YWRR_TEMPLATE_PATH', YWRR_DIR . 'templates/' );
}

/* Load YWCM text domain */
load_plugin_textdomain( 'ywrr', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**
 * Init default plugin settings
 */
if ( !function_exists( 'yith_plugin_registration_hook' ) ) {
    require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

require_once( YWRR_DIR . 'class.yith-woocommerce-review-reminder.php' );

register_activation_hook( __FILE__, array('YWRR_Review_Reminder', 'ywrr_create_tables' ) );
register_activation_hook( __FILE__, array('YWRR_Review_Reminder', 'ywrr_create_schedule_job' ) );
register_deactivation_hook( __FILE__, array('YWRR_Review_Reminder', 'ywrr_create_unschedule_job' ) );

global $YWRR_Review_Reminder;

$YWRR_Review_Reminder = new YWRR_Review_Reminder();