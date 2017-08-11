<?php
/**
 * Displays table containing added products.
 *
 * @package get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit;} ?>

<div class="wrap">
	<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=get_price_comparison-products-add' ) ) ); ?>"
	   class="button button-primary">Add New Product</a>

	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<form id="products-filter" method="post">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page" value="get_price_comparison-products" />
		<!-- Now we can render the completed list table -->
		<?php $product_list_table->display() ?>
	</form>

	<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=get_price_comparison-products-add' ) ) ); ?>"
	   class="button button-primary">Add New Product</a>
</div>
