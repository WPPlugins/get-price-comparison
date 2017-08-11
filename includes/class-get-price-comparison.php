<?php
/**
 * Plugin Setup
 *
 * @package   get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Get_Price_Comparison
 */
class Get_Price_Comparison {

	/**
	 * The single instance of Get_Price_Comparison.
	 *
	 * @var    object
	 * @access   private
	 * @since    1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Form Processor Instance
	 *
	 * @var     Get_Price_Comparison_Product_Form_Processor
	 * @access  public
	 * @since   1.0.0
	 */
	public $processor;

	/**
	 * Constructor function.
	 *git
	 * @access  public
	 * @since   1.0.0
	 *
	 * @param string $file A configurable location of this file.
	 * @param string $version The current version.
	 */
	public function __construct( $file = '', $version = GPC_PLUGIN_VERSION ) {
		$this->_version = $version;
		$this->_token = 'get_price_comparison';

		// Load plugin environment variables.
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir );
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 );

		// Load admin JS & CSS.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1 ); // front end display, for backend.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 20, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 5, 1 );

		// Load API for generic admin functions.
		if ( is_admin() ) {
			$this->admin = new Get_Price_Comparison_Admin_API();
		}

		// Handle localisation.
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		add_action( 'init', array( $this, 'load_form_processor' ), 0 );
	} // End __construct ()


	/**
	 * Load frontend CSS.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles() {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts() {
		wp_register_script( $this->_token . '-price-display-helpers', esc_url( $this->assets_url ) . 'js/price-display-helpers.js', array(), $this->_version );
		wp_enqueue_script( $this->_token . '-price-display-helpers' );

		wp_register_script( $this->_token . '-main-gpc', esc_url( $this->assets_url ) . 'js/main.gpc.js', array(), $this->_version, true );
		wp_enqueue_script( $this->_token . '-main-gpc' );

	} // End enqueue_scripts ().

	/**
	 * Load admin CSS.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles() {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version, false );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ().

	/**
	 * Load admin Javascript.
	 *
	 * @access  public
	 * @since   1.0.0
	 *
	 * @param string $hook A configurable hook.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook = '' ) {
		wp_register_script( $this->_token . '-configuration', esc_url( $this->assets_url ) . 'js/configuration.js', array(), $this->_version );
		wp_register_script( $this->_token . '-clipboard', esc_url( $this->assets_url ) . 'js/third-party/clipboard.min.js', array(), $this->_version );
		wp_register_script( $this->_token . '-env-config', esc_url( $this->assets_url ) . 'js/env.config.js', array(), $this->_version );
		wp_register_script( $this->_token . '-lodash', esc_url( $this->assets_url ) . 'js/third-party/lodash.js', array(), $this->_version );
		wp_register_script( $this->_token . '-pubsub-js', esc_url( $this->assets_url ) . 'js/third-party/pubsub.js', array(), $this->_version );
		wp_register_script( $this->_token . '-jquery-validation', esc_url( $this->assets_url ) . 'js/third-party/jquery.validate.min.js', array('jquery'), $this->_version );
		wp_register_script( $this->_token . '-utility', esc_url( $this->assets_url ) . 'js/utility.js', array(), $this->_version );
		wp_register_script( $this->_token . '-vendor-amazon', esc_url( $this->assets_url ) . 'js/vendor/amazon.js', array(), $this->_version );
		wp_register_script( $this->_token . '-vendor-webgains', esc_url( $this->assets_url ) . 'js/vendor/webgains.js', array(), $this->_version );
		wp_register_script($this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin.js', array(
			'jquery',
			$this->_token . '-configuration',
			$this->_token . '-env-config',
			$this->_token . '-clipboard',
			$this->_token . '-lodash',
			$this->_token . '-pubsub-js',
			$this->_token . '-jquery-validation',
			$this->_token . '-vendor-amazon',
			$this->_token . '-vendor-webgains',
			$this->_token . '-utility',
		), $this->_version);

        wp_enqueue_script( $this->_token . '-admin' );

        wp_localize_script('jquery', 'WPURLS', array(
            'siteurl' => get_option('siteurl'),
        ));
	} // End admin_enqueue_scripts ().

	/**
	 * Load plugin localisation
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation() {
		load_plugin_textdomain( 'get-price-comparison', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ().

	/**
	 * Load the form processor instance
	 *
	 * @return void
	 */
	public function load_form_processor() {
		$this->processor = new Get_Price_Comparison_Product_Form_Processor();
	}

	/**
	 * Load plugin textdomain
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain() {
		$domain = 'get-price-comparison';

		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ().

	/**
	 * Main Get_Price_Comparison Instance
	 *
	 * Ensures only one instance of Get_Price_Comparison is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see   Get_Price_Comparison()
	 *
	 * @param string $file A configurable file path.
	 * @param string $version The plugin version.
	 *
	 * @return Get_Price_Comparison instance
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

	/**
	 * Installation. Runs on activation.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install() {
		$this->_log_version_number();

		$upgrade_path = ABSPATH . 'wp-admin/includes/upgrade.php';
		require_once( $upgrade_path );

		global $wpdb;

		$table_name = $wpdb->prefix . 'get_price_comparison';

			$sql = '
            CREATE TABLE ' . $table_name . ' (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `merchant_id` varchar(255) NOT NULL,
              `product_id` varchar(255) NOT NULL,
              `affiliate_account_id` varchar(100) NOT NULL,
              `path` varchar(255) NOT NULL,
              `link_text` varchar(255) NOT NULL,
              `affiliate_url` text NOT NULL,
              `redirect_code` int(3) NOT NULL DEFAULT \'301\',
              `merchant_name` varchar(255) NOT NULL,
              `affiliate_program_name` varchar(255) NOT NULL,
              `affiliate_code` varchar(255) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `idx_path` (`path`)
            ) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8
        ';

		dbDelta( $sql );
	} // End install ().

	/**
	 * Log the plugin version number.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number() {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ().

}
