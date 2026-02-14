<?php
/**
 * Template Name: Home Page
 *
 * The front page template - matching wireframe design.
 *
 * @package Hilal
 */

get_header();

$hijri = hilal_get_hijri_date_info();
$today = gmdate( 'l, j F Y' );

// Get latest announcement
$latest_announcement = null;
if ( class_exists( 'Hilal_Announcement' ) ) {
    $latest_announcement = Hilal_Announcement::get_latest();
}

// Get upcoming months
$upcoming_months = array();
if ( class_exists( 'Hilal_Hijri_Month' ) ) {
    $current_month = $hijri['month'] ?? 1;
    $upcoming_months = Hilal_Hijri_Month::get_upcoming_months( $current_month, 5 );
}

// Get recent announcements
$recent_announcements = array();
if ( class_exists( 'Hilal_Announcement' ) ) {
    $recent_announcements = Hilal_Announcement::get_recent( 4 );
}

// Calculate days until Ramadan (month 9)
$days_until_ramadan = 0;
$current_month = $hijri['month'] ?? 1;
$current_day = $hijri['day'] ?? 1;
if ( $current_month < 9 ) {
    // Count remaining days in current month + all days in months between + days into Ramadan
    $days_until_ramadan = ( 30 - $current_day ); // Approximate remaining days in current month
    for ( $m = $current_month + 1; $m < 9; $m++ ) {
        $days_until_ramadan += ( $m % 2 === 0 ) ? 29 : 30; // Approximate
    }
}
?>

<!-- Hijri Date Hero Section - Dark Navy with Gold -->
<section class="hijri-date-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-icon">🌙</div>

            <div class="hijri-date-display">
                <span class="hijri-day">
                    <?php echo esc_html( $hijri['day'] ?? '?' ); ?>
                    <?php echo esc_html( $hijri['month_name_en'] ?? '' ); ?>
                    <?php echo esc_html( $hijri['year'] ?? '' ); ?>
                </span>
                <span class="hijri-month-ar">
                    <?php echo esc_html( hilal_convert_to_arabic_numerals( $hijri['day'] ?? '' ) ) . ' ' . esc_html( $hijri['month_name_ar'] ?? '' ) . ' ' . esc_html( hilal_convert_to_arabic_numerals( $hijri['year'] ?? '' ) ) . ' هـ'; ?>
                </span>
            </div>

            <div class="gregorian-date">
                <?php echo esc_html( $today ); ?>
            </div>

            <?php if ( $current_month < 9 ) : ?>
            <!-- Countdown to Ramadan -->
            <div class="countdown-wrapper" id="ramadan-countdown">
                <div class="countdown-item">
                    <span class="value" id="countdown-days"><?php echo esc_html( $days_until_ramadan ); ?></span>
                    <span class="label">Days</span>
                </div>
                <div class="countdown-separator"></div>
                <div class="countdown-item">
                    <span class="value" id="countdown-hours">0</span>
                    <span class="label">Hours</span>
                </div>
                <div class="countdown-separator"></div>
                <div class="countdown-item">
                    <span class="value" id="countdown-minutes">0</span>
                    <span class="label">Minutes</span>
                </div>
            </div>
            <div class="countdown-title">
                UNTIL RAMADAN <?php echo esc_html( $hijri['year'] ?? '' ); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Latest Announcement Banner -->
<?php if ( $latest_announcement ) : ?>
<div class="announcement-banner <?php echo esc_attr( $latest_announcement['priority'] ?? 'medium' ); ?>">
    <span class="badge-new">New</span>
    <div class="content">
        <span class="title">
            <?php echo esc_html( $latest_announcement['title_en'] ); ?> —
        </span>
        <span class="excerpt">
            <?php echo esc_html( wp_trim_words( wp_strip_all_tags( $latest_announcement['body_en'] ), 15 ) ); ?>
        </span>
    </div>
</div>
<?php endif; ?>

<!-- Main Content -->
<section class="section">
    <div class="container">
        <!-- Quick Links Cards -->
        <div class="quick-actions">
            <a href="/calendar/" class="quick-action-card calendar">
                <span class="icon">📅</span>
                <span class="label">Hijri Calendar</span>
                <span class="description">View the full 1447 AH calendar with confirmed dates</span>
            </a>
            <a href="/announcements/" class="quick-action-card announcements">
                <span class="icon">📢</span>
                <span class="label">Announcements</span>
                <span class="description">Latest month confirmations and Islamic events</span>
            </a>
            <a href="/crescent-sightings/" class="quick-action-card report">
                <span class="icon">🌙</span>
                <span class="label">Crescent Sightings</span>
                <span class="description">View approved moon sightings from the community</span>
            </a>
            <a href="/prayer-times/" class="quick-action-card regional">
                <span class="icon">🕌</span>
                <span class="label">Prayer Times</span>
                <span class="description">Prayer times for New Zealand cities</span>
            </a>
        </div>

        <!-- Two Columns: Upcoming Months & Recent Announcements -->
        <div class="two-columns">
            <!-- Upcoming Months -->
            <div class="upcoming-months-list">
                <div class="list-title">Upcoming Months</div>
                <?php
                if ( ! empty( $upcoming_months ) ) :
                    foreach ( $upcoming_months as $month ) :
                        $is_current = ( (int) $month['month_number'] === $current_month );
                ?>
                    <div class="month-list-item <?php echo $is_current ? 'current' : ''; ?>">
                        <div class="month-info">
                            <div class="month-num"><?php echo esc_html( $month['month_number'] ); ?></div>
                            <div class="month-names">
                                <div class="en"><?php echo esc_html( $month['month_name_en'] ); ?></div>
                                <div class="ar"><?php echo esc_html( $month['month_name_ar'] ); ?></div>
                            </div>
                        </div>
                        <div class="month-date">
                            <div class="date">
                                <?php echo $month['gregorian_start'] ? esc_html( hilal_format_date( $month['gregorian_start'] ) ) : '—'; ?>
                            </div>
                            <div class="status <?php echo esc_attr( $month['status'] ?? 'estimated' ); ?>">
                                <?php
                                $status = $month['status'] ?? 'estimated';
                                if ( $status === 'confirmed' ) {
                                    echo '✓ Confirmed';
                                } elseif ( $status === 'pending_sighting' ) {
                                    echo '🌙 Pending Sighting';
                                } else {
                                    echo '📊 Calculated';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php
                    endforeach;
                else :
                ?>
                    <p class="text-muted">No data available</p>
                <?php endif; ?>
            </div>

            <!-- Recent Announcements -->
            <div class="upcoming-months-list">
                <div class="list-title">Recent Announcements</div>
                <?php
                if ( ! empty( $recent_announcements ) ) :
                    foreach ( $recent_announcements as $ann ) :
                ?>
                    <div class="month-list-item">
                        <div class="month-info" style="align-items: flex-start;">
                            <div class="priority-dot <?php echo esc_attr( $ann['priority'] ?? 'medium' ); ?>" style="margin-top: 6px;"></div>
                            <div class="month-names">
                                <div class="en" style="font-size: 0.875rem;"><?php echo esc_html( $ann['title_en'] ); ?></div>
                            </div>
                        </div>
                        <div class="month-date">
                            <div class="date" style="font-size: 0.6875rem;">
                                <?php echo esc_html( get_the_date( 'M j, Y', $ann['id'] ) ); ?>
                            </div>
                        </div>
                    </div>
                <?php
                    endforeach;
                else :
                ?>
                    <p class="text-muted">No announcements</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Subscribe Section -->
        <div class="subscribe-section">
            <div class="icon">🔔</div>
            <h3>Never Miss a Moon Sighting</h3>
            <p>Subscribe to get instant push notifications for new month confirmations</p>
            <form class="subscribe-form" id="home-subscribe">
                <input type="email" name="email" placeholder="Enter your email" required>
                <button type="submit">Subscribe</button>
            </form>
            <div class="subscribe-buttons">
                <button type="button">📱 Get the App</button>
                <button type="button">📧 Email Alerts</button>
            </div>
        </div>
    </div>
</section>

<script>
// Simple countdown timer (placeholder - would need actual Ramadan date calculation)
document.addEventListener('DOMContentLoaded', function() {
    const hoursEl = document.getElementById('countdown-hours');
    const minutesEl = document.getElementById('countdown-minutes');

    if (hoursEl && minutesEl) {
        function updateTime() {
            const now = new Date();
            hoursEl.textContent = 23 - now.getHours();
            minutesEl.textContent = 59 - now.getMinutes();
        }
        updateTime();
        setInterval(updateTime, 60000);
    }

    // Subscribe form
    const subscribeForm = document.getElementById('home-subscribe');
    if (subscribeForm) {
        subscribeForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = this.querySelector('input[name="email"]').value;

            try {
                const response = await hilalAPI.post('subscribe', { email: email });
                if (response.success) {
                    hilalShowNotification('Successfully subscribed!', 'success');
                    this.reset();
                }
            } catch (error) {
                hilalShowNotification(error.message || 'Error subscribing', 'error');
            }
        });
    }
});
</script>

<?php
get_footer();
