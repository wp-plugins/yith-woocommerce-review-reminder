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

/**
 * Implements Request Mail for YWRR plugin (HTML)
 *
 * @class YWRR_Request_Mail
 * @package Yithemes
 * @since 1.0.0
 * @author Your Inspiration Themes
 */

$customer_id = $order->__get( 'user_id' );

$query_args = array(
    'id'    => urlencode( base64_encode( ! empty( $customer_id ) ? $customer_id : 0 ) ),
    'email' => urlencode( base64_encode( $order->billing_email ) )
);
$unsubscribe = add_query_arg( $query_args, get_permalink( get_option( 'ywrr_unsubscribe_page_id' ) ) );

if( defined( 'YWRR_PREMIUM' ) ){
    $review_list = YWRR_Review_Reminder_Premium::ywrr_email_items_list( $item_list );
} else {
    $review_list = include_once( YWRR_TEMPLATE_PATH . 'emails/email-items-list.php' );
}

$find = array(
	'{customer_name}',
	'{customer_email}',
	'{site_title}',
	'{order_id}',
	'{order_date}',
	'{order_date_completed}',
	'{order_list}',
	'{days_ago}'
);

$replace = array(
    '<b>' . $order->billing_first_name . '</b>',
    '<b>' . $order->billing_email . '</b>',
	'<b>' . get_option( 'blogname' ) . '</b>',
    '<b>' . $order->id . '</b>',
    '<b>' . $order->order_date . '</b>',
    '<b>' . $order->modified_date . '</b>',
	$review_list,
    '<b>' . $days_ago . '</b>'
);

$mail_body = str_replace($find, $replace, get_option( 'ywrr_mail_body' ));

if( defined( 'YWRR_PREMIUM' ) ){
    do_action( 'ywrr_email_header', $email_heading );
}else{
    do_action( 'woocommerce_email_header', $email_heading );
} ?>

<p><?php echo wpautop( $mail_body ); ?></p>

<?php
if( defined( 'YWRR_PREMIUM' ) ) {
    do_action( 'ywrr_email_footer', $unsubscribe );
}else{
    echo '<p><a href="' . $unsubscribe .  '">' . get_option( 'ywrr_mail_unsubscribe_text' ) . '</a></p>';
    do_action( 'woocommerce_email_footer' );
}