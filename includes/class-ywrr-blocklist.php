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
    exit; // Exit if accessed directly
}

/**
 * Implements blocklist functions for YWRR plugin
 *
 * @class   YWRR_Blocklist
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 *
 */
class YWRR_Blocklist {

    /**
     * Check if the customer is in blocklist table
     *
     * @since   1.0.0
     * @param   $customer_id int the user id
     * @param   $customer_email string the user email
     * @return  bool
     * @author  Alberto Ruggiero
     */
    static function check_blocklist( $customer_id, $customer_email ) {
        global $wpdb;

        if( 0 == $customer_id) {
            $count = $wpdb->get_var( $wpdb->prepare( "
                    SELECT    COUNT(*)
                    FROM      {$wpdb->prefix}ywrr_email_blocklist
                    WHERE     customer_email = %s
                    ", $customer_email ) );
        } else {
            $count = $wpdb->get_var( $wpdb->prepare( "
                    SELECT    COUNT(*)
                    FROM      {$wpdb->prefix}ywrr_email_blocklist
                    WHERE     customer_id = %d
                    ", $customer_id ) );
        }

        return ( $count >= 1 ? false : true );
    }

    /**
     * Add customer to blocklist table
     *
     * @since   1.0.0
     * @param   $customer_id int the user id
     * @param   $customer_email string the user email
     * @return  void
     * @author  Alberto Ruggiero
     */
    static function add_to_blocklist( $customer_id, $customer_email ) {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'ywrr_email_blocklist',
            array(
                'customer_email'    => $customer_email,
                'customer_id'       => $customer_id
            ),
            array(
                '%s',
                '%d'
            )
        );
    }

}