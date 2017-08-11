<?php
/**
 * Plugin Settings
 *
 * @package   get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Get_Price_Comparison_Settings
 */
class Get_Price_Comparison_Settings {

	/**
	 * The single instance of Get_Price_Comparison_Settings.
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
	 * The token.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin object.
	 *
	 * @var    object
	 * @access   public
	 * @since    1.0.0
	 */
	public $parent = null;

	/**
	 * The plugin assets URL.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Prefix for plugin settings.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 *
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	/**
	 * Get_Price_Comparison_Settings constructor.
	 *
	 * @param object $parent The parent class.
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;

		$this->base = 'gpc_';

		$this->_token = $parent->_token;
		$this->_version = $parent->_version;
		$this->assets_url = $parent->assets_url;

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );

		// Initialise settings.
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings.
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Add settings page to menu.
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page.
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ), array( $this, 'add_settings_link' ) );
	}

	/**
	 * Displays the product management page.
	 */
	public function display_product_management_page() {
		$page = new Get_Price_Comparison_Products( $this->parent );

		$page->display_list_view();
	}

	/**
	 * Displays the add product page.
	 */
	public function display_add_product_page() {
		$page = new Get_Price_Comparison_Products( $this->parent );

		$page->display_create_view();
	}

	/**
	 * Displays the update product page.
	 */
	public function display_update_product_page() {
		$page = new Get_Price_Comparison_Products( $this->parent );

		$page->display_update_view();
	}

	/**
	 * Displays the delete product page.
	 */
	public function display_delete_product_page() {
		$page = new Get_Price_Comparison_Products( $this->parent );

		$page->display_delete_view();
	}

	/**
	 * Load settings JS & CSS
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		wp_register_script( $this->_token . '-configuration', esc_url( $this->assets_url ) . 'js/configuration.js', array(), $this->_version );
		wp_register_script( $this->_token . '-env-config', esc_url( $this->assets_url ) . 'js/env.config.js', array(), $this->_version );
		wp_register_script( $this->_token . '-license-key', esc_url( $this->assets_url ) . 'js/license-key.js', array(
		    $this->_token . '-configuration',
		    $this->_token . '-env-config',
        ), $this->_version );

		wp_enqueue_script( $this->_token . '-license-key' );
	}

	/**
	 * Initialise settings
	 *
	 * @return void
	 */
	public function init_settings() {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 *
	 * @return void
	 */
	public function add_menu_item() {
		$page = add_menu_page(
			__( 'Get Price Comparison', 'get-price-comparison' ), // page title.
			__( 'Get Price Comparison', 'get-price-comparison' ), // menu title.
			'manage_options',
			$this->parent->_token . '_settings',
			array( $this, 'settings_page' ) // callback function.
		);

        add_submenu_page(
            $this->parent->_token . '_settings',
            __( 'License Key', 'get-price-comparison' ), // page title.
            __( 'License Key', 'get-price-comparison' ), // menu title.
            'manage_options',
            $this->parent->_token . '_settings',
            array( $this, 'settings_page' ) // callback function.
        );

		add_submenu_page(
			$this->parent->_token . '_settings',
			__( 'Products', 'get-price-comparison' ), // page title.
			__( 'Products', 'get-price-comparison' ), // menu title.
			'manage_options',
			$this->parent->_token . '-products',
			array( $this, 'display_product_management_page' )
		);

		add_submenu_page(
			$this->parent->_token . '_settings',
			__( 'Add New Product', 'get-price-comparison' ), // page title.
			__( 'Add New Product', 'get-price-comparison' ), // menu title.
			'manage_options',
			$this->parent->_token . '-products-add',
			array( $this, 'display_add_product_page' )
		);

		add_submenu_page(
			null,
			__( 'Update Product', 'get-price-comparison' ), // page title.
			__( 'Update Product', 'get-price-comparison' ), // menu title.
			'manage_options',
			$this->parent->_token . '-products-update',
			array( $this, 'display_update_product_page' )
		);

		add_submenu_page(
			null,
			__( 'Delete Product', 'get-price-comparison' ), // page title.
			__( 'Delete Product', 'get-price-comparison' ), // menu title.
			'manage_options',
			$this->parent->_token . '-products-delete',
			array( $this, 'display_delete_product_page' )
		);
	}

	/**
	 * Add settings link to plugin list table
	 *
	 * @param  array $links Existing links.
	 *
	 * @return array        Modified links
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'get-price-comparison' ) . '</a>';
		array_push( $links, $settings_link );

		return $links;
	}

	/**
	 * Build settings fields
	 *
	 * @return array Fields to be displayed on settings page.
	 */
	private function settings_fields() {
		$settings['standard'] = array(
			'title'       => __( 'License Key', 'get-price-comparison' ),
			'description' => __( 'Please enter the license key you received when joining GetPriceComparison.com. This information was also sent to you in your Welcome email, and can also be found by <a href="https://app.getpricecomparison.com/license-key" target="_blank">clicking here</a>, or logging in to GetPriceComparison.com, and clicking \'License Key\' from the User Profile menu.', 'get-price-comparison' ),
			'fields'      => array(
				array(
					'id'          => 'license_key',
					'label'       => __( 'License Key', 'get-price-comparison' ),
					'type'        => 'text',
					'default'     => '',
					'placeholder' => __( 'E.g. 32e3827c-dd73-11e6-8e9b-010027874055', 'get-price-comparison' ),
					'onchange'    => 'validateLicenseKey()',
					'size'        => 50,
				),
			),
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 *
	 * @return void
	 */
	public function register_settings() {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab.
			$current_section = '';

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section !== $section ) {
					continue;
				}

				// Add section to page.
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field.
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field.
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page.
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) {
					break;
				}
			}
		}
	}

	/**
	 * Output for settings section
	 *
	 * @param array $section The settings for this section.
	 */
	public function settings_section( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html; // WPCS: XSS ok.
	}



	/**
	 * Load settings page content
	 *
	 * @return void
	 */
	public function settings_page() {

		// Build page HTML.
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
		$html .= '<h2>' . __( 'Get Price Comparison', 'get-price-comparison' ) . '</h2>' . "\n";

		$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

		// Get settings fields.
		ob_start();
		settings_fields( $this->parent->_token . '_settings' );
		do_settings_sections( $this->parent->_token . '_settings' );
		$html .= ob_get_clean();

		$html .= '<p class="submit">' . "\n";
		$html .= '<input name="Submit" type="submit" class="button-primary" id="save-license-key-settings" value="' . esc_attr( __( 'Save Settings', 'get-price-comparison' ) ) . '" />' . "\n";
		$html .= '</p>' . "\n";
		$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		$html .= '<script type="text/javascript">
            jQuery( document ).ready( function ( e ) {
                validateLicenseKey(
                    jQuery(\'#license_key\').val()
                );
            });
        </script>' . "\n";

		echo $html; // WPCS: XSS ok.
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
	 * @return Get_Price_Comparison_Settings instance
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
