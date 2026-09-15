<?php get_header(); ?>

<main id="main-content" class="cotheme-content">
    <div class="cotheme-container" style="padding: 2rem 1.5rem;">
        <h1><?php printf(esc_html__('Sökresultat för: %s', 'cotheme'), esc_html(get_search_query())); ?></h1>

        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="margin-bottom: 2rem;">
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <div class="entry-summary">
                        <?php the_excerpt(); ?>
                    </div>
                </article>
            <?php endwhile; ?>

            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <p><?php esc_html_e('Inga resultat hittades.', 'cotheme'); ?></p>
            <?php get_search_form(); ?>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
