<?php
/**
 * Display plugin license key as a hidden field on a page, primarily useful as an include
 *
 * @package get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<input type="hidden" id="gpc-license-key" value="<?php echo get_option( 'gpc_license_key' ); // WPCS: XSS ok. ?>" />
