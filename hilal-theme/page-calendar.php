<?php
/**
 * Template Name: Hijri Calendar
 *
 * Hijri calendar page - matching wireframe design.
 *
 * @package Hilal
 */

get_header();

$hijri        = hilal_get_hijri_date_info();
$current_year = $hijri['year'] ?? 1446;

// Get year from URL parameter
$selected_year = isset( $_GET['year'] ) ? (int) $_GET['year'] : $current_year;

// Get months for the selected year
$months = array();
if ( class_exists( 'Hilal_Hijri_Month' ) ) {
    $months = Hilal_Hijri_Month::get_year_months( $selected_year );

    // Create months if none exist
    if ( empty( $months ) ) {
        Hilal_Hijri_Month::create_year_months( $selected_year );
        $months = Hilal_Hijri_Month::get_year_months( $selected_year );
    }
}

// Get all events
$events = array();
if ( class_exists( 'Hilal_Islamic_Event' ) ) {
    $events = Hilal_Islamic_Event::get_all_events();
}

$current_month = $hijri['month'] ?? 0;
?>

<section class="section">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><?php echo esc_html( get_theme_mod( 'hilal_calendar_title', 'Hijri Calendar' ) ); ?> — <?php echo esc_html( $selected_year ); ?> AH</h1>
            <p class="subtitle"><?php echo esc_html( get_theme_mod( 'hilal_calendar_subtitle', 'Complete year with Gregorian dates' ) ); ?></p>
        </div>

        <!-- Year Navigation -->
        <div style="display: flex; justify-content: center; align-items: center; gap: 2rem; margin-bottom: 2rem;">
            <a href="<?php echo esc_url( add_query_arg( 'year', $selected_year - 1 ) ); ?>" class="btn btn-outline">
                ← Previous Year
            </a>
            <span style="font-size: 1.25rem; font-weight: 700; color: var(--hilal-gold);">
                <?php echo esc_html( $selected_year ); ?> AH
            </span>
            <a href="<?php echo esc_url( add_query_arg( 'year', $selected_year + 1 ) ); ?>" class="btn btn-outline">
                Next Year →
            </a>
        </div>

        <!-- Calendar Grid -->
        <div class="calendar-grid">
            <?php
            // Ensure we have all 12 months
            for ( $m = 1; $m <= 12; $m++ ) :
                // Find month data
                $month_data = null;
                foreach ( $months as $month ) {
                    if ( (int) $month['month_number'] === $m ) {
                        $month_data = $month;
                        break;
                    }
                }

                // Fallback if month not found
                if ( ! $month_data && class_exists( 'Hilal_Hijri_Date' ) ) {
                    $month_data = array(
                        'month_number'    => $m,
                        'hijri_year'      => $selected_year,
                        'month_name_en'   => Hilal_Hijri_Date::get_month_name( $m, 'en' ),
                        'month_name_ar'   => Hilal_Hijri_Date::get_month_name( $m, 'ar' ),
                        'gregorian_start' => null,
                        'gregorian_end'   => null,
                        'days_count'      => ( $m % 2 === 0 ) ? 29 : 30, // Approximate
                        'status'          => 'estimated',
                    );
                }

                // Check if current month
                $is_current = ( $selected_year == $current_year && $m == $current_month );

                // Check if past month (confirmed)
                $is_past = ( $selected_year < $current_year ) || ( $selected_year == $current_year && $m < $current_month );

                // Get events for this month
                $month_events = array_filter( $events, function( $e ) use ( $m ) {
                    return $e['hijri_month'] === $m;
                } );
            ?>
                <div class="month-card <?php echo $is_current ? 'current' : ''; ?>">
                    <?php if ( $is_current ) : ?>
                        <span class="current-badge">Current Month</span>
                    <?php endif; ?>

                    <div class="month-card-header">
                        <div class="month-number"><?php echo esc_html( $m ); ?></div>
                        <div>
                            <h3 class="month-name">
                                <?php echo esc_html( $month_data['month_name_en'] ); ?>
                            </h3>
                            <p class="month-name-ar">
                                <?php echo esc_html( $month_data['month_name_ar'] ); ?>
                            </p>
                        </div>
                    </div>

                    <div class="month-details">
                        <div>
                            <div class="month-detail-label">Starts</div>
                            <div class="month-detail-value">
                                <?php
                                if ( $month_data['gregorian_start'] ) {
                                    echo esc_html( hilal_format_date( $month_data['gregorian_start'] ) );
                                } else {
                                    echo '<span class="text-muted">Pending</span>';
                                }
                                ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div class="month-detail-label">Duration</div>
                            <div class="month-detail-value">
                                <?php echo esc_html( $month_data['days_count'] ?? '—' ); ?> days
                            </div>
                        </div>
                    </div>

                    <div class="month-status <?php echo esc_attr( $month_data['status'] ?? 'estimated' ); ?>">
                        <?php
                        $status = $month_data['status'] ?? 'estimated';
                        if ( $status === 'confirmed' || $is_past ) {
                            echo '✓ Confirmed by Sighting';
                        } elseif ( $status === 'pending_sighting' ) {
                            echo '🌙 Pending Sighting';
                        } else {
                            echo '📊 Calculated';
                        }
                        ?>
                    </div>

                    <?php if ( ! empty( $month_events ) ) : ?>
                        <div class="month-events">
                            <?php foreach ( array_values( $month_events ) as $event ) : ?>
                                <span class="event-tag <?php echo esc_attr( $event['event_category'] ?? '' ); ?>">
                                    <?php echo esc_html( $event['hijri_day'] . ' - ' . $event['event_name_en'] ); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Legend -->
        <div style="margin-top: 2rem; padding: 1rem; background: var(--hilal-white); border-radius: 14px; border: 1px solid var(--hilal-border); display: flex; gap: 2rem; flex-wrap: wrap; justify-content: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="month-status confirmed" style="margin: 0;">✓</span>
                <span>Confirmed</span>
                <small class="text-muted">Moon sighted</small>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="month-status pending_sighting" style="margin: 0;">🌙</span>
                <span>Pending Sighting</span>
                <small class="text-muted">Awaiting moon sighting</small>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="month-status estimated" style="margin: 0;">📊</span>
                <span>Calculated</span>
                <small class="text-muted">Calculated estimates</small>
            </div>
        </div>
    </div>
</section>

<?php
get_footer();
