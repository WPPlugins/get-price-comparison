<?php
/**
 * GPC Tiny MCE Widget Setup
 *
 * @package   get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Get_Price_Comparison_TinyMCE_Widget
 */
class Get_Price_Comparison_TinyMCE_Widget {

	/**
	 * Get_Price_Comparison_TinyMCE_Widget constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup_tinymce_plugin' ) );
		add_action( 'after_wp_tiny_mce', array( $this, 'render' ), 50 );
	}

	/**
	 * Check if the current user can edit Posts or Pages, and is using the Visual Editor
	 * If so, add some filters so we can register our plugin
	 */
	public function setup_tinymce_plugin() {

		// Check if the logged in WordPress User can edit Posts or Pages.
		// If not, don't register our TinyMCE plugin.
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// Check if the logged in WordPress User has the Visual Editor enabled.
		// If not, don't register our TinyMCE plugin.
		if ( get_user_option( 'rich_editing' ) !== 'true' ) {
			return;
		}

		// Setup some filters.
		add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );
		add_filter( 'mce_buttons', array( $this, 'add_tinymce_toolbar_button' ) );
	}


	/**
	 * Adds a TinyMCE plugin compatible JS file to the TinyMCE / Visual Editor instance.
	 *
	 * @param array $plugin_array Array of registered TinyMCE Plugins.
	 *
	 * @return array Modified array of registered TinyMCE Plugins.
	 */
	public function add_tinymce_plugin( $plugin_array ) {
		$plugin_array['gpc_tinymce_widget'] = plugin_dir_url( __DIR__ ) . '../js/gpc-tinymce-plugin.js';

		return $plugin_array;
	}


	/**
	 * Adds a button to the TinyMCE / Visual Editor which the user can click
	 * to insert a link with a custom CSS class.
	 *
	 * @param array $buttons Array of registered TinyMCE Buttons.
	 *
	 * @return array Modified array of registered TinyMCE Buttons.
	 */
	public function add_tinymce_toolbar_button( $buttons ) {
		array_push( $buttons, '|', 'gpc_tinymce_widget' );

		return $buttons;
	}

	/**
	 * Render out to screen
	 *
	 * @return null
	 */
	public function render() {
		if ( ! is_admin() ) {
			return null;
		}

		$product_api = new Get_Price_Comparison_Product();
		$products = $product_api->as_json();

		echo '<div id="gpc-products-list-as-json" style="display: none;">' . esc_html( $products ) . '</div>';

		// a simple empty container to render the GPC tinymce popup modal content into.
		echo '<div id="gpc-insert-product-dialog-content" style="display: none;"></div>';
	}
}

$tinymce_custom_link_class = new Get_Price_Comparison_TinyMCE_Widget;
