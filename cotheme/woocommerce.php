<?php
/**
 * WooCommerce template wrapper
 * Ger BB Themer full kontroll over shop-sidor.
 *
 * @package CoTheme
 */

get_header(); ?>

<main id="main-content" class="cotheme-content cotheme-woocommerce">
	<?php do_action('cotheme_before_content'); ?>

	<?php if (cotheme_themer_content_enabled()) : ?>
		<?php the_content(); ?>
	<?php else : ?>
		<div class="cotheme-container" style="padding: 2rem 1.5rem;">
			<?php
			if (is_singular('product')) {
				do_action('cotheme_before_product');
				woocommerce_content();
				do_action('cotheme_after_product');
			} else {
				do_action('cotheme_before_shop');
				woocommerce_content();
				do_action('cotheme_after_shop');
			}
			?>
		</div>
	<?php endif; ?>

	<?php do_action('cotheme_after_content'); ?>
</main>

<?php get_footer(); ?>
