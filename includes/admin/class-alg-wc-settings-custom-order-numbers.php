<?php
/**
 * Custom Order Numbers for WooCommerce - Settings
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Settings_Custom_Order_Numbers' ) ) :

class Alg_WC_Settings_Custom_Order_Numbers extends WC_Settings_Page {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id    = 'alg_wc_custom_order_numbers';
		$this->label = __( 'Custom Order Numbers', 'custom-order-numbers-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_settings() {
		global $current_section;
		return apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() );
	}

}

endif;

return new Alg_WC_Settings_Custom_Order_Numbers();
