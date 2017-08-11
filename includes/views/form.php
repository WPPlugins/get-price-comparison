<?php
/**
 * Form for creating / updating a product.
 *
 * @package get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } ?>


<table class="form-table">
	<tr class="form-field">
		<td width="205px" valign="top">
			Affiliate Account
		</td>
		<td>
			<select name="affiliate_account_select">
<!--                populated by Javascript -->
			</select>
		</td>
	</tr>


	<tr class="form-field">
		<td width="205px" valign="top">
			Merchant
		</td>
		<td>
			<select name="merchant_id">
<!--                populated by Javascript -->
			</select>
		</td>
	</tr>


	<tr class="form-field">
		<td width="205px" valign="top">
			Product URL
		</td>
		<td>
			<input type="text" name="product_url" value="<?php echo esc_attr( $product_data['product_url'] ); ?>" size="30"/>
		</td>
	</tr>


	<tr class="form-field">
		<td width="205px" valign="top">
			Product ID
		</td>
		<td>
			<input type="text" name="product_id" value="<?php echo esc_attr( $product_data['product_id'] ); ?>" size="30"/>
			<span class="description toggle_pane">
                <br/>The product ID is not always immediately obvious.<br/>If unsure, please copy / paste the product's web page URL into the 'Product URL' box above, which will find the right product ID for you.
            </span>
		</td>
	</tr>


	<tr class="form-field"
		id="gpc-product-preview">
	</tr>

	<tr class="form-field"
		id="gpc-product-path">
		<td width="205px" valign="top">
			Link
		</td>
		<td>
			<input type="text"
                   id="gpc-product-path-input"
				   name="path"
				   pattern="\/.+?"
				   value="<?php echo esc_attr( $product_data['path'] ); ?>"
				   size="30"/>
			<span id="gpc-path-validation-output"></span>
			<span class="description toggle_pane"><br/>Must be in the format <code>/path/visitor/will/see</code>, when
				your visitor hovers over the link for this product.<br/><a href="#" id="click-to-use-suggested-link-path">Click to use suggested link path</a>
			</span>
		</td>
	</tr>

	<tr class="form-field"
		id="gpc-link-text">
		<td width="205px" valign="top">
			Link Text
		</td>
		<td>
			<input type="text"
				   name="link_text"
				   value="<?php echo esc_attr( $product_data['link_text'] ); ?>"
				   size="30"/>
			<span id="gpc-link-text-validation-output"></span>
			<span class="description toggle_pane"><br/>
				<code>__PRICE__</code> will be replaced automatically with the current price.<br/>
				<a href="#" id="click-to-use-suggested-link-text">Click to use suggested link text</a>
			</span>
		</td>
	</tr>

	<tr class="form-field hidden" id="gpc-affiliate-url">
		<td width="205px" valign="top">
			Affiliate URL
		</td>
		<td>
			<input type="text"
				   name="affiliate_url"
				   readonly="readonly"
				   value="<?php echo esc_attr( $product_data['affiliate_url'] ); ?>"
				   size="30"/>
			<br/>
			<a href="<?php echo esc_html( $product_data['affiliate_url'] ); ?>" target="_blank">Test this link</a>
		</td>
	</tr>

	<input type="hidden" name="redirect_code" value="<?php echo esc_attr( $product_data['redirect_code'] ); ?>"/>

	<input type="hidden" name="affiliate_program_name" value="<?php echo esc_attr( $product_data['affiliate_program_name'] ); ?>"/>
	<input type="hidden" name="affiliate_account_id" value="<?php echo esc_attr( $product_data['affiliate_account_id'] ); ?>"/>
	<input type="hidden" name="affiliate_code" value="<?php echo esc_attr( $product_data['affiliate_code'] ); ?>"/>
	<input type="hidden" name="merchant_name" value="<?php echo esc_attr( $product_data['merchant_name'] ); ?>"/>
	<input type="hidden" name="product_id" value="<?php echo esc_attr( $product_data['product_id'] ); ?>"/>

</table>



<script type="text/javascript">

    var pathChecker = "<?php echo admin_url( 'admin.php?page=get_price_comparison-path_checker' ); ?>";

	var updateProduct = {
		affiliate_account_id: <?php echo esc_html( $product_data['affiliate_account_id'] ) ? '"' . esc_html( $product_data['affiliate_account_id'] ) . '"' : '""'; ?>,
		merchant_id: <?php echo esc_html( $product_data['merchant_id'] ) ? '"' . esc_html( $product_data['merchant_id'] ) . '"' : '""'; ?>,
		product_id: <?php echo esc_html( $product_data['product_id'] ) ? '"' . esc_html( $product_data['product_id'] ) . '"' : '""'; ?>,
		path: <?php echo esc_html( $product_data['path'] ) ? '"' . esc_html( $product_data['path'] ) . '"' : '""'; ?>,
		link_text: <?php echo esc_html( $product_data['affiliate_account_id'] ) ? '"' . esc_html( $product_data['affiliate_account_id'] ) . '"' : '""'; ?>
	};

	jQuery( document ).ready( function ( e ) {
		addProductDropDownHelper(updateProduct);
	});
</script>
