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
 * Implements scheduling functions for YWRR plugin
 *
 * @class   YWRR_Schedule
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 *
 */
class YWRR_Schedule {

    /**
     * Create a schedule record
     *
     * @since   1.0.0
     * @param   $order_id int the order id
     * @return  void
     * @author  Alberto Ruggiero
     */
    static function schedule_mail( $order_id ) {
        if( self::check_exists_schedule( $order_id ) == 0) {
            global $wpdb;

            $order          = wc_get_order( $order_id );
            $scheduled_date = date( 'Y-m-d', strtotime( current_time( 'mysql' ) . ' + ' . get_option( 'ywrr_mail_schedule_day' ) . ' days'));

            $wpdb->insert(
                $wpdb->prefix . 'ywrr_email_schedule',
                array(
                    'order_id'          => $order_id,
                    'mail_status'       => 'pending',
                    'scheduled_date'    => $scheduled_date,
                    'order_date'        => $order->modified_date
                ),
                array(
                    '%d',
                    '%s',
                    '%s',
                    '%s'
                )
            );
        }
    }

    /**
     * Checks if order has a scheduled email
     *
     * @since   1.0.0
     * @param   $order_id int the order id
     * @return  int
     * @author  Alberto Ruggiero
     */
    static function check_exists_schedule( $order_id ) {
        global $wpdb;

        $count = $wpdb->get_var( $wpdb->prepare( "
                    SELECT    COUNT(*)
                    FROM      {$wpdb->prefix}ywrr_email_schedule
                    WHERE     order_id = %d
                    ", $order_id ) );

        return $count;
    }

    /**
     * Changes email schedule status
     *
     * @since   1.0.0
     * @param   $order_id int the order id
     * @param   $status string the status of scheduling
     * @return  void
     * @author  Alberto Ruggiero
     */
    static function change_schedule_status( $order_id, $status = 'cancelled' ) {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'ywrr_email_schedule',
            array(
                'mail_status'   => $status,
                'request_items' => ''
            ),
            array( 'order_id'   => $order_id ),
            array(
                '%s'
            ),
            array( '%d' )
        );
    }

    /**
     * Handles the daily mail sending
     *
     * @since   1.0.0
     * @return  void
     * @author  Alberto Ruggiero
     */
    static function daily_schedule() {
        global $wpdb;

        $orders = $wpdb->get_results( $wpdb->prepare("
                    SELECT    order_id
                    FROM      {$wpdb->prefix}ywrr_email_schedule
                    WHERE     mail_status = 'pending' AND scheduled_date <= %s
                    ", current_time( 'mysql' ) ) );

        foreach ( $orders as $item ) {
            YWRR_Emails::send_email( $item->order_id, get_option( 'ywrr_mail_schedule_day' ));
            self::change_schedule_status( $item->order_id, 'sent' );
        }
    }

}