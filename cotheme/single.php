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
                <?php if (!cotheme_is_title_hidden()) : ?>
                    <div class="cotheme-container">
                        <header style="margin-bottom: 2rem;">
                            <h1><?php the_title(); ?></h1>
                            <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                        </header>
                    </div>
                <?php endif; ?>

                <?php the_content(); ?>

                <?php if (!cotheme_is_nav_hidden()) : ?>
                    <div class="cotheme-container" style="margin-top: 2rem;">
                        <?php
                        the_post_navigation([
                            'prev_text' => '<span class="screen-reader-text">' . __('Föregående:', 'cotheme') . '</span> %title',
                            'next_text' => '%title <span class="screen-reader-text">' . __('Nästa:', 'cotheme') . '</span>',
                        ]);
                        ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endif; ?>

        <?php do_action('cotheme_after_post'); ?>

    <?php endwhile; ?>

    <?php do_action('cotheme_after_content'); ?>
</main>

<?php get_footer(); ?>
