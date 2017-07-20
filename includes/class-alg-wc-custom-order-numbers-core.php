<?php
/**
 * Custom Order Numbers for WooCommerce - Core Class
 *
 * @version 1.0.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Custom_Order_Numbers_Core' ) ) :

class Alg_WC_Custom_Order_Numbers_Core {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_custom_order_numbers_enabled', 'yes' ) ) {
			add_action( 'wp_insert_post',           array( $this, 'add_new_order_number' ), PHP_INT_MAX ); // 'woocommerce_new_order'
			add_filter( 'woocommerce_order_number', array( $this, 'display_order_number' ), PHP_INT_MAX, 2 );
			if ( 'yes' === get_option( 'alg_wc_custom_order_numbers_order_tracking_enabled', 'yes' ) ) {
				add_filter( 'woocommerce_shortcode_order_tracking_order_id', array( $this, 'add_order_number_to_tracking' ), PHP_INT_MAX );
			}
			if ( 'yes' === get_option( 'alg_wc_custom_order_numbers_search_by_custom_number_enabled', 'yes' ) ) {
				add_action( 'pre_get_posts', array( $this, 'search_by_custom_number' ) );
			}
			add_action( 'admin_menu', array( $this, 'add_renumerate_orders_tool' ), PHP_INT_MAX );
		}
	}

	/**
	 * Add menu item.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_renumerate_orders_tool() {
		add_submenu_page(
			'woocommerce',
			__( 'Renumerate Orders', 'custom-order-numbers-for-woocommerce' ),
			__( 'Renumerate Orders', 'custom-order-numbers-for-woocommerce' ),
			'manage_woocommerce',
			'alg-wc-renumerate-orders-tools',
			array( $this, 'create_renumerate_orders_tool' )
		);
	}

	/**
	 * Add Renumerate Orders tool to WooCommerce menu (the content).
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @todo    (maybe) more results
	 * @todo    (maybe) check if sequential is enabled
	 */
	public function create_renumerate_orders_tool() {
		$html = '';
		$result_message = '';
		$last_renumerated_order = 0;
		if ( isset( $_POST['alg_renumerate_orders'] ) ) {
			$total_renumerated_orders = $this->renumerate_orders();
			$last_renumerated_order   = $total_renumerated_orders[1];
			$total_renumerated_orders = $total_renumerated_orders[0];
			$result_message = '<p><div class="updated"><p><strong>' . sprintf( __( '%d orders successfully renumerated!', 'custom-order-numbers-for-woocommerce' ), $total_renumerated_orders ) . '</strong></p></div></p>';
		}
		$html .= '<h1>' . __( 'Renumerate Orders', 'custom-order-numbers-for-woocommerce' ) . '</h1>';
		$html .= $result_message;
		$next_order_number = ( 0 != $last_renumerated_order ) ? ( $last_renumerated_order + 1 ) : get_option( 'alg_wc_custom_order_numbers_counter', 1 );
		$html .= '<p>' . sprintf( __( 'Press the button below to renumerate all existing orders. First order number will be <strong>%d</strong> (as set in <a href="%s">WooCommerce > Settings > Custom Order Numbers</a>).', 'custom-order-numbers-for-woocommerce' ), $next_order_number, admin_url( 'admin.php?page=wc-settings&tab=alg_wc_custom_order_numbers' ) ) . '</p>';
		$html .= '<form method="post" action="">';
		$html .= '<input class="button-primary" type="submit" name="alg_renumerate_orders" value="' . __( 'Renumerate orders', 'custom-order-numbers-for-woocommerce' ) . '">';
		$html .= '</form>';
		echo $html;
	}

	/**
	 * Renumerate orders function.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function renumerate_orders() {
		$total_renumerated = 0;
		$last_renumerated = 0;
		$offset = 0;
		$block_size = 512;
		while( true ) {
			$args = array(
				'post_type'      => 'shop_order',
				'post_status'    => 'any',
				'posts_per_page' => $block_size,
				'orderby'        => 'date',
				'order'          => 'ASC',
				'offset'         => $offset,
				'fields'         => 'ids',
			);
			$loop = new WP_Query( $args );
			if ( ! $loop->have_posts() ) {
				break;
			}
			foreach ( $loop->posts as $order_id ) {
				$last_renumerated = $this->add_order_number_meta( $order_id, true );
				$total_renumerated++;
			}
			$offset += $block_size;
		}
		return array( $total_renumerated, $last_renumerated );
	}

	/**
	 * search_by_custom_number.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @see     https://github.com/pablo-pacheco/wc-booster-search-order-by-custom-number-fix
	 */
	function search_by_custom_number( $query ) {
		if (
			! is_admin() ||
			! isset( $query->query ) ||
			! isset( $query->query['s'] ) ||
			false === is_numeric( $query->query['s'] ) ||
			0 == $query->query['s'] ||
			'shop_order' !== $query->query['post_type'] ||
			! $query->query_vars['shop_order_search']
		) {
			return;
		}
		$custom_order_id = $query->query['s'];
		$query->query_vars['post__in'] = array();
		$query->query['s'] = '';
		$query->set( 'meta_key', '_alg_wc_custom_order_number' );
		$query->set( 'meta_value', $custom_order_id );
	}

	/**
	 * add_order_number_to_tracking.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function add_order_number_to_tracking( $order_number ) {
		$offset = 0;
		$block_size = 512;
		while( true ) {
			$args = array(
				'post_type'      => 'shop_order',
				'post_status'    => 'any',
				'posts_per_page' => $block_size,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'offset'         => $offset,
				'fields'         => 'ids',
			);
			$loop = new WP_Query( $args );
			if ( ! $loop->have_posts() ) {
				break;
			}
			foreach ( $loop->posts as $order_id ) {
				$_order = wc_get_order( $order_id );
				$_order_number = $this->display_order_number( $order_id, $_order );
				if ( $_order_number === $order_number ) {
					return $order_id;
				}
			}
			$offset += $block_size;
		}
		return $order_number;
	}

	/**
	 * Display order number.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function display_order_number( $order_number, $order ) {
		$order_number_meta = get_post_meta( $order->id, '_alg_wc_custom_order_number', true );
		if ( '' == $order_number_meta || 'order_id' === get_option( 'alg_wc_custom_order_numbers_counter_type', 'sequential' ) ) {
			$order_number_meta = $order->id;
		}
		$order_timestamp = strtotime( $order->order_date );
		$order_number = apply_filters( 'alg_wc_custom_order_numbers', sprintf( '%s%d', do_shortcode( get_option( 'alg_wc_custom_order_numbers_prefix', '' ) ), $order_number_meta ), 'value', array( 'order_timestamp' => $order_timestamp, 'order_number_meta' => $order_number_meta ) );
		return $order_number;
	}

	/**
	 * add_new_order_number.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public function add_new_order_number( $order_id ) {
		$this->add_order_number_meta( $order_id, false );
	}

	/**
	 * Add/update order_number meta to order.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @todo    (maybe) check if sequential is enabled
	 */
	public function add_order_number_meta( $order_id, $do_overwrite ) {
		if ( 'shop_order' !== get_post_type( $order_id ) ) {
			return false;
		}
		if ( true === $do_overwrite || 0 == get_post_meta( $order_id, '_alg_wc_custom_order_number', true ) ) {
			if ( 'yes' === get_option( 'alg_wc_custom_order_numbers_use_mysql_transaction_enabled', 'yes' ) ) {
				global $wpdb;
				$wpdb->query( 'START TRANSACTION' );
				$wp_options_table = $wpdb->prefix . 'options';
				$result_select = $wpdb->get_row( "SELECT * FROM $wp_options_table WHERE option_name = 'alg_wc_custom_order_numbers_counter'" );
				if ( NULL != $result_select ) {
					$current_order_number = $result_select->option_value;
					$result_update = $wpdb->update(
						$wp_options_table,
						array( 'option_value' => ( $current_order_number + 1 ) ),
						array( 'option_name'  => 'alg_wc_custom_order_numbers_counter' )
					);
					if ( NULL != $result_update ) {
						$wpdb->query( 'COMMIT' ); // all ok
						update_post_meta( $order_id, '_alg_wc_custom_order_number', $current_order_number );
					} else {
						$wpdb->query( 'ROLLBACK' ); // something went wrong, Rollback
						return false;
					}
				} else {
					$wpdb->query( 'ROLLBACK' ); // something went wrong, Rollback
					return false;
				}
			} else {
				$current_order_number = get_option( 'alg_wc_custom_order_numbers_counter', 1 );
				update_option( 'alg_wc_custom_order_numbers_counter', ( $current_order_number + 1 ) );
				update_post_meta( $order_id, '_alg_wc_custom_order_number', $current_order_number );
			}
			return $current_order_number;
		}
		return false;
	}

}

endif;

return new Alg_WC_Custom_Order_Numbers_Core();
