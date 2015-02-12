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

if ( ! class_exists( 'YWRR_Unsubscribe_Mail' ) ) :

/**
 * Implements Unsubscribe Mail for YWRR plugin
 *
 * @class   YWRR_Unsubscribe_Mail
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 * @extends WC_Email
 *
 */
class YWRR_Unsubscribe_Mail extends WC_Email {

    /**
     * @var string $customer_mail email of the customer that wants to unsubscribe
     */
    var $customer_mail;

    /**
     * Constructor
     *
     * Initialize email type and set templates paths
     *
     * @since   1.0.0
     * @author  Alberto Ruggiero
     */
    public function __construct() {

        $this->template_html 	= 'emails/unsubscribe-request.php';
        $this->template_plain 	= 'emails/plain/unsubscribe-request.php';


        parent::__construct();

        // Other settings
        $this->recipient = $this->get_option( 'recipient' );

        if ( ! $this->recipient )
            $this->recipient = get_option( 'admin_email' );

    }

    /**
     * Trigger email send
     *
     * @param   $customer_mail string email of the customer that wants to unsubscribe
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  void
     */
    public function trigger( $customer_mail ) {

        if ( $customer_mail ) {
            $this->email_type   = get_option( 'ywrr_mail_type' );
            $this->heading      = __( 'Unsubscribe Request', 'ywrr' );
            $this->subject      = __( 'Unsubscribe Request', 'ywrr' );

            $this->customer_mail    = $customer_mail;

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
            'email_heading' => $this->get_heading(),
            'customer_mail' => $this->customer_mail,
            'sent_to_admin' => true,
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
            'email_heading' => $this->get_heading(),
            'customer_mail' => $this->customer_mail,
            'sent_to_admin' => true,
            'plain_text'    => true
        ), YWRR_TEMPLATE_PATH , YWRR_TEMPLATE_PATH );
        return ob_get_clean();
    }

}

endif;

return new YWRR_Unsubscribe_Mail();