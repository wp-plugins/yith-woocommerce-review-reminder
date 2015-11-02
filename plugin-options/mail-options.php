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

global $YWRR_Review_Reminder;

$custom_template = !defined( 'YWRR_PREMIUM' ) ? '' : array(
    'name'    => __( 'Mail template', 'yith-woocommerce-review-reminder' ),
    'type'    => 'custom-select',
    'desc'    => '',
    'options' => array(
        'base'      => __( 'Woocommerce Template', 'yith-woocommerce-review-reminder' ),
        'premium-1' => __( 'Template 1', 'yith-woocommerce-review-reminder' ),
        'premium-2' => __( 'Template 2', 'yith-woocommerce-review-reminder' ),
        'premium-3' => __( 'Template 3', 'yith-woocommerce-review-reminder' ),
    ),
    'default' => 'base',
    'id'      => 'ywrr_mail_template'
);

$item_link = !defined( 'YWRR_PREMIUM' ) ? '' : array(
    'name'    => __( 'Set Destination', 'yith-woocommerce-review-reminder' ),
    'type'    => 'select',
    'desc'    => __( 'Set the destination you want to show in the email', 'yith-woocommerce-review-reminder' ),
    'options' => array(
        'product' => __( 'Product page', 'yith-woocommerce-review-reminder' ),
        'review'  => __( 'Default WooCommerce Reviews Tab', 'yith-woocommerce-review-reminder' ),
        'custom'  => __( 'Custom Anchor', 'yith-woocommerce-review-reminder' ),
    ),
    'default' => 'product',
    'id'      => 'ywrr_mail_item_link'
);

$item_link_hash = !defined( 'YWRR_PREMIUM' ) ? '' : array(
    'name' => __( 'Set Custom Anchor', 'yith-woocommerce-review-reminder' ),
    'type' => 'text',
    'desc' => '',
    'id'   => 'ywrr_mail_item_link_hash',
);

$email_templates_enable = ( defined( 'YWRR_PREMIUM' ) && defined( 'YITH_WCET_PREMIUM' ) ) ? array(
    'name'    => __( 'Use YITH WooCommerce Email Templates', 'yith-woocommerce-review-reminder' ),
    'type'    => 'checkbox',
    'desc'    => '',
    'id'      => 'ywrr_mail_template_enable',
    'default' => 'no',
) : '';

$videobox = defined( 'YWRR_PREMIUM' ) ? '' : array(
    'name'    => __( 'Upgrade to the PREMIUM VERSION', 'yith-woocommerce-review-reminder' ),
    'type'    => 'videobox',
    'default' => array(
        'plugin_name'               => __( 'YITH WooCommerce Review Reminder', 'yith-woocommerce-review-reminder' ),
        'title_first_column'        => __( 'Discover the Advanced Features', 'yith-woocommerce-review-reminder' ),
        'description_first_column'  => __( 'Upgrade to the PREMIUM VERSION of YITH WooCommerce Review Reminder to benefit from all features!', 'yith-woocommerce-review-reminder' ),
        'video'                     => array(
            'video_id'          => '118824650',
            'video_image_url'   => YWRR_ASSETS_URL . '/images/yith-woocommerce-review-reminder.jpg',
            'video_description' => __( 'YITH WooCommerce Review Reminder', 'yith-woocommerce-review-reminder' ),
        ),
        'title_second_column'       => __( 'Get Support and Pro Features', 'yith-woocommerce-review-reminder' ),
        'description_second_column' => __( 'By purchasing the premium version of the plugin, you will take advantage of the advanced features of the product and you will get one year of free updates and support through our platform available 24h/24.', 'yith-woocommerce-review-reminder' ),
        'button'                    => array(
            'href'  => $YWRR_Review_Reminder->get_premium_landing_uri(),
            'title' => 'Get Support and Pro Features'
        )
    ),
    'id'      => 'ywrr_general_videobox'
);

$placeholder_desc = '<b>{customer_name}</b> ' . __( 'Replaced with the customer\'s name', 'yith-woocommerce-review-reminder' ) . '<br /><br />';
$placeholder_desc .= '<b>{customer_email}</b> ' . __( 'Replaced with the customer\'s email', 'yith-woocommerce-review-reminder' ) . '<br /><br />';
$placeholder_desc .= '<b>{site_title}</b> ' . __( 'Replaced with the site title', 'yith-woocommerce-review-reminder' ) . '<br /><br />';
$placeholder_desc .= '<b>{order_id}</b> ' . __( 'Replaced with the order ID', 'yith-woocommerce-review-reminder' ) . '<br /><br />';
$placeholder_desc .= '<b>{order_date}</b> ' . __( 'Replaced with the date and time of the order', 'yith-woocommerce-review-reminder' ) . '<br /><br />';
$placeholder_desc .= '<b>{order_date_completed}</b> ' . __( 'Replaced with the date the order was marked completed', 'yith-woocommerce-review-reminder' ) . '<br /><br />';
$placeholder_desc .= '<b>{order_list}</b> ' . __( 'Replaced with a list of products purchased but not reviewed (Do not forget it!!!)', 'yith-woocommerce-review-reminder' ) . '<br /><br />';
$placeholder_desc .= '<b>{days_ago}</b> ' . __( 'Replaced with the days ago the order was made', 'yith-woocommerce-review-reminder' ) . '<br /><br />';

return array(
    'mail' => array(
        'section_general_settings_videobox'     => $videobox,

        'review_reminder_general_title'         => array(
            'name' => __( 'General Settings', 'yith-woocommerce-review-reminder' ),
            'type' => 'title',
            'desc' => '',
        ),
        'review_reminder_general_enable_plugin' => array(
            'name'    => __( 'Enable YITH WooCommerce Review Reminder', 'yith-woocommerce-review-reminder' ),
            'type'    => 'checkbox',
            'desc'    => '',
            'id'      => 'ywrr_enable_plugin',
            'default' => 'yes',
        ),
        'review_reminder_general_end'           => array(
            'type' => 'sectionend',
        ),
        'review_reminder_mail_section_title'    => array(
            'name' => __( 'Mail Settings', 'yith-woocommerce-review-reminder' ),
            'type' => 'title',
            'desc' => '',
        ),
        'review_reminder_mail_type'             => array(
            'name'    => __( 'Email type', 'yith-woocommerce-review-reminder' ),
            'type'    => 'select',
            'desc'    => __( 'Choose which format of email to send.', 'yith-woocommerce-review-reminder' ),
            'options' => array(
                'html'  => __( 'HTML', 'yith-woocommerce-review-reminder' ),
                'plain' => __( 'Plain text', 'yith-woocommerce-review-reminder' )
            ),
            'default' => 'html',
            'id'      => 'ywrr_mail_type'
        ),
        'review_reminder_mail_subject'          => array(
            'name'              => __( 'Email subject', 'yith-woocommerce-review-reminder' ),
            'type'              => 'text',
            'desc'              => '',
            'id'                => 'ywrr_mail_subject',
            'default'           => __( '[{site_title}] Review recently purchased products', 'yith-woocommerce-review-reminder' ),
            'css'               => 'width: 400px;',
            'custom_attributes' => array(
                'required' => 'required'
            )
        ),
        'review_reminder_mail_body'             => array(
            'name'              => __( 'Email content', 'yith-woocommerce-review-reminder' ),
            'type'              => 'customtext',
            'desc'              => $placeholder_desc,
            'id'                => 'ywrr_mail_body',
            'default'           => __( 'Hello {customer_name},
Thank you for purchasing items from the {site_title} shop!
We would love if you could help us and other customers by reviewing the products you recently purchased.
It only takes a minute and it would really help others by giving them an idea of your experience.
Click the link below for each product and review the product under the \'Reviews\' tab.

{order_list}

Much appreciated,

{site_title}.', 'yith-woocommerce-review-reminder' ),
            'css'               => 'width: 100%; height:300px; resize: none;',
            'custom_attributes' => array(
                'required' => 'required'
            )
        ),
        'review_reminder_mail_item_link'        => $item_link,
        'review_reminder_mail_item_link_hash'   => $item_link_hash,
        'review_reminder_mail_template_enable'  => $email_templates_enable,
        'review_reminder_mail_template'         => $custom_template,
        'review_reminder_mail_unsubscribe_text' => array(
            'name'              => __( 'Review unsubscription text', 'yith-woocommerce-review-reminder' ),
            'type'              => 'text',
            'desc'              => '',
            'id'                => 'ywrr_mail_unsubscribe_text',
            'default'           => __( 'Unsubscribe from review emails', 'yith-woocommerce-review-reminder' ),
            'css'               => 'width: 400px;',
            'custom_attributes' => array(
                'required' => 'required'
            )
        ),
        'review_reminder_mail_section_end'      => array(
            'type' => 'sectionend',
        )
    )

);