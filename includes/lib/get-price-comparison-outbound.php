<?php
/**
 * Outbound URL processing
 *
 * @package get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Determine the real outbound link
 */
function get_price_comparison_outbound() {

	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) { // WPCS: input var okay. WPCS: sanitization ok.
		return false;
	}

	if ( ! isset( $_SERVER['SERVER_PROTOCOL'] ) ) { // WPCS: input var okay. WPCS: sanitization ok.
		return false;
	}

	$raw_request_uri = $_SERVER['REQUEST_URI']; // WPCS: input var okay. WPCS: sanitization ok.
	$processed_request_uri = urldecode( wp_unslash( $raw_request_uri ) );
	$request_uri = esc_attr( preg_replace( '#/$#','', $processed_request_uri ) );

	global $wpdb;

	$table_name = "{$wpdb->prefix}get_price_comparison";

	$cache_key = 'outbound_link' . $request_uri;

	$link = wp_cache_get( $cache_key );

	if ( null === $link || false === $link ) {

		$query = $wpdb->prepare( '
        SELECT *
        FROM ' . $table_name . '
        WHERE path = %s
    ', array(
			$request_uri,
		) );

		$link = $wpdb->get_row( $query ); // WPCS: db call ok. WPCS: unprepared SQL ok.

		wp_cache_set( $cache_key, $link );

	}

	if ( null === $link || false === $link ) {
		return false;
	}

	switch ( $link->redirect_type ) {

		case 301:
			header( 'HTTP/1.1 301 Moved Permanently' );
			break;

		case 302:
			if ( 'HTTP/1.0' === esc_attr( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ) ) ) { // WPCS: input var okay. WPCS: sanitization ok.
				header( 'HTTP/1.1 302 Found' );
			}
			break;

		case 307:
		default:
			header( 'HTTP/1.1 307 Temporary Redirect' );
			break;
	}

	header( 'Location: ' . $link->affiliate_url );
	exit;

}

add_action( 'init', 'get_price_comparison_outbound' ); // Redirect.
