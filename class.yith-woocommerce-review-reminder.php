<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'YWRR_Review_Reminder' ) ) {

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
         * @var array
         */
        var $_email_templates = array();

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

            if ( !function_exists( 'WC' ) ) {
                return;
            }

            $this->_email_types = array(
                'request'     => array(
                    'class' => 'YWRR_Request_Mail',
                    'file'  => 'class-ywrr-request-email.php',
                    'hide'  => false,
                ),
                'unsubscribe' => array(
                    'class' => 'YWRR_Unsubscribe_Mail',
                    'file'  => 'class-ywrr-unsubscribe-email.php',
                    'hide'  => true,
                ),
            );

            // Load Plugin Framework
            add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 12 );

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

            if ( is_admin() ) {
                add_filter( 'set-screen-option', 'YWRR_Blocklist_Table::set_options', 10, 3 );

                add_action( 'woocommerce_admin_field_customtext', 'YWRR_Custom_Textarea::output' );
                add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'save_ywrr_textarea' ), 10, 3 );

                add_action( 'ywrr_blocklist', 'YWRR_Blocklist_Table::output' );
                add_action( 'current_screen', 'YWRR_Blocklist_Table::add_options' );
                add_action( 'admin_notices', array( $this, 'ywrr_protect_unsubscribe_page_notice' ) );
                add_action( 'wp_trash_post', array( $this, 'ywrr_protect_unsubscribe_page' ), 10, 1 );
                add_action( 'before_delete_post', array( $this, 'ywrr_protect_unsubscribe_page' ), 10, 1 );
            }
            else {
                add_action( 'template_redirect', array( YWRR_Form_Handler(), 'unsubscribe_review_request' ) );
                add_shortcode( 'ywrr_unsubscribe', array( $this, 'ywrr_unsubscribe' ) );
                add_filter( 'wp_get_nav_menu_items', array( $this, 'ywrr_hide_unsubscribe_page' ), 10, 3 );
            }

            if ( get_option( 'ywrr_enable_plugin' ) == 'yes' ) {

                add_action( 'woocommerce_order_status_completed', array( YWRR_Schedule(), 'schedule_mail' ) );
                add_action( 'ywrr_daily_send_mail_job', array( YWRR_Schedule(), 'daily_schedule' ) );

            }

            add_action( 'init', array( $this, 'ywrr_post_status' ) );
            add_action( 'init', array( $this, 'ywrr_create_pages' ) );

            add_filter( 'woocommerce_email_classes', array( $this, 'ywrr_custom_email' ) );
            add_filter( 'woocommerce_get_sections_email', array( $this, 'ywrr_hide_sections' ) );

            add_option( 'ywrr_mail_schedule_day', 7 );
            add_option( 'ywrr_mail_template', 'base' );

            add_action( 'ywrr_email_header', array( $this, 'ywrr_email_header' ), 10, 2 );
            add_action( 'ywrr_email_footer', array( $this, 'ywrr_email_footer' ), 10, 2 );

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

            if ( !is_admin() || defined( 'DOING_AJAX' ) ) {
                include_once( 'includes/class-ywrr-form-handler.php' );
            }

        }

        /**
         * Saves custom textarea content
         *
         * @since   1.0.6
         *
         * @param $value
         * @param $option
         * @param $raw_value
         *
         * @return string
         * @author  Alberto ruggiero
         */
        public function save_ywrr_textarea( $value, $option, $raw_value ) {

            if ( $option['type'] == 'customtext' ) {
                $value = wp_kses_post( trim( $raw_value ) );

            }

            return $value;

        }

        /**
         * Get the email header.
         *
         * @since   1.0.0
         *
         * @param   $email_heading
         * @param   $template
         *
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function ywrr_email_header( $email_heading, $template = false ) {

            if ( !$template ) {
                $template = get_option( 'ywrr_mail_template' );
            }

            if ( array_key_exists( $template, $this->_email_templates ) ) {
                $path   = $this->_email_templates[$template]['path'];
                $folder = $this->_email_templates[$template]['folder'];

                wc_get_template( $folder . '/email-header.php', array( 'email_heading' => $email_heading ), $path, $path );

            }
            else {
                wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading, 'mail_type' => 'yith-review-reminder' ) );

            }

        }

        /**
         * Get the email footer.
         *
         * @since   1.0.0
         *
         * @param   $unsubscribe
         * @param   $template
         *
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function ywrr_email_footer( $unsubscribe, $template = false ) {

            if ( !$template ) {
                $template = get_option( 'ywrr_mail_template' );
            }

            if ( array_key_exists( $template, $this->_email_templates ) ) {
                $path   = $this->_email_templates[$template]['path'];
                $folder = $this->_email_templates[$template]['folder'];

                wc_get_template( $folder . '/email-footer.php', array( 'unsubscribe' => $unsubscribe ), $path, $path );

            }
            else {
                echo '<p><a href="' . $unsubscribe . '">' . get_option( 'ywrr_mail_unsubscribe_text' ) . '</a></p>';
                wc_get_template( 'emails/email-footer.php', array( 'mail_type' => 'yith-review-reminder' ) );
            }

        }

        /**
         * Set the list item for the selected template.
         *
         * @since   1.0.0
         *
         * @param   $item_list
         * @param   $template
         *
         * @return  string
         * @author  Alberto Ruggiero
         */
        public function ywrr_email_items_list( $item_list, $template = false ) {

            if ( !$template ) {
                $template = get_option( 'ywrr_mail_template' );
            }

            if ( array_key_exists( $template, $this->_email_templates ) ) {

                $path   = $this->_email_templates[$template]['path'];
                $folder = $this->_email_templates[$template]['folder'];

                $style = include( $path . $folder . '/email-items-list.php' );

            }
            elseif ( defined( 'YITH_WCET_PREMIUM' ) && get_option( 'ywrr_mail_template_enable' ) == 'yes' ) {

                $style = include( YITH_WCET_TEMPLATE_PATH . '/emails/email-items-list.php' );

            }
            else {

                $style = include( YWRR_TEMPLATE_PATH . '/emails/email-items-list.php' );

            }

            return $style;

        }

        /**
         * ADMIN FUNCTIONS
         */

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
            if ( !empty( $this->_panel ) ) {
                return;
            }

            $admin_tabs = array(
                'mail'      => __( 'Mail Settings', 'yith-woocommerce-review-reminder' ),
                'blocklist' => __( 'Blocklist', 'yith-woocommerce-review-reminder' )
            );

            if ( defined( 'YWRR_PREMIUM' ) ) {
                $admin_tabs['settings'] = __( 'Request Settings', 'yith-woocommerce-review-reminder' );
                $admin_tabs['mandrill'] = __( 'Mandrill Settings', 'yith-woocommerce-review-reminder' );
                $admin_tabs['schedule'] = __( 'Schedule List', 'yith-woocommerce-review-reminder' );
            }
            else {
                $admin_tabs['premium-landing'] = __( 'Premium Version', 'yith-woocommerce-review-reminder' );
            }

            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'page_title'       => __( 'Review Reminder', 'yith-woocommerce-review-reminder' ),
                'menu_title'       => __( 'Review Reminder', 'yith-woocommerce-review-reminder' ),
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
         * Hides custom email settings from WooCommerce panel
         *
         * @since   1.0.0
         *
         * @param   $sections
         *
         * @return  array
         * @author  Andrea Grillo
         */
        public function ywrr_hide_sections( $sections ) {
            foreach ( $this->_email_types as $type => $email_type ) {
                $class_name = strtolower( $email_type['class'] );
                if ( isset( $sections[$class_name] ) && $email_type['hide'] == true ) {
                    unset( $sections[$class_name] );
                }
            }

            return $sections;
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
                'label'                     => __( 'Unsubscribe Page', 'yith-woocommerce-review-reminder' ),
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

            if ( !function_exists( 'wc_create_page' ) ) {
                return;
            }

            $pages = apply_filters( 'woocommerce_create_pages', array(
                'unsubscribe' => array(
                    'name'    => _x( 'unsubscribe', 'Page slug', 'yith-woocommerce-review-reminder' ),
                    'title'   => _x( 'Unsubscribe', 'Page title', 'yith-woocommerce-review-reminder' ),
                    'content' => '[ywrr_unsubscribe]'
                )
            ) );

            foreach ( $pages as $key => $page ) {
                wc_create_page( esc_sql( $page['name'] ), 'ywrr_' . $key . '_page_id', $page['title'], $page['content'], !empty( $page['parent'] ) ? wc_get_page_id( $page['parent'] ) : '' );
            }

            $unsubscribe_page = array(
                'ID'          => get_option( 'ywrr_unsubscribe_page_id' ),
                'post_status' => 'ywrr-unsubscribe'
            );

            wp_update_post( $unsubscribe_page );
        }

        /**
         * Add the YWRR_Request_Mail class to WooCommerce mail classes
         *
         * @since   1.0.0
         *
         * @param   $email_classes
         *
         * @return  array
         * @author  Alberto Ruggiero
         */
        public function ywrr_custom_email( $email_classes ) {

            foreach ( $this->_email_types as $type => $email_type ) {
                $email_classes[$email_type['class']] = include( "includes/emails/{$email_type['file']}" );
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

            if ( $pagenow == 'edit.php' && $post_type == 'page' && isset( $_GET['impossible'] ) ) {
                echo '<div id="message" class="error"><p>' . __( 'The unsubscribe page cannot be deleted', 'yith-woocommerce-review-reminder' ) . '</p></div>';
            }
        }

        /**
         * Prevent the deletion of unsubscribe page
         *
         * @since   1.0.0
         *
         * @param   $post_id
         *
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function ywrr_protect_unsubscribe_page( $post_id ) {
            if ( $post_id == get_option( 'ywrr_unsubscribe_page_id' ) ) {

                $query_args = array(
                    'post_type'  => 'page',
                    'impossible' => '1'
                );
                $error_url  = esc_url( add_query_arg( $query_args, admin_url( 'edit.php' ) ) );

                wp_redirect( $error_url );
                exit();
            }
        }

        /**
         * FRONTEND FUNCTIONS
         */

        /**
         * Hides unsubscribe page from menus
         *
         * @since   1.0.0
         *
         * @param   $items
         * @param   $menu
         * @param   $args
         *
         * @return  array
         * @author  Andrea Grillo
         */
        public function ywrr_hide_unsubscribe_page( $items, $menu, $args ) {

            foreach ( $items as $key => $value ) {
                if ( 'unsubscribe' === basename( $value->url ) ) {
                    unset( $items[$key] );
                }
            }

            return $items;

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
         * YITH FRAMEWORK
         */

        /**
         * Load plugin framework
         *
         * @since   1.0.0
         * @return  void
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function plugin_fw_loader() {
            if ( !defined( 'YIT_CORE_PLUGIN' ) ) {
                global $plugin_fw_data;
                if ( !empty( $plugin_fw_data ) ) {
                    $plugin_fw_file = array_shift( $plugin_fw_data );
                    require_once( $plugin_fw_file );
                }
            }
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
         * Action Links
         *
         * add the action links to plugin admin page
         *
         * @since   1.0.0
         *
         * @param   $links | links plugin array
         *
         * @return  mixed
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     plugin_action_links_{$plugin_file_name}
         */
        public function action_links( $links ) {

            $links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'yith-woocommerce-review-reminder' ) . '</a>';

            if ( defined( 'YWRR_FREE_INIT' ) ) {
                $links[] = '<a href="' . $this->_premium_landing . '" target="_blank">' . __( 'Premium Version', 'yith-woocommerce-review-reminder' ) . '</a>';
            }

            return $links;
        }

        /**
         * plugin_row_meta
         *
         * add the action links to plugin admin page
         *
         * @since   1.0.0
         *
         * @param   $plugin_meta
         * @param   $plugin_file
         * @param   $plugin_data
         * @param   $status
         *
         * @return  Array
         * @author  Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     plugin_row_meta
         */
        public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
            if ( ( defined( 'YWRR_INIT' ) && ( YWRR_INIT == $plugin_file ) ) ||
                ( defined( 'YWRR_FREE_INIT' ) && ( YWRR_FREE_INIT == $plugin_file ) )
            ) {

                $plugin_meta[] = '<a href="' . $this->_official_documentation . '" target="_blank">' . __( 'Plugin Documentation', 'yith-woocommerce-review-reminder' ) . '</a>';
            }

            return $plugin_meta;
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

}