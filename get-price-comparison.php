<?php
/**
 * Plugin Name: Get Price Comparison
 * Version: 1.4.3
 * Plugin URI: http://www.getpricecomparison.com
 * Description: Effortless updating. Always show the latest price to your readers.
 * Author: GetPriceComparison.com
 * Author URI: https://www.getpricecomparison.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: get-price-comparison
 * Domain Path: /lang/
 *
 * @package get-price-comparison
 * @author GetPriceComparison.com
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Load plugin class files.
require_once( 'includes/class-get-price-comparison.php' );
require_once( 'includes/class-get-price-comparison-products.php' );
require_once( 'includes/class-get-price-comparison-settings.php' );
//
// Load plugin libraries.
require_once( 'includes/lib/class-get-price-comparison-admin-api.php' );
require_once( 'includes/lib/class-get-price-comparison-product-list-table.php' );
require_once( 'includes/lib/class-get-price-comparison-product.php' );
require_once( 'includes/lib/class-get-price-comparison-product-form-processor.php' );
// @codingStandardsIgnoreStart
// require_once('includes/lib/class-get-price-comparison-tinymce-widget.php');
// @codingStandardsIgnoreEnd
require_once( 'includes/lib/get-price-comparison-outbound.php' );
require_once( 'includes/lib/get-price-comparison-path-checker.php' );

define( 'GPC_PLUGIN_VERSION','1.4.3' );
define( 'GPC_PLUGIN_NAME','get-price-comparison' );
define( 'GPC_PATH',WP_PLUGIN_DIR . '/' . GPC_PLUGIN_NAME );
define( 'GPC_VIEWS_PATH',GPC_PATH . '/includes/views' );
define( 'GPC_PRODUCTS_TABLE','get_price_comparison' );

/**
 * Returns the main instance of get_price_comparison to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object get_price_comparison.
 */
function get_price_comparison() {
	$instance = get_price_comparison::instance( __FILE__, GPC_PLUGIN_VERSION );

	if ( null === $instance->settings ) {
		$instance->settings = get_price_comparison_Settings::instance( $instance );
	}

	return $instance;
}

get_price_comparison();
