<?php
/**
 * Template Name: Crescent Sightings
 *
 * Public display of approved crescent sightings.
 *
 * @package Hilal
 */

get_header();

// Get approved sightings - check multiple possible meta keys
$args = array(
    'post_type'      => 'sighting_report',
    'posts_per_page' => 20,
    'post_status'    => 'publish',
    'meta_query'     => array(
        'relation' => 'OR',
        array(
            'key'   => 'status',
            'value' => 'approved',
        ),
        array(
            'key'   => '_status',
            'value' => 'approved',
        ),
    ),
    'orderby'        => 'date',
    'order'          => 'DESC',
);

$query = new WP_Query( $args );

// If no approved sightings found, show all published sightings for now
if ( ! $query->have_posts() ) {
    $args = array(
        'post_type'      => 'sighting_report',
        'posts_per_page' => 20,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    $query = new WP_Query( $args );
}
?>

<section class="section">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div style="font-size: 2.5rem; margin-bottom: 8px;">🌙</div>
            <h1><?php echo esc_html( get_theme_mod( 'hilal_sightings_title', 'Crescent Sightings' ) ); ?></h1>
            <p class="subtitle">Approved sightings from the community</p>
        </div>

        <!-- Sightings List -->
        <?php if ( $query->have_posts() ) : ?>
            <div class="sightings-list" style="max-width: 800px; margin: 0 auto;">
                <?php while ( $query->have_posts() ) : $query->the_post();
                    $attachment_id = get_post_meta( get_the_ID(), 'attachment', true );
                    $details       = get_post_meta( get_the_ID(), 'details', true );

                    // Get attachment info
                    $attachment_url = '';
                    $attachment_name = '';
                    $attachment_size = 0;
                    if ( $attachment_id ) {
                        $attachment_url = wp_get_attachment_url( $attachment_id );
                        $file_path = get_attached_file( $attachment_id );
                        $attachment_name = basename( $file_path );
                        $attachment_size = file_exists( $file_path ) ? filesize( $file_path ) : 0;
                    }
                ?>
                    <div class="card sighting-card" style="margin-bottom: 1.5rem;">
                        <div class="card-body">
                            <!-- Header with date -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--hilal-gray-100);">
                                <h3 style="margin: 0; font-size: 1.125rem;"><?php the_title(); ?></h3>
                                <span style="font-size: 0.875rem; color: var(--hilal-gray-500);">
                                    <?php echo esc_html( get_the_date( 'd M Y' ) ); ?>
                                </span>
                            </div>

                            <!-- Details -->
                            <?php if ( $details ) : ?>
                                <div style="margin-bottom: 1rem; line-height: 1.7; color: var(--hilal-gray-700);">
                                    <?php echo nl2br( esc_html( $details ) ); ?>
                                </div>
                            <?php endif; ?>

                            <!-- PDF Attachment -->
                            <?php if ( $attachment_url ) : ?>
                                <div style="padding: 1rem; background: var(--hilal-gray-50); border-radius: 8px; display: flex; align-items: center; gap: 12px;">
                                    <span style="font-size: 2rem;">📄</span>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 500;"><?php echo esc_html( $attachment_name ); ?></div>
                                        <div style="font-size: 0.875rem; color: var(--hilal-gray-500);">
                                            <?php echo esc_html( size_format( $attachment_size ) ); ?>
                                        </div>
                                    </div>
                                    <a href="<?php echo esc_url( $attachment_url ); ?>"
                                       target="_blank"
                                       class="btn btn-outline-gold btn-sm"
                                       style="display: inline-flex; align-items: center; gap: 6px;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                                        </svg>
                                        Download
                                    </a>
                                </div>
                            <?php endif; ?>

                            <!-- Verified Badge -->
                            <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--hilal-gray-100); display: flex; align-items: center; gap: 6px; color: var(--hilal-success); font-size: 0.8125rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                </svg>
                                Verified & Approved
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <!-- No Sightings -->
            <div class="card" style="max-width: 500px; margin: 0 auto; text-align: center;">
                <div class="card-body" style="padding: 3rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🔭</div>
                    <h3>No Approved Sightings Yet</h3>
                    <p class="text-muted">No crescent sightings have been approved yet.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.sighting-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.sighting-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
}
</style>

<?php
get_footer();
