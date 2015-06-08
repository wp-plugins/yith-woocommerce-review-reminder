<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Implements features of YWRR plugin
 *
 * @class   YWRR_Review_Reminder
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 */
class YWRR_Review_Reminder {

    /**
     * Panel object
     *
     * @var     /Yit_Plugin_Panel object
     * @since   1.0.0
     * @see     plugin-fw/lib/yit-plugin-panel.php
     */
    protected $_panel;

    /**
     * @var $_premium string Premium tab template file name
     */
    protected $_premium = 'premium.php';

    /**
     * @var string Premium version landing link
     */
    protected $_premium_landing = 'http://yithemes.com/themes/plugins/yith-woocommerce-review-reminder/';

    /**
     * @var string Plugin official documentation
     */
    protected $_official_documentation = 'http://yithemes.com/docs-plugins/yith_woocommerce_review_reminder/';

    /**
     * @var string Yith WooCommerce Review Reminder panel page
     */
    protected $_panel_page = 'yith_ywrr_panel';

    /**
     * @var array
     */
    protected $_email_types = array();

    /**
     * Constructor
     *
     * Initialize plugin and registers actions and filters to be used
     *
     * @since   1.0.0
     * @return  mixed
     * @author  Alberto Ruggiero
     */
    public function __construct() {
        if ( ! function_exists( 'WC' ) ) {
            return;
        }

        $this->_email_types = array(
            'request'       => array(
                'class' => 'YWRR_Request_Mail',
                'file'  => 'class-ywrr-request-email.php',
                'hide'  => false,
            ),
            'unsubscribe'   =>  array(
                'class' => 'YWRR_Unsubscribe_Mail',
                'file'  => 'class-ywrr-unsubscribe-email.php',
                'hide'  => true,
            ),
        );

        // Load Plugin Framework
        add_action( 'after_setup_theme', array( $this, 'plugin_fw_loader' ), 1 );

        //Add action links
        add_filter( 'plugin_action_links_' . plugin_basename( YWRR_DIR . '/' . basename( YWRR_FILE ) ), array(
            $this,
            'action_links'
        ) );
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

        // Include required files
        $this->includes();

        //  Add stylesheets and scripts files
        add_action( 'admin_menu', array( $this, 'add_menu_page' ), 5 );
        add_action( 'yith_review_reminder_premium', array( $this, 'premium_tab' ) );

        add_action( 'init', array( $this, 'ywrr_post_status' ) );
        add_action( 'init', array( $this, 'ywrr_create_pages' ) );

        add_filter( 'set-screen-option', 'YWRR_Blocklist_Table::set_options', 10, 3);

        add_action( 'woocommerce_admin_field_customtext', 'YWRR_Custom_Textarea::output' );
        add_action( 'ywrr_blocklist', 'YWRR_Blocklist_Table::output' );
        add_action( 'current_screen', 'YWRR_Blocklist_Table::add_options' );

        add_action( 'woocommerce_order_status_completed', 'YWRR_Schedule::schedule_mail' );
        add_filter( 'woocommerce_email_classes', array( $this, 'ywrr_custom_email' ) );
        add_filter( 'woocommerce_get_sections_email', array( $this, 'ywrr_hide_sections' ) );

        if ( defined( 'YWRR_PREMIUM' ) ) {
            add_action( 'ywrr_daily_send_mail_job', 'YWRR_Schedule_Premium::daily_schedule' );
        } else {
            add_action( 'ywrr_daily_send_mail_job', 'YWRR_Schedule::daily_schedule' );
        }

        add_action( 'admin_notices', array( $this, 'ywrr_protect_unsubscribe_page_notice' ) );
        add_action( 'wp_trash_post', array( $this,'ywrr_protect_unsubscribe_page' ), 10, 1 );
        add_action( 'before_delete_post', array( $this,'ywrr_protect_unsubscribe_page' ), 10, 1 );

        add_option( 'ywrr_mail_schedule_day', 7 );
        add_option( 'ywrr_mail_template', 'base' );

        add_shortcode( 'ywrr_unsubscribe', array( $this, 'ywrr_unsubscribe' ) );

    }

    /**
     * Hides custom email settings from WooCommerce panel
     *
     * @since   1.0.0
     * @param   $sections
     * @return  array
     * @author  Andrea Grillo
     */
    public function ywrr_hide_sections( $sections ){
        foreach( $this->_email_types as $type => $email_type ){
            $class_name = strtolower( $email_type['class'] );
            if( isset( $sections[ $class_name ] ) && $email_type['hide'] == true ){
                unset( $sections[ $class_name ] );
            }
        }

        return $sections;
    }

    /**
     * Enqueue css file
     *
     * @since   1.0.0
     * @return  void
     * @author  Andrea Grillo <andrea.grillo@yithemes.com>
     */
    public function plugin_fw_loader() {
        if ( ! defined( 'YIT' ) || ! defined( 'YIT_CORE_PLUGIN' ) ) {
            require_once( 'plugin-fw/yit-plugin.php' );
        }
    }

    /**
     * Files inclusion
     *
     * @since   1.0.0
     * @return  void
     * @author  Alberto Ruggiero
     */
    private function includes() {

        include_once( 'includes/class-ywrr-emails.php' );
        include_once( 'includes/class-ywrr-blocklist.php' );
        include_once( 'includes/class-ywrr-schedule.php' );

        if ( is_admin() ) {
            include_once( 'includes/admin/class-yith-custom-table.php' );
            include_once( 'templates/admin/custom-textarea.php' );
            include_once( 'templates/admin/blocklist-table.php' );
        }

        if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
            include_once( 'includes/class-ywrr-form-handler.php' );
        }
    }

    /**
     * Add a panel under YITH Plugins tab
     *
     * @since   1.0.0
     * @return  void
     * @author  Alberto Ruggiero
     * @use     /Yit_Plugin_Panel class
     * @see     plugin-fw/lib/yit-plugin-panel.php
     */
    public function add_menu_page() {
        if ( ! empty( $this->_panel ) ) {
            return;
        }

        $admin_tabs = array(
            'mail'      => __( 'Mail Settings', 'ywrr' ),
            'blocklist' => __( 'Blocklist', 'ywrr' )
        );

        if ( defined( 'YWRR_PREMIUM' ) ) {
            $admin_tabs['settings'] = __( 'Request Settings', 'ywrr' );
            $admin_tabs['mandrill'] = __( 'Mandrill Settings', 'ywrr' );
            $admin_tabs['schedule'] = __( 'Schedule List', 'ywrr' );
        } else {
            $admin_tabs['premium-landing'] = __( 'Premium Version', 'ywrr' );
        }

        $args = array(
            'create_menu_page' => true,
            'parent_slug'      => '',
            'page_title'       => __( 'Review Reminder', 'ywrr' ),
            'menu_title'       => __( 'Review Reminder', 'ywrr' ),
            'capability'       => 'manage_options',
            'parent'           => '',
            'parent_page'      => 'yit_plugin_panel',
            'page'             => $this->_panel_page,
            'admin-tabs'       => $admin_tabs,
            'options-path'     => YWRR_DIR . '/plugin-options'
        );

        $this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );
    }

    /**
     * Premium Tab Template
     *
     * Load the premium tab template on admin page
     *
     * @since   1.0.0
     * @return  void
     * @author  Andrea Grillo <andrea.grillo@yithemes.com>
     */
    public function premium_tab() {
        $premium_tab_template = YWRR_TEMPLATE_PATH . '/admin/' . $this->_premium;
        if ( file_exists( $premium_tab_template ) ) {
            include_once( $premium_tab_template );
        }
    }

    /**
     * Creates a custom post status for unsubscribe page in order to avoid visibility of page in automatic menus
     *
     * @since   1.0.0
     * @return  void
     * @author  Alberto Ruggiero
     */
    public function ywrr_post_status() {
        register_post_status( 'ywrr-unsubscribe', array(
            'label'                     => __( 'Unsubscribe Page', 'ywrr' ),
            'public'                    => true,
            'exclude_from_search'       => true,
            'show_in_admin_all_list'    => false,
            'show_in_admin_status_list' => false
        ) );
    }

    /**
     * Creates the unsubscribe page
     *
     * @since   1.0.0
     * @return  void
     * @author  Alberto Ruggiero
     */
    public function ywrr_create_pages() {

        if (! function_exists( 'wc_create_page' ) ) return;

        $pages = apply_filters( 'woocommerce_create_pages', array(
            'unsubscribe' => array(
                'name'    => _x( 'unsubscribe', 'Page slug', 'ywrr' ),
                'title'   => _x( 'Unsubscribe', 'Page title', 'ywrr' ),
                'content' => '[ywrr_unsubscribe]'
            )
        ) );

        foreach ( $pages as $key => $page ) {
            wc_create_page( esc_sql( $page['name'] ), 'ywrr_' . $key . '_page_id', $page['title'], $page['content'], ! empty( $page['parent'] ) ? wc_get_page_id( $page['parent'] ) : '' );
        }

        $unsubscribe_page = array(
            'ID'            => get_option( 'ywrr_unsubscribe_page_id' ),
            'post_status'   => 'ywrr-unsubscribe'
        );

        wp_update_post($unsubscribe_page);
    }

    /**
     * Add the YWRR_Request_Mail class to WooCommerce mail classes
     *
     * @since   1.0.0
     * @param   $email_classes
     * @return  array
     * @author  Alberto Ruggiero
     */
    public function ywrr_custom_email( $email_classes ) {

        foreach( $this->_email_types as $type => $email_type ){
            $email_classes[ $email_type['class'] ]     = include( "includes/emails/{$email_type['file']}" );
        }

        return $email_classes;
    }

    /**
     * Notifies the inability to delete the page
     *
     * @since   1.0.0
     * @return  void
     * @author  Alberto Ruggiero
     */
    public function ywrr_protect_unsubscribe_page_notice() {
        global $post_type, $pagenow;

        if( $pagenow == 'edit.php' && $post_type == 'page' && isset( $_GET['impossible'] ) ) {
            echo '<div id="message" class="error"><p>' . __( 'The unsubscribe page cannot be deleted','ywrr' ).'</p></div>';
        }
    }

    /**
     * Prevent the deletion of unsubscribe page
     *
     * @since   1.0.0
     * @param   $post_id
     * @return  void
     * @author  Alberto Ruggiero
     */
    public function ywrr_protect_unsubscribe_page( $post_id ) {
        if( $post_id == get_option( 'ywrr_unsubscribe_page_id' ) ) {

            $query_args = array(
                'post_type'     => 'page',
                'impossible'    => '1'
            );
            $error_url = esc_url( add_query_arg( $query_args, admin_url( 'edit.php' ) ) );

            wp_redirect( $error_url );
            exit();
        }
    }

    /**
     * Unsubscribe page shortcode.
     *
     * @since   1.0.0
     * @return  string
     * @author  Alberto Ruggiero
     */
    public function ywrr_unsubscribe() {
        echo '<div class ="woocommerce">';

        wc_get_template( 'unsubscribe.php', array(), YWRR_TEMPLATE_PATH, YWRR_TEMPLATE_PATH );

        echo '</div>';
    }

    /**
     * Action Links
     *
     * add the action links to plugin admin page
     *
     * @since   1.0.0
     * @param   $links | links plugin array
     * @return  mixed
     * @author  Andrea Grillo <andrea.grillo@yithemes.com>
     * @use     plugin_action_links_{$plugin_file_name}
     */
    public function action_links( $links ) {

        $links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'ywrr' ) . '</a>';

        if ( defined( 'YWRR_FREE_INIT' ) ) {
            $links[] = '<a href="' . $this->_premium_landing . '" target="_blank">' . __( 'Premium Version', 'ywrr' ) . '</a>';
        }

        return $links;
    }

    /**
     * plugin_row_meta
     *
     * add the action links to plugin admin page
     *
     * @since   1.0.0
     * @param   $plugin_meta
     * @param   $plugin_file
     * @param   $plugin_data
     * @param   $status
     * @return  Array
     * @author  Andrea Grillo <andrea.grillo@yithemes.com>
     * @use     plugin_row_meta
     */
    public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
        if ( ( defined( 'YWRR_INIT' ) && ( YWRR_INIT == $plugin_file ) ) ||
            ( defined( 'YWRR_FREE_INIT' ) && ( YWRR_FREE_INIT == $plugin_file ) )
        ) {

            $plugin_meta[] = '<a href="' . $this->_official_documentation . '" target="_blank">' . __( 'Plugin Documentation', 'ywrr' ) . '</a>';
        }

        return $plugin_meta;
    }

    /**
     * Creates database table for blocklist e scheduling
     *
     * @since   1.0.0
     * @return  void
     * @author  Alberto Ruggiero
     */
    static function ywrr_create_tables() {
        global $wpdb;

        $wpdb->hide_errors();

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty($wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }
            if ( ! empty($wpdb->collate ) ) {
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

    /**
     * Creates a cron job to handle daily mail send
     *
     * @since   1.0.0
     * @return  void
     * @author  Alberto Ruggiero
     */
    static function ywrr_create_schedule_job () {
        wp_schedule_event( time(), 'daily', 'ywrr_daily_send_mail_job' );
    }

    /**
     * Removes cron job
     *
     * @since   1.0.0
     * @return  void
     * @author  Alberto Ruggiero
     */
    static function ywrr_create_unschedule_job () {
        wp_clear_scheduled_hook( 'ywrr_daily_send_mail_job' );
    }

    /**
     * Get the premium landing uri
     *
     * @since   1.0.0
     * @return  string
     * @author  Alberto Ruggiero
     */
    public function get_premium_landing_uri() {
        return defined( 'YITH_REFER_ID' ) ? $this->_premium_landing . '?refer_id=' . YITH_REFER_ID : $this->_premium_landing;
    }

}