<?php get_header(); ?>

<main id="main-content" class="cotheme-content">
    <?php do_action('cotheme_before_content'); ?>

    <?php if (have_posts()) : ?>
        <div class="cotheme-container">
            <h1 class="screen-reader-text"><?php echo esc_html(get_bloginfo('name')); ?></h1>
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                    </header>
                    <div class="entry-summary">
                        <?php the_excerpt(); ?>
                    </div>
                </article>
            <?php endwhile; ?>

            <?php the_posts_pagination(); ?>
        </div>
    <?php else : ?>
        <div class="cotheme-container">
            <p><?php esc_html_e('Inga inlägg hittades.', 'cotheme'); ?></p>
        </div>
    <?php endif; ?>

    <?php do_action('cotheme_after_content'); ?>
</main>

<?php get_footer(); ?>
