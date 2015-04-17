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
 * Unsubscribe page shortcode template
 *
 * @package Yithemes
 * @since 1.0.0
 * @author Your Inspiration Themes
 */

$form_action = 'unsubscribe_review_request' . ( defined( 'YWRR_PREMIUM' ) ? '_premium' : '');

wc_print_notices();

if( isset( $_GET[ 'email' ] )) {
    ?>
    <p><?php printf( __( 'If you don\'t want to receive any more review requests, please retype your email address: %s', 'ywrr' ), '<b>' . urldecode( base64_decode( $_GET[ 'email' ] ) ) . '</b>' ) ?></p>
    <form method="post" action="">
        <p class="form-row form-row-wide">
            <label for="account_email"><?php _e( 'Email address', 'ywrr' ); ?> <span class="required">*</span></label>
            <input type="email" class="input-text" name="account_email" id="account_email"/>
        </p>
        <p>
            <?php wp_nonce_field( $form_action ); ?>
            <input type="submit" class="button" name="<?php echo $form_action; ?>" value="<?php _e( 'Unsubscribe', 'ywrr' ); ?>"/>
            <input type="hidden" name="account_id" value="<?php echo urldecode( base64_decode( $_GET[ 'id' ] ) ); ?>"/>
            <input type="hidden" name="action" value="<?php echo $form_action; ?>"/>
        </p>
    </form>
<?php
} else {
    ?>
    <p class="return-to-shop"><a class="button wc-backward" href="<?php echo get_home_url(); ?>"><?php _e( 'Return To Home Page', 'ywrr' ) ?></a></p>
<?php
}