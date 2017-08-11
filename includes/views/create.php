<?php
/**
 * Wraps form that allows creating a product.
 *
 * @package get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit;} ?>

<div class="wrap"">
	<h2>Add a New Product</h2>

	<?php
	require( GPC_VIEWS_PATH . '/errors.php' );
	?>

	<form name="form-gpc-link" id="form-gpc-link" method="post" action="<?php echo admin_url( 'admin-post.php' ); // WPCS: XSS ok. ?>">
		<input type="hidden" name="action" value="add_gpc_link">
		<?php wp_nonce_field( 'admin_post_add_gpc_link' ); ?>

		<?php
		require( GPC_VIEWS_PATH . '/form.php' );
		?>

		<p class="submit">
			<input type="submit"
					disabled="disabled"
					id="gpc-product-submit-button"
					name="Submit"
					value="Create"
					class="button button-primary"
            />&nbsp;
		</p>

	</form>
</div>
