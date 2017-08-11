<?php
/**
 * Form submission processor
 *
 * @package get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Get_Price_Comparison_Product_Form_Processor
 */
class Get_Price_Comparison_Product_Form_Processor {

	/**
	 * The single instance of Get_Price_Comparison_Product_Form_Processor.
	 *
	 * @var    object
	 * @access   private
	 * @since    1.0.0
	 */
	private static $_instance = null;

	/**
	 * The version number.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * Get_Price_Comparison_Product_Form_Processor constructor.
	 *
	 * @param string $file A configurable location of this file.
	 * @param string $version The current version.
	 */
	public function __construct( $file = '', $version = GPC_PLUGIN_VERSION ) {

		$this->_version = $version;
		// Load plugin environment variables.
		$this->file = $file;

		add_action( 'admin_post_add_gpc_link', array( $this, 'admin_post_add_gpc_link' ), 10, 1 );
		add_action( 'admin_post_update_gpc_link', array( $this, 'admin_post_update_gpc_link' ), 10, 1 );
	}

	/**
	 * Processes adding a new Link.
	 *
	 * @return bool
	 */
	public function admin_post_add_gpc_link() {

		if ( empty( $_POST ) ) { // WPCS: input var okay.
			return false;
		}

		check_admin_referer( 'admin_post_add_gpc_link' );

		$post_data = $_POST; // WPCS: input var okay. WPCS: sanitization ok.

		$products = new Get_Price_Comparison_Product();

		$record = $products->create( $post_data );

		wp_safe_redirect( esc_url( wp_nonce_url( admin_url( 'admin.php?page=get_price_comparison-products' ) ) ) );
		exit();
	}

	/**
	 * Processes updating an existing Link.
	 *
	 * @return bool
	 */
	public function admin_post_update_gpc_link() {

		if ( empty( $_POST ) ) { // WPCS: input var okay.
			return false;
		}

		check_admin_referer( 'admin_post_update_gpc_link' );

		$post_data = $_POST; // WPCS: input var okay. WPCS: sanitization ok.

		$products = new Get_Price_Comparison_Product();

		$record = $products->update( (int) $post_data['id'], $post_data );

		wp_safe_redirect( esc_url( wp_nonce_url( admin_url( 'admin.php?page=get_price_comparison-products' ) ) ) );
		exit();
	}

	/**
	 * Main Get_Price_Comparison_Product_Form_Processor Instance
	 *
	 * Ensures only one instance of Get_Price_Comparison_Product_Form_Processor is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see   Get_Price_Comparison_Product_Form_Processor()
	 *
	 * @param string $file A configurable file path.
	 * @param string $version The plugin version.
	 *
	 * @return Get_Price_Comparison_Product_Form_Processor instance
	 */
	public static function instance( $file = '', $version = GPC_PLUGIN_VERSION ) {
		if ( null === self::$_instance ) {
			self::$_instance = new self($file, $version);
		}

		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version ); // WPCS: XSS ok.
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version ); // WPCS: XSS ok.
	} // End __wakeup ()
}
