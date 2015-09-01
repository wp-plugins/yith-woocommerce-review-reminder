<?php
/*
Plugin Name: YITH WooCommerce Review Reminder
Plugin URI: http://yithemes.com/themes/plugins/yith-woocommerce-review-reminder
Description: Send a review reminder to the customers over WooCommerce.
Author: Yithemes
Text Domain: ywrr
Version: 1.0.6
Author URI: http://yithemes.com/
*/

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

function ywrr_install_woocommerce_admin_notice() {
    ?>
    <div class="error">
        <p><?php _e( 'YITH WooCommerce Review Reminder is enabled but not effective. It requires WooCommerce in order to work.', 'ywrr' ); ?></p>
    </div>
<?php
}

function ywrr_install_free_admin_notice() {
    ?>
    <div class="error">
        <p><?php _e( 'You can\'t activate the free version of YITH WooCommerce Review Reminder while you are using the premium one.', 'ywrr' ); ?></p>
    </div>
<?php
}

if ( !defined( 'YWRR_VERSION' ) ) {
    define( 'YWRR_VERSION', '1.0.6' );
}

if ( !defined( 'YWRR_FREE_INIT' ) ) {
    define( 'YWRR_FREE_INIT', plugin_basename( __FILE__ ) );
}

if ( !defined( 'YWRR_FILE' ) ) {
    define( 'YWRR_FILE', __FILE__ );
}

if ( !defined( 'YWRR_DIR' ) ) {
    define( 'YWRR_DIR', plugin_dir_path( __FILE__ ) );
}

if ( !defined( 'YWRR_URL' ) ) {
    define( 'YWRR_URL', plugins_url( '/', __FILE__ ) );
}

if ( !defined( 'YWRR_ASSETS_URL' ) ) {
    define( 'YWRR_ASSETS_URL', YWRR_URL . 'assets/' );
}

if ( !defined( 'YWRR_TEMPLATE_PATH' ) ) {
    define( 'YWRR_TEMPLATE_PATH', YWRR_DIR . 'templates/' );
}

function ywrr_init() {

    /* Load YWRR text domain */
    load_plugin_textdomain( 'ywrr', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    global $YWRR_Review_Reminder;
    $YWRR_Review_Reminder = new YWRR_Review_Reminder();
}

add_action( 'ywrr_init', 'ywrr_init' );

function ywrr_install() {

    require_once( YWRR_DIR . 'class.yith-woocommerce-review-reminder.php' );

    if ( !function_exists( 'WC' ) ) {
        add_action( 'admin_notices', 'ywrr_install_woocommerce_admin_notice' );
    }
    elseif ( defined( 'YWRR_PREMIUM' ) ) {
        add_action( 'admin_notices', 'ywrr_install_free_admin_notice' );
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }
    else {
        do_action( 'ywrr_init' );
    }
}

add_action( 'plugins_loaded', 'ywrr_install', 11 );

/**
 * Init default plugin settings
 */
if ( !function_exists( 'yith_plugin_registration_hook' ) ) {
    require_once 'plugin-fw/yit-plugin-registration-hook.php';
}


register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );
register_activation_hook( __FILE__, 'ywrr_create_tables' );
register_activation_hook( __FILE__, 'ywrr_create_schedule_job' );
register_deactivation_hook( __FILE__, 'ywrr_create_unschedule_job' );

if ( !function_exists( 'ywrr_create_tables' ) ) {

    /**
     * Creates database table for blocklist e scheduling
     *
     * @since   1.0.0
     * @return  void
     * @author  Alberto Ruggiero
     */
    function ywrr_create_tables() {
        global $wpdb;

        $wpdb->hide_errors();

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( !empty( $wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }
            if ( !empty( $wpdb->collate ) ) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $ywrr_tables = "
            CREATE TABLE {$wpdb->prefix}ywrr_email_blocklist (
              id int NOT NULL AUTO_INCREMENT,
              customer_email longtext NOT NULL,
              customer_id bigint(20) NOT NULL DEFAULT 0,
              PRIMARY KEY (id)
            ) $collate;
            CREATE TABLE {$wpdb->prefix}ywrr_email_schedule (
              id int NOT NULL AUTO_INCREMENT,
              order_id bigint(20) NOT NULL,
              order_date date NOT NULL DEFAULT '0000-00-00',
              scheduled_date date NOT NULL DEFAULT '0000-00-00',
              request_items longtext NOT NULL DEFAULT '',
              mail_status varchar(15) NOT NULL DEFAULT 'pending',
              PRIMARY KEY (id)
            ) $collate;
            ";

        dbDelta( $ywrr_tables );
    }
}

if ( !function_exists( 'ywrr_create_schedule_job' ) ) {

    /**
     * Creates a cron job to handle daily mail send
     *
     * @since   1.0.0
     * @return  void
     * @author  Alberto Ruggiero
     */
    function ywrr_create_schedule_job() {
        wp_schedule_event( time(), 'daily', 'ywrr_daily_send_mail_job' );
    }

}

if ( !function_exists( 'ywrr_create_unschedule_job' ) ) {

    /**
     * Removes cron job
     *
     * @since   1.0.0
     * @return  void
     * @author  Alberto Ruggiero
     */
    function ywrr_create_unschedule_job() {
        wp_clear_scheduled_hook( 'ywrr_daily_send_mail_job' );
    }

}