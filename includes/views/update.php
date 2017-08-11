<?php
/**
 * Wraps form that allows updating a product.
 *
 * @package get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

?>

<div class="wrap">
	<h2>Update Product</h2>

	<?php
	require( GPC_VIEWS_PATH . '/errors.php' );
	?>

	<form name="form-gpc-link" id="form-gpc-link" method="post" action="<?php echo admin_url( 'admin-post.php' ); // WPCS: XSS ok. ?>">
		<input type="hidden" name="action" value="update_gpc_link">
		<?php wp_nonce_field( 'admin_post_update_gpc_link' ); ?>
		<input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>">

		<?php
		require( GPC_VIEWS_PATH . '/form.php' );
		?>

		<p class="submit">
			<input type="submit" id="gpc-product-submit-button" name="Submit" value="Update" class="button button-primary"/>&nbsp;
		</p>
	</form>
</div>

