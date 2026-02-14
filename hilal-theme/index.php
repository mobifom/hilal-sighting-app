<?php
/**
 * Main template file
 *
 * @package Hilal
 */

get_header();
$lang = hilal_get_language();
?>

<div class="container">
    <section class="section">
        <?php if ( have_posts() ) : ?>
            <div class="posts-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="card-image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'medium_large' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <header class="entry-header">
                                <?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
                            </header>

                            <div class="entry-excerpt">
                                <?php the_excerpt(); ?>
                            </div>

                            <footer class="entry-footer">
                                <span class="posted-on">
                                    <?php echo get_the_date(); ?>
                                </span>
                            </footer>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php the_posts_navigation(); ?>

        <?php else : ?>
            <div class="no-results">
                <h2><?php echo hilal_is_arabic() ? 'لا توجد نتائج' : 'Nothing Found'; ?></h2>
                <p>
                    <?php
                    if ( hilal_is_arabic() ) {
                        echo 'يبدو أننا لم نجد ما تبحث عنه.';
                    } else {
                        echo 'It seems we can\'t find what you\'re looking for.';
                    }
                    ?>
                </p>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php
get_footer();
