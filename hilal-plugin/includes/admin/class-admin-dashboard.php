<?php
/**
 * Admin Dashboard Customizations
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Admin Dashboard Class
 */
class Hilal_Admin_Dashboard {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
        add_action( 'admin_menu', array( $this, 'add_admin_menu_pages' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Add dashboard widgets
     */
    public function add_dashboard_widgets() {
        // Pending Reports Widget
        wp_add_dashboard_widget(
            'hilal_pending_reports',
            __( 'Pending Sighting Reports', 'hilal' ),
            array( $this, 'render_pending_reports_widget' )
        );

        // Current Hijri Month Widget
        wp_add_dashboard_widget(
            'hilal_current_month',
            __( 'Current Hijri Month', 'hilal' ),
            array( $this, 'render_current_month_widget' )
        );

        // Quick Stats Widget
        wp_add_dashboard_widget(
            'hilal_stats',
            __( 'Hilal Statistics', 'hilal' ),
            array( $this, 'render_stats_widget' )
        );
    }

    /**
     * Render pending reports widget
     */
    public function render_pending_reports_widget() {
        $pending_count = Hilal_Sighting_Report::count_pending();
        $pending_reports = Hilal_Sighting_Report::get_by_status( 'pending', 5 );

        echo '<div class="hilal-widget">';

        if ( $pending_count > 0 ) {
            echo '<p class="hilal-alert">';
            echo '<span class="dashicons dashicons-warning" style="color: #d63638;"></span> ';
            printf(
                /* translators: %d: number of pending reports */
                _n(
                    '%d sighting report pending review',
                    '%d sighting reports pending review',
                    $pending_count,
                    'hilal'
                ),
                $pending_count
            );
            echo '</p>';

            echo '<ul class="hilal-report-list">';
            foreach ( $pending_reports as $report ) {
                $edit_link = get_edit_post_link( $report['id'] );
                echo '<li>';
                echo '<a href="' . esc_url( $edit_link ) . '">';
                echo '<strong>' . esc_html( $report['observer_name'] ) . '</strong>';
                echo ' - ' . esc_html( $report['location_name'] );
                echo '</a>';
                echo '<br><small>' . esc_html( $report['submitted_at'] ) . '</small>';
                echo '</li>';
            }
            echo '</ul>';

            echo '<p><a href="' . esc_url( admin_url( 'edit.php?post_type=sighting_report&status=pending' ) ) . '" class="button">';
            echo __( 'View All Pending Reports', 'hilal' );
            echo '</a></p>';
        } else {
            echo '<p style="color: #00a32a;">';
            echo '<span class="dashicons dashicons-yes-alt"></span> ';
            echo __( 'No pending reports!', 'hilal' );
            echo '</p>';
        }

        echo '</div>';
    }

    /**
     * Render current month widget
     */
    public function render_current_month_widget() {
        $current = Hilal_Hijri_Date::get_current_month_info();

        echo '<div class="hilal-widget hilal-month-widget">';

        echo '<div class="hilal-hijri-date">';
        echo '<span class="hilal-day">' . esc_html( $current['day'] ) . '</span>';
        echo '<span class="hilal-month">' . esc_html( $current['month_name_en'] ) . '</span>';
        echo '<span class="hilal-month-ar">' . esc_html( $current['month_name_ar'] ) . '</span>';
        echo '<span class="hilal-year">' . esc_html( $current['year'] ) . ' AH</span>';
        echo '</div>';

        echo '<div class="hilal-status">';
        echo '<strong>' . __( 'Status:', 'hilal' ) . '</strong> ';

        $status_labels = array(
            'confirmed'        => '<span style="color: #00a32a;">' . __( 'Confirmed by Sighting', 'hilal' ) . '</span>',
            'pending_sighting' => '<span style="color: #dba617;">' . __( 'Pending Sighting', 'hilal' ) . '</span>',
            'estimated'        => '<span style="color: #72aee6;">' . __( 'Estimated (Calculated)', 'hilal' ) . '</span>',
        );

        echo $status_labels[ $current['status'] ] ?? $current['status'];
        echo '</div>';

        if ( 'calculated' === $current['source'] ) {
            echo '<p class="hilal-note">';
            echo '<em>' . __( 'Note: Using calculated date. Create Hijri months in the calendar to use confirmed dates.', 'hilal' ) . '</em>';
            echo '</p>';
        }

        echo '<p>';
        echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=hijri_month' ) ) . '" class="button">';
        echo __( 'Manage Calendar', 'hilal' );
        echo '</a>';
        echo '</p>';

        echo '</div>';
    }

    /**
     * Render statistics widget
     */
    public function render_stats_widget() {
        global $wpdb;

        // Count posts
        $announcements = wp_count_posts( 'announcement' );
        $events        = wp_count_posts( 'islamic_event' );
        $months        = wp_count_posts( 'hijri_month' );
        $reports       = wp_count_posts( 'sighting_report' );

        // Count subscribers
        $subscribers = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}hilal_subscribers WHERE unsubscribed_at IS NULL" );
        $device_tokens = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}hilal_device_tokens" );

        echo '<div class="hilal-widget hilal-stats-widget">';

        echo '<div class="hilal-stats-grid">';

        echo '<div class="hilal-stat">';
        echo '<span class="hilal-stat-number">' . esc_html( $announcements->publish ?? 0 ) . '</span>';
        echo '<span class="hilal-stat-label">' . __( 'Announcements', 'hilal' ) . '</span>';
        echo '</div>';

        echo '<div class="hilal-stat">';
        echo '<span class="hilal-stat-number">' . esc_html( $months->publish ?? 0 ) . '</span>';
        echo '<span class="hilal-stat-label">' . __( 'Hijri Months', 'hilal' ) . '</span>';
        echo '</div>';

        echo '<div class="hilal-stat">';
        echo '<span class="hilal-stat-number">' . esc_html( $events->publish ?? 0 ) . '</span>';
        echo '<span class="hilal-stat-label">' . __( 'Islamic Events', 'hilal' ) . '</span>';
        echo '</div>';

        echo '<div class="hilal-stat">';
        echo '<span class="hilal-stat-number">' . esc_html( $reports->publish ?? 0 ) . '</span>';
        echo '<span class="hilal-stat-label">' . __( 'Sighting Reports', 'hilal' ) . '</span>';
        echo '</div>';

        echo '<div class="hilal-stat">';
        echo '<span class="hilal-stat-number">' . esc_html( $subscribers ?? 0 ) . '</span>';
        echo '<span class="hilal-stat-label">' . __( 'Email Subscribers', 'hilal' ) . '</span>';
        echo '</div>';

        echo '<div class="hilal-stat">';
        echo '<span class="hilal-stat-number">' . esc_html( $device_tokens ?? 0 ) . '</span>';
        echo '<span class="hilal-stat-label">' . __( 'Mobile Devices', 'hilal' ) . '</span>';
        echo '</div>';

        echo '</div>';
        echo '</div>';
    }

    /**
     * Add admin menu pages
     */
    public function add_admin_menu_pages() {
        // Hilal Settings Page
        add_menu_page(
            __( 'Hilal Settings', 'hilal' ),
            __( 'Hilal', 'hilal' ),
            'manage_options',
            'hilal-settings',
            array( $this, 'render_settings_page' ),
            'dashicons-moon',
            3
        );

        // Subscribers submenu
        add_submenu_page(
            'hilal-settings',
            __( 'Subscribers', 'hilal' ),
            __( 'Subscribers', 'hilal' ),
            'manage_options',
            'hilal-subscribers',
            array( $this, 'render_subscribers_page' )
        );

        // Initialize Year submenu
        add_submenu_page(
            'hilal-settings',
            __( 'Initialize Year', 'hilal' ),
            __( 'Initialize Year', 'hilal' ),
            'manage_options',
            'hilal-init-year',
            array( $this, 'render_init_year_page' )
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Save settings
        if ( isset( $_POST['hilal_save_settings'] ) && check_admin_referer( 'hilal_settings_nonce' ) ) {
            update_option( 'hilal_prayer_method', sanitize_text_field( $_POST['hilal_prayer_method'] ?? 'mwl' ) );
            update_option( 'hilal_fcm_server_key', sanitize_text_field( $_POST['hilal_fcm_server_key'] ?? '' ) );
            update_option( 'hilal_email_from_name', sanitize_text_field( $_POST['hilal_email_from_name'] ?? '' ) );
            update_option( 'hilal_email_from_address', sanitize_email( $_POST['hilal_email_from_address'] ?? '' ) );

            echo '<div class="notice notice-success"><p>' . __( 'Settings saved.', 'hilal' ) . '</p></div>';
        }

        $prayer_method     = get_option( 'hilal_prayer_method', 'mwl' );
        $fcm_server_key    = get_option( 'hilal_fcm_server_key', '' );
        $email_from_name   = get_option( 'hilal_email_from_name', get_bloginfo( 'name' ) );
        $email_from_address = get_option( 'hilal_email_from_address', get_option( 'admin_email' ) );

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <form method="post" action="">
                <?php wp_nonce_field( 'hilal_settings_nonce' ); ?>

                <h2><?php _e( 'Prayer Times Settings', 'hilal' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="hilal_prayer_method"><?php _e( 'Default Calculation Method', 'hilal' ); ?></label>
                        </th>
                        <td>
                            <select name="hilal_prayer_method" id="hilal_prayer_method">
                                <?php foreach ( Hilal_Prayer_Calculator::get_methods() as $id => $name ) : ?>
                                    <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $prayer_method, $id ); ?>>
                                        <?php echo esc_html( $name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2><?php _e( 'Push Notification Settings', 'hilal' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="hilal_fcm_server_key"><?php _e( 'Firebase Server Key', 'hilal' ); ?></label>
                        </th>
                        <td>
                            <input type="password" name="hilal_fcm_server_key" id="hilal_fcm_server_key"
                                   value="<?php echo esc_attr( $fcm_server_key ); ?>" class="regular-text">
                            <p class="description">
                                <?php _e( 'Get this from Firebase Console > Project Settings > Cloud Messaging.', 'hilal' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <h2><?php _e( 'Email Settings', 'hilal' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="hilal_email_from_name"><?php _e( 'From Name', 'hilal' ); ?></label>
                        </th>
                        <td>
                            <input type="text" name="hilal_email_from_name" id="hilal_email_from_name"
                                   value="<?php echo esc_attr( $email_from_name ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="hilal_email_from_address"><?php _e( 'From Email', 'hilal' ); ?></label>
                        </th>
                        <td>
                            <input type="email" name="hilal_email_from_address" id="hilal_email_from_address"
                                   value="<?php echo esc_attr( $email_from_address ); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="hilal_save_settings" class="button-primary"
                           value="<?php _e( 'Save Settings', 'hilal' ); ?>">
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Render subscribers page
     */
    public function render_subscribers_page() {
        global $wpdb;

        $table_name  = $wpdb->prefix . 'hilal_subscribers';
        $subscribers = $wpdb->get_results( "SELECT * FROM $table_name WHERE unsubscribed_at IS NULL ORDER BY created_at DESC" );

        ?>
        <div class="wrap">
            <h1><?php _e( 'Email Subscribers', 'hilal' ); ?></h1>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e( 'Email', 'hilal' ); ?></th>
                        <th><?php _e( 'Name', 'hilal' ); ?></th>
                        <th><?php _e( 'Channels', 'hilal' ); ?></th>
                        <th><?php _e( 'Verified', 'hilal' ); ?></th>
                        <th><?php _e( 'Subscribed', 'hilal' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $subscribers ) ) : ?>
                        <tr>
                            <td colspan="5"><?php _e( 'No subscribers yet.', 'hilal' ); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $subscribers as $sub ) : ?>
                            <tr>
                                <td><?php echo esc_html( $sub->email ); ?></td>
                                <td><?php echo esc_html( $sub->name ); ?></td>
                                <td><?php echo esc_html( $sub->channels ); ?></td>
                                <td><?php echo $sub->verified ? '✓' : '✗'; ?></td>
                                <td><?php echo esc_html( $sub->created_at ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render initialize year page
     */
    public function render_init_year_page() {
        // Handle form submission
        if ( isset( $_POST['hilal_init_year'] ) && check_admin_referer( 'hilal_init_year_nonce' ) ) {
            $year = (int) $_POST['hijri_year'];

            if ( $year >= 1400 && $year <= 1500 ) {
                $created = Hilal_Hijri_Month::create_year_months( $year );

                if ( ! empty( $created ) ) {
                    echo '<div class="notice notice-success"><p>';
                    printf(
                        __( 'Created %d months for year %d AH.', 'hilal' ),
                        count( $created ),
                        $year
                    );
                    echo '</p></div>';
                } else {
                    echo '<div class="notice notice-info"><p>';
                    printf( __( 'All months for year %d AH already exist.', 'hilal' ), $year );
                    echo '</p></div>';
                }
            }
        }

        // Handle events creation
        if ( isset( $_POST['hilal_create_events'] ) && check_admin_referer( 'hilal_init_year_nonce' ) ) {
            $created = Hilal_Islamic_Event::create_default_events();

            if ( ! empty( $created ) ) {
                echo '<div class="notice notice-success"><p>';
                printf( __( 'Created %d Islamic events.', 'hilal' ), count( $created ) );
                echo '</p></div>';
            } else {
                echo '<div class="notice notice-info"><p>';
                echo __( 'All default events already exist.', 'hilal' );
                echo '</p></div>';
            }
        }

        $current_hijri = Hilal_Hijri_Date::get_today_hijri();

        ?>
        <div class="wrap">
            <h1><?php _e( 'Initialize Hijri Calendar', 'hilal' ); ?></h1>

            <div class="card">
                <h2><?php _e( 'Create Hijri Year', 'hilal' ); ?></h2>
                <p><?php _e( 'Create all 12 months for a Hijri year with estimated dates. You can then update individual months when sightings are confirmed.', 'hilal' ); ?></p>

                <form method="post" action="">
                    <?php wp_nonce_field( 'hilal_init_year_nonce' ); ?>

                    <p>
                        <label for="hijri_year"><?php _e( 'Hijri Year:', 'hilal' ); ?></label>
                        <input type="number" name="hijri_year" id="hijri_year"
                               value="<?php echo esc_attr( $current_hijri['year'] ); ?>"
                               min="1400" max="1500" required>
                    </p>

                    <p>
                        <input type="submit" name="hilal_init_year" class="button-primary"
                               value="<?php _e( 'Create Year Months', 'hilal' ); ?>">
                    </p>
                </form>
            </div>

            <div class="card" style="margin-top: 20px;">
                <h2><?php _e( 'Create Default Islamic Events', 'hilal' ); ?></h2>
                <p><?php _e( 'Create standard Islamic events (Eid al-Fitr, Eid al-Adha, Ramadan, etc.) that recur every year.', 'hilal' ); ?></p>

                <form method="post" action="">
                    <?php wp_nonce_field( 'hilal_init_year_nonce' ); ?>

                    <p>
                        <input type="submit" name="hilal_create_events" class="button-secondary"
                               value="<?php _e( 'Create Default Events', 'hilal' ); ?>">
                    </p>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page.
     */
    public function enqueue_admin_assets( $hook ) {
        // Only on dashboard and Hilal pages
        if ( 'index.php' !== $hook && strpos( $hook, 'hilal' ) === false ) {
            return;
        }

        wp_add_inline_style( 'common', '
            .hilal-widget { padding: 10px 0; }
            .hilal-alert { background: #fcf0f1; padding: 10px; border-left: 4px solid #d63638; margin: 0 0 15px 0; }
            .hilal-report-list { margin: 0; padding: 0; list-style: none; }
            .hilal-report-list li { padding: 8px 0; border-bottom: 1px solid #eee; }
            .hilal-report-list li:last-child { border-bottom: none; }
            .hilal-month-widget .hilal-hijri-date { text-align: center; padding: 15px; background: #f0f6fc; border-radius: 5px; margin-bottom: 15px; }
            .hilal-month-widget .hilal-day { display: block; font-size: 48px; font-weight: bold; color: #2271b1; }
            .hilal-month-widget .hilal-month { display: block; font-size: 18px; color: #1d2327; }
            .hilal-month-widget .hilal-month-ar { display: block; font-size: 16px; color: #50575e; direction: rtl; }
            .hilal-month-widget .hilal-year { display: block; font-size: 14px; color: #787c82; }
            .hilal-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
            .hilal-stat { text-align: center; padding: 15px; background: #f0f6fc; border-radius: 5px; }
            .hilal-stat-number { display: block; font-size: 24px; font-weight: bold; color: #2271b1; }
            .hilal-stat-label { display: block; font-size: 12px; color: #50575e; }
            .hilal-note { background: #fcf9e8; padding: 10px; border-left: 4px solid #dba617; }
        ' );
    }
}
