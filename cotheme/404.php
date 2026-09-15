<?php get_header(); ?>

<main id="main-content" class="cotheme-content">
    <div class="cotheme-container" style="text-align: center; padding: 4rem 1.5rem;">
        <h1>404</h1>
        <p><?php esc_html_e('Sidan kunde inte hittas.', 'cotheme'); ?></p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="cotheme-btn" style="margin-top: 1rem;">
            <?php esc_html_e('Tillbaka till startsidan', 'cotheme'); ?>
        </a>
    </div>
</main>

<?php get_footer(); ?>
