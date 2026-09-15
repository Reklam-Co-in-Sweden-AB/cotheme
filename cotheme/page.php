<?php get_header(); ?>

<main id="main-content" class="cotheme-content">
    <?php do_action('cotheme_before_content'); ?>

    <?php while (have_posts()) : the_post(); ?>

        <?php do_action('cotheme_before_post'); ?>

        <?php if (cotheme_themer_content_enabled()) : ?>
            <?php do_action('cotheme_before_post_content'); ?>
            <?php the_content(); ?>
            <?php do_action('cotheme_after_post_content'); ?>
        <?php else : ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <?php the_content(); ?>
            </article>
        <?php endif; ?>

        <?php do_action('cotheme_after_post'); ?>

    <?php endwhile; ?>

    <?php do_action('cotheme_after_content'); ?>
</main>

<?php get_footer(); ?>
