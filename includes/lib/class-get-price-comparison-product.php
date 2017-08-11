<?php
/**
 * Methods for DB interaction
 *
 * @package get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Class Get_Price_Comparison_Product
 */
class Get_Price_Comparison_Product {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Get_Price_Comparison_Product constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = "{$wpdb->prefix}get_price_comparison";
	}

	/**
	 * Initialises a new product as array.
	 *
	 * @return array
	 */
	public function init_new_product() {
		return array(
			'merchant_id'            => '',
			'product_id'             => '',
			'product_url'            => '',
			'affiliate_account_id'   => '',
			'path'                   => '',
			'link_text'              => '',
			'affiliate_url'          => '',
			'redirect_code'          => '302',
			'affiliate_code'         => '',
			'affiliate_program_name' => '',
			'merchant_name'          => '',
		);
	}

	/**
	 * Gets a single product from the DB.
	 *
	 * @param int $id The product id.
	 *
	 * @return array|null|object
	 */
	public function get_one_product( $id ) {
		global $wpdb;

		if ( $this->validate_number( $id ) === false ) {
			die( 'Invalid ID detected' );
		}

		$cache_key = 'get_one_product' . $id;

		$result = wp_cache_get( $cache_key );

		if ( $result ) {
			return $result;
		}

		$query = $wpdb->prepare( "
            SELECT * 
            FROM $this->table_name
            WHERE id = %d
        ", array(
				(int) $id,
			)
		);

		$result = $wpdb->get_row( $query, ARRAY_A ); // WPCS: db call ok. WPCS: unprepared SQL ok.

		wp_cache_set( $cache_key, $result );

		return $result;
	}

	/**
	 * Gets all products from the DB.
	 *
	 * @return array|null|object
	 */
	public function get_all_products() {
		global $wpdb;

		$cache_key = 'get_all_products';

		$result = wp_cache_get( $cache_key );

		if ( $result ) {
			return $result;
		}

		$query = $wpdb->prepare( "
            SELECT * 
            FROM $this->table_name
        ", array()
		);

		$result = $wpdb->get_results( $query, ARRAY_A ); // WPCS: db call ok. WPCS: unprepared SQL ok.

		wp_cache_set( $cache_key, $result );

		return $result;
	}

	/**
	 * Creates a product.
	 *
	 * @param array $data The product data.
	 *
	 * @return int
	 */
	public function create( array $data ) {
		global $wpdb;

		$data = $this->validate_and_sanitize_input( $data );

		$query = $wpdb->prepare( "
          INSERT INTO $this->table_name
            (
              merchant_id,
              product_id,
              affiliate_account_id,
              path,
              link_text,
              affiliate_url,
              affiliate_code,
              affiliate_program_name,
              merchant_name,
              redirect_code
            ) VALUES (
              %d,
              %s,
              %s,
              %s,
              %s,
              %s,
              %s,
              %s,
              %s,
              %d
            )
        ", array(
				$data['merchant_id'],
				$data['product_id'],
				$data['affiliate_account_id'],
				$data['path'],
				$data['link_text'],
				$data['affiliate_url'],
				$data['affiliate_code'],
				$data['affiliate_program_name'],
				$data['merchant_name'],
				$data['redirect_code'],
			)
		);

		$wpdb->query( $query ); // WPCS: db call ok. WPCS: unprepared SQL ok. WPCS: cache ok.

		return $wpdb->insert_id;
	}

	/**
	 * Updates a product.
	 *
	 * @param int   $id The product ID.
	 * @param array $values The updated product values.
	 *
	 * @return false|int
	 */
	public function update( $id, $values ) {
		global $wpdb;

		$data = $this->validate_and_sanitize_input( $values );

		$query = $wpdb->prepare( "
          UPDATE $this->table_name
          SET 
            merchant_id = %d,
			product_id = %s,
			affiliate_account_id = %s,
			path = %s,
			link_text = %s,
			affiliate_url = %s,
			affiliate_code = %s,
			affiliate_program_name = %s,
			merchant_name = %s,
			redirect_code = %s,
          WHERE id =  %d
        ", array(
				$data['merchant_id'],
				$data['product_id'],
				$data['affiliate_account_id'],
				$data['path'],
				$data['link_text'],
				$data['affiliate_url'],
				$data['affiliate_code'],
				$data['affiliate_program_name'],
				$data['merchant_name'],
				$data['redirect_code'],
				(int) $id,
			)
		);

		return $wpdb->query( $query ); // WPCS: db call ok. WPCS: unprepared SQL ok. WPCS: cache ok.
	}

	/**
	 * Deletes a product.
	 *
	 * @param int $id The product ID.
	 *
	 * @return false|int
	 */
	public function delete( $id ) {
		global $wpdb;

		if ( $this->validate_number( $id ) === false ) {
			die( 'Invalid ID detected' );
		}

		$query = $wpdb->prepare( "
          DELETE FROM $this->table_name
          WHERE id = %d
      ", array(
			(int) $id,
		));

		return $wpdb->query( $query ); // WPCS: db call ok. WPCS: unprepared SQL ok. WPCS: cache ok.
	}

	/**
	 * Returns the results as JSON.
	 *
	 * @return mixed|string
	 */
	public function as_json() {
		return wp_json_encode( $this->get_all_products() );
	}

	/**
	 * Validates, and sanitises input.
	 *
	 * @param array $input The input.
	 *
	 * @return array
	 */
	public function validate_and_sanitize_input( array $input ) {
		$valid = $this->validate_input( $input );

		if ( in_array( false, $valid, true ) ) {
			die( 'Invalid input detected during validation' );
		}

		$sanitised = $this->sanitize_input( $valid );

		if ( in_array( false, $sanitised, true ) ) {
			die( 'Invalid input detected during sanitisation' );
		}

		return $sanitised;
	}

	/**
	 * Validates input.
	 *
	 * @param array $data The product data as an array.
	 *
	 * @return array
	 */
	private function validate_input( array $data ) {
		return array(
			'merchant_id'            => $this->validate_number( $data['merchant_id'] ),
			'product_id'             => $this->validate_string( $data['product_id'] ),
			'affiliate_account_id'   => $this->is_guid( $data['affiliate_account_id'] ),
			'path'                   => $this->is_path( $data['path'] ),
			'link_text'              => $this->validate_string( $data['link_text'] ),
			'affiliate_url'          => $this->validate_affiliate_url( $data['affiliate_url'] ),
			'redirect_code'          => $this->is_redirect_code( $data['redirect_code'] ),
			'affiliate_code'         => $this->validate_string( $data['affiliate_code'] ),
			'affiliate_program_name' => $this->validate_string( $data['affiliate_program_name'] ),
			'merchant_name'          => $this->validate_string( $data['merchant_name'] ),
		);
	}

	/**
	 * Sanitises input.
	 *
	 * @param array $data The product data as an array.
	 *
	 * @return array
	 */
	private function sanitize_input( array $data ) {
		return array(
			'merchant_id'            => (int) $data['merchant_id'],
			'product_id'             => sanitize_text_field( $data['product_id'] ),
			'affiliate_account_id'   => $this->is_guid( $data['affiliate_account_id'] ),
			'path'                   => $this->is_path( $data['path'] ),
			'link_text'              => sanitize_text_field( $data['link_text'] ),
			'affiliate_url'          => esc_url_raw( $data['affiliate_url'] ),
			'redirect_code'          => $this->is_redirect_code( $data['redirect_code'] ),
			'affiliate_code'         => sanitize_text_field( $data['affiliate_code'] ),
			'affiliate_program_name' => sanitize_text_field( $data['affiliate_program_name'] ),
			'merchant_name'          => sanitize_text_field( $data['merchant_name'] ),
		);
	}

	/**
	 * Checks if is a GUID.
	 *
	 * @param string $guid The GUID.
	 *
	 * @return bool
	 */
	private function is_guid( $guid ) {
		return preg_match(
			'/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i',
			$guid
		) ? $guid : false;
	}

	/**
	 * Checks if is an allowable path.
	 *
	 * @param string $path The product path.
	 *
	 * @return bool
	 */
	private function is_path( $path ) {
		return preg_match(
			'/\/[A-Za-z0-9\/]+/',
			$path
		) ? $path : false;
	}

	/**
	 * Checks if is a valid Affiliate URL.
	 *
	 * @param string $affiliate_url The affiliate URL.
	 *
	 * @return bool
	 */
	private function validate_affiliate_url( $affiliate_url ) {
		return (substr( $affiliate_url, 0, 4 ) === 'http') ? $affiliate_url : false;
	}

	/**
	 * Checks if is allowable Redirect Code.
	 *
	 * @param int $redirect_code The redirect code.
	 *
	 * @return bool
	 */
	private function is_redirect_code( $redirect_code ) {
		return in_array( (int) $redirect_code, array( 301, 302, 307 ), true ) ? $redirect_code : false;
	}

	/**
	 * Checks if is a string.
	 *
	 * @param string $string The... string.
	 *
	 * @return bool
	 */
	private function validate_string( $string ) {
		return (strlen( $string ) > 0) ? $string : false;
	}

	/**
	 * Checks if is a number.
	 *
	 * @param int $number The... number.
	 *
	 * @return bool
	 */
	private function validate_number( $number ) {
		return ( (int) $number > 0) ? $number : false;
	}
}
