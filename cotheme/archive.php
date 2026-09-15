<?php get_header(); ?>

<main id="main-content" class="cotheme-content">
    <?php do_action('cotheme_before_content'); ?>

    <?php if (cotheme_themer_content_enabled()) : ?>
        <?php the_content(); ?>
    <?php else : ?>
        <div class="cotheme-container" style="padding: 2rem 1.5rem;">
            <header>
                <?php the_archive_title('<h1>', '</h1>'); ?>
                <?php the_archive_description('<p>', '</p>'); ?>
            </header>

            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="margin-bottom: 2rem;">
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                        <div class="entry-summary">
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>

                <?php the_posts_pagination(); ?>
            <?php else : ?>
                <p><?php esc_html_e('Inga inlägg hittades.', 'cotheme'); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php do_action('cotheme_after_content'); ?>
</main>

<?php get_footer(); ?>
