<?php
/**
 * Path Checker
 *
 * @package get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Determine if a given path is already taken
 */
function get_price_comparison_path_checker() {

	$raw_query_string = (string) $_SERVER['QUERY_STRING'];
	$processed_query_string = urldecode( wp_unslash( $raw_query_string ) );
	$query_string = esc_attr( preg_replace( '#/$#','', $processed_query_string ) );

	$parsed_output = array();
	parse_str( $query_string, $parsed_output );

	if ( $parsed_output['page'] !== 'get_price_comparison-path_checker' ) {
		return false;
	}

	$path_to_check = $_POST['path'];
	$processed_path = urldecode( wp_unslash( $path_to_check ) );
	$path = esc_attr( preg_replace( '#/$#','', $processed_path ) );

	$response = wp_safe_remote_head( home_url() . $path );

	if ( $response['response']['code'] !== 404 ) {
		return wp_send_json( '<span class="dashicons dashicons-no"></span><br/>This path is already in use, please use a different one.' );
	}

	// this path has not yet been used
	return wp_send_json( true );
}

add_action( 'init', 'get_price_comparison_path_checker' ); // Redirect.
