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
 * Implements email functions for YWRR plugin
 *
 * @class   YWRR_Emails
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 *
 */
class YWRR_Emails {

    /**
     * Prepares and send the review request mail
     *
     * @since   1.0.0
     * @param   $order_id int the order id
     * @param   $days
     * @param   $items_to_review
     * @param   $stored_items
     * @return  void
     * @author  Alberto Ruggiero
     */
    static function send_email( $order_id, $days, $items_to_review = array(), $stored_items = array() ){

        $list       = YWRR_Emails::get_review_list( $order_id );
        $wc_email   = WC_Emails::instance();
        $email      = $wc_email->emails['YWRR_Request_Mail'];

        $email->trigger( $order_id, $list, $days );

    }

    /**
     * Prepares the list of items to review from stored options
     *
     * @since   1.0.0
     * @param   $order_id int the order id
     * @return  array
     * @author  Alberto Ruggiero
     */
    static function get_review_list( $order_id ) {
        global $wpdb;

        $items      = array();

                $line_items = $wpdb->get_results( $wpdb->prepare( "
                    SELECT    a.order_item_name,
                              MAX(CASE WHEN b.meta_key = '_product_id' THEN b.meta_value ELSE NULL END) AS product_id
                    FROM      {$wpdb->prefix}woocommerce_order_items a INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta b ON a.order_item_id= b.order_item_id
                    WHERE     a.order_id = %d AND a.order_item_type = 'line_item'
                    GROUP BY  a.order_item_name
                    ORDER BY  a.order_item_id ASC
                    ", $order_id ) );


        foreach ( $line_items as $item ) {
            $items[ $item->product_id ]['name']  = $item->order_item_name;
            $items[ $item->product_id ]['id']    = $item->product_id;
        }

        return $items;
    }

}