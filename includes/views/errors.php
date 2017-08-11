<?php
/**
 * Display errors if available.
 *
 * @package get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( isset( $errors ) and count( $errors ) > 0 ) {
	?>
	<div class="error">
		<ul>
			<?php
			?>
		</ul>
	</div>
	<?php
}
?>
