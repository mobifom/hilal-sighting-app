<?php
/**
 * Template Name: Announcements
 *
 * Announcements listing page - matching wireframe design.
 *
 * @package Hilal
 */

get_header();

// Get filter from query string
$filter_type = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '';

// Get all announcements
$announcements = array();
if ( class_exists( 'Hilal_Announcement' ) ) {
    $announcements = Hilal_Announcement::get_all( $filter_type );
}

$type_labels = array(
    'month_start'   => 'Month Start',
    'moon_sighting' => 'Sighting',
    'islamic_event' => 'Event',
    'general'       => 'General',
);
?>

<section class="section">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Announcements</h1>
            <p class="subtitle">Official moon sighting confirmations and Islamic events</p>
        </div>

        <!-- Filter Tabs -->
        <div style="display: flex; gap: 8px; margin-bottom: 24px; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo esc_url( remove_query_arg( 'type' ) ); ?>"
               class="filter-btn <?php echo empty( $filter_type ) ? 'active' : ''; ?>">
                All
            </a>
            <a href="<?php echo esc_url( add_query_arg( 'type', 'month_start' ) ); ?>"
               class="filter-btn <?php echo $filter_type === 'month_start' ? 'active' : ''; ?>">
                Month Start
            </a>
            <a href="<?php echo esc_url( add_query_arg( 'type', 'moon_sighting' ) ); ?>"
               class="filter-btn <?php echo $filter_type === 'moon_sighting' ? 'active' : ''; ?>">
                Sighting
            </a>
            <a href="<?php echo esc_url( add_query_arg( 'type', 'islamic_event' ) ); ?>"
               class="filter-btn <?php echo $filter_type === 'islamic_event' ? 'active' : ''; ?>">
                Event
            </a>
            <a href="<?php echo esc_url( add_query_arg( 'type', 'general' ) ); ?>"
               class="filter-btn <?php echo $filter_type === 'general' ? 'active' : ''; ?>">
                General
            </a>
        </div>

        <!-- Announcements List -->
        <div class="announcements-list">
            <?php if ( ! empty( $announcements ) ) : ?>
                <?php foreach ( $announcements as $ann ) : ?>
                    <article class="announcement-item">
                        <div class="meta">
                            <span class="priority-dot <?php echo esc_attr( $ann['priority'] ?? 'medium' ); ?>"></span>
                            <span class="type-badge">
                                <?php echo esc_html( $type_labels[ $ann['type'] ] ?? $ann['type'] ); ?>
                            </span>
                            <span class="date"><?php echo esc_html( get_the_date( 'M j, Y', $ann['id'] ) ); ?></span>
                        </div>

                        <h3><?php echo esc_html( $ann['title_en'] ); ?></h3>

                        <div class="body">
                            <?php echo wp_kses_post( wpautop( $ann['body_en'] ) ); ?>
                        </div>

                        <div class="actions">
                            <button type="button" onclick="hilalShare('<?php echo esc_url( $ann['url'] ); ?>')">
                                🔗 Share
                            </button>
                            <button type="button" onclick="window.print()">
                                🖨️ Print
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="text-center" style="padding: 3rem;">
                    <p class="text-muted">No announcements at this time.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
function hilalShare(url) {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: url
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(function() {
            hilalShowNotification('Link copied to clipboard', 'success');
        });
    }
}
</script>

<?php
get_footer();
