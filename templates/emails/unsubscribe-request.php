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

/**
 * Implements Unsubscribe Mail for YWRR plugin (HTML)
 *
 * @class   YWRR_Unsubscribe_Mail
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 */

do_action( 'woocommerce_email_header', $email_heading ); ?>

    <p><?php printf( __( 'You have received a request for unsubscription. Email address is the following: %s', 'yith-woocommerce-review-reminder' ), '<b>' . $customer_mail . '</b>' ) ?></p>

<?php

do_action( 'woocommerce_email_footer' );