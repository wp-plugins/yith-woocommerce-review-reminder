<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! class_exists( 'YWRR_Request_Mail' ) ) :

/**
 * Implements Request Mail for YWRR plugin
 *
 * @class   YWRR_Request_Mail
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 * @extends WC_Email
 *
 */
class YWRR_Request_Mail extends WC_Email {

    /**
     * @var int $days_ago number of days after order completion
     */
    var $days_ago;

    /**
     * @var array $item_list list of item to review
     */
    var $item_list;

    /**
     * @var string $review_list processed list of items in HTML or Plain mode
     */
    var $review_list;

    /**
     * Constructor
     *
     * Initialize email type and set templates paths
     *
     * @since   1.0.0
     * @author  Alberto Ruggiero
     */
    public function __construct() {

        $this->title            = __( 'Review reminder','ywrr' );
        $this->template_html 	= 'emails/review-request.php';
        $this->template_plain 	= 'emails/plain/review-request.php';

        parent::__construct();
    }

    /**
     * Trigger email send
     *
     * @param   $order_id int the order id
     * @param   $item_list array the list of items to review
     * @param   $days_ago int number of days after order completion
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  void
     */
    public function trigger( $order_id, $item_list, $days_ago ) {

        if ( $order_id ) {
            $this->email_type   = get_option( 'ywrr_mail_type' );
            $this->heading      = get_option( 'ywrr_mail_subject' );
            $this->subject      = get_option( 'ywrr_mail_subject' );

            $this->object 		= wc_get_order( $order_id );
            $this->recipient	= $this->object->billing_email;
            $this->days_ago     = $days_ago;
            $this->item_list    = $item_list;

            $this->find['site-title']    = '{site_title}';
            $this->replace['site-title'] = $this->get_blogname();
        }

        if ( ! $this->get_recipient() ) {
            return;
        }

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), "" );
    }

    /**
     * Get HTML content
     *
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  string
     */
    function get_content_html() {
        ob_start();
        wc_get_template( $this->template_html, array(
            'order' 		=> $this->object,
            'email_heading' => $this->get_heading(),
            'days_ago'      => $this->days_ago,
            'item_list'     => $this->item_list,
            'review_list'   => $this->review_list,
            'sent_to_admin' => false,
            'plain_text'    => false
        ), YWRR_TEMPLATE_PATH , YWRR_TEMPLATE_PATH );
        return ob_get_clean();
    }

    /**
     * Get Plain content
     *
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  string
     */
    function get_content_plain() {
        ob_start();
        wc_get_template( $this->template_plain, array(
            'order' 		=> $this->object,
            'email_heading' => $this->get_heading(),
            'days_ago'      => $this->days_ago,
            'item_list'     => $this->item_list,
            'review_list'   => $this->review_list,
            'sent_to_admin' => false,
            'plain_text'    => true
        ), YWRR_TEMPLATE_PATH , YWRR_TEMPLATE_PATH );
        return ob_get_clean();
    }

    /**
     * Admin Panel Options Processing
     * - Saves the options to the DB
     *
     * @since 1.0.0
     * @return boolean|null
     */
    public function process_admin_options() {
        woocommerce_update_options( $this->form_fields['mail'] );
    }

    /**
     * Setup email settings screen.
     *
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  string
     */
    public function admin_options() {
        ?>
        <table class="form-table">
            <?php woocommerce_admin_fields( $this->form_fields['mail'] ); ?>
        </table>
            <?php

    }

    /**
     * Initialise Settings Form Fields
     *
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  void
     */
    function init_form_fields() {
        $this->form_fields = include( YWRR_DIR . '/plugin-options/mail-options.php' );
    }
}

endif;

return new YWRR_Request_Mail();