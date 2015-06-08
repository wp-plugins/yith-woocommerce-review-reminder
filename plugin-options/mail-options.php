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

global $YWRR_Review_Reminder;

$custom_template    = ! defined( 'YWRR_PREMIUM' ) ? '' : array(
    'name'      => __( 'Mail template', 'ywrr' ),
    'type'      => 'custom-select',
    'desc'      => '',
    'options'   => array(
        'base'        => __('Woocommerce Template', 'ywrr'),
        'premium-1'   => __('Template 1', 'ywrr'),
        'premium-2'   => __('Template 2', 'ywrr'),
        'premium-3'   => __('Template 3', 'ywrr')
    ),
    'default'   => 'base',
    'id'        => 'ywrr_mail_template'
);

$videobox           = defined( 'YWRR_PREMIUM' ) ? '' : array(
    'name'      => __( 'Upgrade to the PREMIUM VERSION', 'ywrr' ),
    'type'      => 'videobox',
    'default'   => array(
        'plugin_name'               => __( 'YITH WooCommerce Review Reminder', 'ywrr' ),
        'title_first_column'        => __( 'Discover the Advanced Features', 'ywrr' ),
        'description_first_column'  => __('Upgrade to the PREMIUM VERSION of YITH WooCommerce Review Reminder to benefit from all features!', 'ywrr'),
        'video'                     => array(
            'video_id'           => '118824650',
            'video_image_url'    =>  YWRR_ASSETS_URL . '/images/yith-woocommerce-review-reminder.jpg',
            'video_description'  => __( 'YITH WooCommerce Review Reminder', 'ywrr' ),
        ),
        'title_second_column'       => __( 'Get Support and Pro Features', 'ywrr' ),
        'description_second_column' => __('By purchasing the premium version of the plugin, you will take advantage of the advanced features of the product and you will get one year of free updates and support through our platform available 24h/24.', 'ywrr'),
        'button'                    => array(
            'href'  => $YWRR_Review_Reminder->get_premium_landing_uri(),
            'title' => 'Get Support and Pro Features'
        )
    ),
    'id'        => 'ywrr_general_videobox'
);

return array(
    'mail' => array(
        'section_general_settings_videobox'             => $videobox,
        'review_reminder_mail_section_title'            => array(
            'name'              => __( 'Mail Settings', 'ywrr' ),
            'type'              => 'title',
            'desc'              => '',
            'id'                => 'ywrr_mail_settings_title',
        ),
        'review_reminder_mail_type'                     => array(
            'name'              => __( 'Email type', 'ywrr' ),
            'type'              => 'select',
            'desc'              => __( 'Choose which format of email to send.', 'ywrr' ),
            'options'           => array(
                'html'  => __('HTML', 'ywrr'),
                'plain' => __('Plain text', 'ywrr')
            ),
            'default'           => 'html',
            'id'                => 'ywrr_mail_type'
        ),
        'review_reminder_mail_subject'                  => array(
            'name'              => __( 'Email subject', 'ywrr' ),
            'type'              => 'text',
            'desc'              => '',
            'id'                => 'ywrr_mail_subject',
            'default'           => __( '[{site_title}] Review recently purchased products', 'ywrr' ),
            'css'               => 'width: 400px;',
            'custom_attributes' => array(
                'required' => 'required'
            )
        ),
        'review_reminder_mail_body'                     => array(
            'name'              => __( 'Email content', 'ywrr' ),
            'type'              => 'customtext',
            'desc'              => __('<b>{customer_name}</b> Replaced with the customer\'s name<br /><br />
                        <b>{customer_email}</b> Replaced with the customer\'s email<br /><br />
                        <b>{site_title}</b> Replaced with the site title<br /><br />
                        <b>{order_id}</b> Replaced with the order ID<br /><br />
                        <b>{order_date}</b> Replaced with the date and time of the order<br /><br />
                        <b>{order_date_completed}</b> Replaced with the date the order was marked completed<br /><br />
                        <b>{order_list}</b> Replaced with a list of products purchased but not reviewed (Do not forget it!!!)<br /><br />
                        <b>{days_ago}</b> Replaced with the days ago the order was made<br /><br />', 'ywrr' ),
            'id'                => 'ywrr_mail_body',
            'default'           => __( 'Hello {customer_name},
Thank you for purchasing items from the {site_title} shop!
We would love if you could help us and other customers by reviewing the products you recently purchased.
It only takes a minute and it would really help others by giving them an idea of your experience.
Click the link below for each product and review the product under the \'Reviews\' tab.

{order_list}

Much appreciated,

{site_title}.', 'ywrr' ),
            'css'               => 'width: 100%; height:300px; resize: none;',
            'custom_attributes' => array(
                'required' => 'required'
            )
        ),
        'review_reminder_mail_template'                 => $custom_template,
        'review_reminder_mail_unsubscribe_text'         => array(
            'name'              => __( 'Review unsubscription text', 'ywrr' ),
            'type'              => 'text',
            'desc'              => '',
            'id'                => 'ywrr_mail_unsubscribe_text',
            'default'           => __( 'Unsubscribe from review emails', 'ywrr' ),
            'css'               => 'width: 400px;',
            'custom_attributes' => array(
                'required' => 'required'
            )
        ),
        'review_reminder_mail_section_end'              => array(
            'type'              => 'sectionend',
            'id'                => 'ywrr_mail_settings_end'
        )
    )

);