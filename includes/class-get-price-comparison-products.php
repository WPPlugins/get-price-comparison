<?php
/**
 * Product Pages Setup
 *
 * @package   get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Get_Price_Comparison_Products
 */
class Get_Price_Comparison_Products {

	/**
	 * The single instance of Get_Price_Comparison_Settings.
	 *
	 * @var    object
	 * @access   private
	 * @since    1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 *
	 * @var    object
	 * @access   public
	 * @since    1.0.0
	 */
	public $parent = null;

	/**
	 * Get_Price_Comparison_Products constructor.
	 *
	 * @param object $parent The parent class.
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;
	}

	/**
	 * Display list view
	 */
	public function display_list_view() {
		$products = new Get_Price_Comparison_Product();

		$product_data = $products->get_all_products();

		$product_list_table = new Get_Price_Comparison_Product_List_Table( $product_data );

		// Fetch, prepare, sort, and filter our data.
		$product_list_table->prepare_items();

		// Include the view markup.
		include plugin_dir_path(__FILE__) . 'views/table.php';
	}

	/**
	 * Display create view
	 */
	public function display_create_view() {
        include plugin_dir_path(__FILE__) . 'views/license-key.php';

		$product = new Get_Price_Comparison_Product();

		$product_data = $product->init_new_product();

        include plugin_dir_path(__FILE__) . 'views/create.php';
	}

	/**
	 * Display update view
	 */
	public function display_update_view() {
		include __DIR__ . '/views/license-key.php';

		if ( ! isset( $_GET['id'] ) ) { // WPCS: input var okay.
			return false;
		}

		$id = (int) $_GET['id']; // WPCS: input var okay.

		$product = new Get_Price_Comparison_Product();

		$product_data = $product->get_one_product( $id );

        include plugin_dir_path(__FILE__) . 'views/update.php';
	}

	/**
	 * Display delete view
	 */
	public function display_delete_view() {

		if ( ! isset( $_GET['id'] ) ) { // WPCS: input var okay.
			return false;
		}

		$id = (int) $_GET['id']; // WPCS: input var okay.

		$product = new Get_Price_Comparison_Product();

		$product_data = $product->delete( $id );

        include plugin_dir_path(__FILE__) . 'views/delete.php';
	}

	/**
	 * Main Get_Price_Comparison_Settings Instance
	 *
	 * Ensures only one instance of Get_Price_Comparison_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see   Get_Price_Comparison()
	 *
	 * @param object $parent The parent class.
	 *
	 * @return Get_Price_Comparison_Products instance
	 */
	public static function instance( $parent ) {
		if ( null === self::$_instance ) {
			self::$_instance = new self($parent);
		}

		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version ); // WPCS: XSS ok.
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version ); // WPCS: XSS ok.
	} // End __wakeup()

}
