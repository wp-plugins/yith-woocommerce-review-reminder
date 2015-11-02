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

if ( !class_exists( 'YWRR_Form_Handler' ) ) {

    /**
     * Handle frontend forms
     *
     * @class   YWRR_Form_Handler
     * @package Yithemes
     * @since   1.0.0
     * @author  Your Inspiration Themes
     *
     */
    class YWRR_Form_Handler {

        /**
         * Single instance of the class
         *
         * @var \YWRR_Form_Handler
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return \YWRR_Form_Handler
         * @since 1.0.0
         */
        public static function get_instance() {

            if ( is_null( self::$instance ) ) {

                self::$instance = new self( $_REQUEST );

            }

            return self::$instance;
        }

        /**
         * Constructor
         *
         * @since   1.0.0
         * @return  mixed
         * @author  Alberto Ruggiero
         */
        public function __construct() {

        }

        /**
         * Handles the unsubscribe form
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function unsubscribe_review_request() {

            if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
                return;
            }

            if ( empty( $_POST['action'] ) || 'unsubscribe_review_request' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'unsubscribe_review_request' ) ) {
                return;
            }

            $customer_email = !empty( $_POST['account_email'] ) ? sanitize_email( $_POST['account_email'] ) : '';

            if ( empty( $customer_email ) || !is_email( $customer_email ) ) {
                wc_add_notice( __( 'Please provide a valid email address.', 'yith-woocommerce-review-reminder' ), 'error' );
            }
            elseif ( $customer_email !== urldecode( base64_decode( $_GET['email'] ) ) ) {
                wc_add_notice( __( 'Please retype the email address as provided.', 'yith-woocommerce-review-reminder' ), 'error' );
            }

            if ( wc_notice_count( 'error' ) === 0 ) {
                $wc_email = WC_Emails::instance();
                $email    = $wc_email->emails['YWRR_Unsubscribe_Mail'];

                $email->trigger( $customer_email );

                wc_add_notice( __( 'An email has been sent with your request', 'yith-woocommerce-review-reminder' ) );
                wp_safe_redirect( get_permalink( get_option( 'ywrr_unsubscribe_page_id' ) ) );
                exit;
            }
        }

    }

    /**
     * Unique access to instance of YWRR_Form_Handler class
     *
     * @return \YWRR_Form_Handler
     */
    function YWRR_Form_Handler() {

        return YWRR_Form_Handler::get_instance();

    }

}
