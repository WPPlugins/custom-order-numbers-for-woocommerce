<?php
/**
 * Custom Order Numbers for WooCommerce - General Section Settings
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Custom_Order_Numbers_Settings_General' ) ) :

class Alg_WC_Custom_Order_Numbers_Settings_General extends Alg_WC_Custom_Order_Numbers_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id   = '';
		$this->desc = __( 'General', 'custom-order-numbers-for-woocommerce' );
		parent::__construct();
		add_action( 'admin_head', array( $this, 'add_tool_button_class_style' ) );
	}

	/**
	 * add_tool_button_class_style.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_tool_button_class_style() {
		echo '<style type="text/css">';
		echo '#alg-tool-button { ';
		echo 'background: #ba0000; border-color: #aa0000; text-shadow: 0 -1px 1px #990000,1px 0 1px #990000,0 1px 1px #990000,-1px 0 1px #990000; box-shadow: 0 1px 0 #990000;';
		echo ' }';
		echo '</style>';
	}

	/**
	 * add_settings.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @todo    (maybe) remove option to disable "Use MySQL Transaction"
	 */
	function add_settings( $settings ) {
		$settings = array_merge(
			array(
				array(
					'title'     => __( 'Custom Order Numbers Options', 'custom-order-numbers-for-woocommerce' ),
					'type'      => 'title',
					'desc'      => __( 'Enable sequential order numbering, set custom number prefix, suffix and number width.', 'custom-order-numbers-for-woocommerce' ),
					'id'        => 'alg_wc_custom_order_numbers_options',
				),
				array(
					'title'     => __( 'WooCommerce Custom Order Numbers', 'custom-order-numbers-for-woocommerce' ),
					'desc'      => '<strong>' . __( 'Enable', 'custom-order-numbers-for-woocommerce' ) . '</strong>',
					'desc_tip'  => __( 'Custom Order Numbers for WooCommerce.', 'custom-order-numbers-for-woocommerce' ),
					'id'        => 'alg_wc_custom_order_numbers_enabled',
					'default'   => 'yes',
					'type'      => 'checkbox',
				),
				array(
					'title'     => __( 'Order Numbers Counter', 'custom-order-numbers-for-woocommerce' ),
					'id'        => 'alg_wc_custom_order_numbers_counter_type',
					'default'   => 'sequential',
					'type'      => 'select',
					'options'   => array(
						'sequential' => __( 'Sequential', 'custom-order-numbers-for-woocommerce' ),
						'order_id'   => __( 'Order ID', 'custom-order-numbers-for-woocommerce' ),
					),
				),
				array(
					'title'     => __( 'Next Order Number', 'custom-order-numbers-for-woocommerce' ),
					'desc'      => __( 'Next new order will be given this number.', 'custom-order-numbers-for-woocommerce' ) . ' ' . sprintf( __( 'Use <a class="button-primary" id="alg-tool-button" href="%s">Renumerate Orders tool</a> for existing orders.', 'custom-order-numbers-for-woocommerce' ), admin_url( 'admin.php?page=alg-wc-renumerate-orders-tools' ) ),
					'desc_tip'  => __( 'This will be ignored if sequential order numbering is disabled.', 'custom-order-numbers-for-woocommerce' ),
					'id'        => 'alg_wc_custom_order_numbers_counter',
					'default'   => 1,
					'type'      => 'number',
				),
				array(
					'title'     => __( 'Order Number Custom Prefix', 'custom-order-numbers-for-woocommerce' ),
					'desc_tip'  => __( 'Prefix before order number (optional). This will change the prefixes for all existing orders.', 'custom-order-numbers-for-woocommerce' ),
					'id'        => 'alg_wc_custom_order_numbers_prefix',
					'default'   => '',
					'type'      => 'text',
					'css'       => 'width:300px;',
				),
				array(
					'title'     => __( 'Order Number Date Prefix', 'custom-order-numbers-for-woocommerce' ),
					'desc'      => apply_filters( 'alg_wc_custom_order_numbers', sprintf( 'You will need <a href="%s" target="_blank">Custom Order Numbers for WooCommerce Pro</a> plugin to set this option.', 'http://coder.fm/item/custom-order-numbers-for-woocommerce/' ), 'settings' ),
					'desc_tip'  => __( 'Date prefix before order number (optional). This will change the prefixes for all existing orders. Value is passed directly to PHP `date` function, so most of PHP date formats can be used. The only exception is using `\` symbol in date format, as this symbol will be excluded from date. Try: Y-m-d- or mdy.', 'custom-order-numbers-for-woocommerce' ),
					'id'        => 'alg_wc_custom_order_numbers_date_prefix',
					'default'   => '',
					'type'      => 'text',
					'custom_attributes' => apply_filters( 'alg_wc_custom_order_numbers', array( 'readonly' => 'readonly' ), 'settings' ),
					'css'       => 'width:300px;',
				),
				array(
					'title'     => __( 'Order Number Width', 'custom-order-numbers-for-woocommerce' ),
					'desc'      => apply_filters( 'alg_wc_custom_order_numbers', sprintf( 'You will need <a href="%s" target="_blank">Custom Order Numbers for WooCommerce Pro</a> plugin to set this option.', 'http://coder.fm/item/custom-order-numbers-for-woocommerce/' ), 'settings' ),
					'desc_tip'  => __( 'Minimum width of number without prefix (zeros will be added to the left side). This will change the minimum width of order number for all existing orders. E.g. set to 5 to have order number displayed as 00001 instead of 1. Leave zero to disable.', 'custom-order-numbers-for-woocommerce' ),
					'id'        => 'alg_wc_custom_order_numbers_min_width',
					'default'   => 0,
					'type'      => 'number',
					'custom_attributes' => apply_filters( 'alg_wc_custom_order_numbers', array( 'readonly' => 'readonly' ), 'settings' ),
					'css'       => 'width:300px;',
				),
				array(
					'title'     => __( 'Order Number Custom Suffix', 'custom-order-numbers-for-woocommerce' ),
					'desc'      => apply_filters( 'alg_wc_custom_order_numbers', sprintf( 'You will need <a href="%s" target="_blank">Custom Order Numbers for WooCommerce Pro</a> plugin to set this option.', 'http://coder.fm/item/custom-order-numbers-for-woocommerce/' ), 'settings' ),
					'desc_tip'  => __( 'Suffix after order number (optional). This will change the suffixes for all existing orders.', 'custom-order-numbers-for-woocommerce' ),
					'id'        => 'alg_wc_custom_order_numbers_suffix',
					'default'   => '',
					'type'      => 'text',
					'custom_attributes' => apply_filters( 'alg_wc_custom_order_numbers', array( 'readonly' => 'readonly' ), 'settings' ),
					'css'       => 'width:300px;',
				),
				array(
					'title'     => __( 'Order Number Date Suffix', 'custom-order-numbers-for-woocommerce' ),
					'desc'      => apply_filters( 'alg_wc_custom_order_numbers', sprintf( 'You will need <a href="%s" target="_blank">Custom Order Numbers for WooCommerce Pro</a> plugin to set this option.', 'http://coder.fm/item/custom-order-numbers-for-woocommerce/' ), 'settings' ),
					'desc_tip'  => __( 'Date suffix after order number (optional). This will change the suffixes for all existing orders. Value is passed directly to PHP `date` function, so most of PHP date formats can be used. The only exception is using `\` symbol in date format, as this symbol will be excluded from date. Try: Y-m-d- or mdy.', 'custom-order-numbers-for-woocommerce' ),
					'id'        => 'alg_wc_custom_order_numbers_date_suffix',
					'default'   => '',
					'type'      => 'text',
					'custom_attributes' => apply_filters( 'alg_wc_custom_order_numbers', array( 'readonly' => 'readonly' ), 'settings' ),
					'css'       => 'width:300px;',
				),
				array(
					'title'     => __( 'Use MySQL Transaction', 'custom-order-numbers-for-woocommerce' ),
					'desc'      => __( 'Enable', 'custom-order-numbers-for-woocommerce' ),
					'desc_tip'  => __( 'This should be enabled if you have a lot of simultaneous orders in your shop - to prevent duplicate order numbers (sequential).', 'custom-order-numbers-for-woocommerce' ),
					'id'        => 'alg_wc_custom_order_numbers_use_mysql_transaction_enabled',
					'default'   => 'yes',
					'type'      => 'checkbox',
				),
				array(
					'title'     => __( 'Enable Order Tracking by Custom Number', 'custom-order-numbers-for-woocommerce' ),
					'desc'      => __( 'Enable', 'custom-order-numbers-for-woocommerce' ),
					'id'        => 'alg_wc_custom_order_numbers_order_tracking_enabled',
					'default'   => 'yes',
					'type'      => 'checkbox',
				),
				array(
					'title'     => __( 'Enable Order Admin Search by Custom Number', 'custom-order-numbers-for-woocommerce' ),
					'desc'      => __( 'Enable', 'custom-order-numbers-for-woocommerce' ),
					'id'        => 'alg_wc_custom_order_numbers_search_by_custom_number_enabled',
					'default'   => 'yes',
					'type'      => 'checkbox',
				),
				array(
					'type'      => 'sectionend',
					'id'        => 'alg_wc_custom_order_numbers_options',
				),
			),
			$settings
		);
		return $settings;
	}

}

endif;

return new Alg_WC_Custom_Order_Numbers_Settings_General();
