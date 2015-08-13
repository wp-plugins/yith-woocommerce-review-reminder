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

$review_list = '';


foreach ( $item_list as $item ) {
    $product_link = apply_filters( 'ywrr_product_permalink', get_permalink( $item['id'] ) );

    $review_list .= '<li><a href="' . $product_link . '">' . $item['name'] . '</a></li>';
}

return '<ul>' . $review_list . '</ul>';