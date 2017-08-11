<?php
/**
 * Page shown after record delete.
 *
 * @package get-price-comparison
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$url = esc_url( wp_nonce_url( admin_url( 'admin.php?page=get_price_comparison-products' ) ) );

?>

<br />

<p>Deleted.</p>

<p>Redirecting...</p>

<br />

<a href="<?php echo $url; ?>" class="button button-primary">Back</a>

<script>
    setTimeout(function() {
        window.location.href = "<?php echo $url; ?>";
    }, 1500);
</script>
