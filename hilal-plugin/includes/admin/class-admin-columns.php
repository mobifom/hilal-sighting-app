<?php
/**
 * Admin Custom Columns
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Admin Columns Class
 */
class Hilal_Admin_Columns {

    /**
     * Constructor
     */
    public function __construct() {
        // Sighting Report columns
        add_filter( 'manage_sighting_report_posts_columns', array( $this, 'sighting_report_columns' ) );
        add_action( 'manage_sighting_report_posts_custom_column', array( $this, 'sighting_report_column_content' ), 10, 2 );
        add_filter( 'manage_edit-sighting_report_sortable_columns', array( $this, 'sighting_report_sortable' ) );

        // Announcement columns
        add_filter( 'manage_announcement_posts_columns', array( $this, 'announcement_columns' ) );
        add_action( 'manage_announcement_posts_custom_column', array( $this, 'announcement_column_content' ), 10, 2 );

        // Hijri Month columns
        add_filter( 'manage_hijri_month_posts_columns', array( $this, 'hijri_month_columns' ) );
        add_action( 'manage_hijri_month_posts_custom_column', array( $this, 'hijri_month_column_content' ), 10, 2 );
        add_filter( 'manage_edit-hijri_month_sortable_columns', array( $this, 'hijri_month_sortable' ) );

        // Islamic Event columns
        add_filter( 'manage_islamic_event_posts_columns', array( $this, 'islamic_event_columns' ) );
        add_action( 'manage_islamic_event_posts_custom_column', array( $this, 'islamic_event_column_content' ), 10, 2 );

        // Row actions
        add_filter( 'post_row_actions', array( $this, 'sighting_report_row_actions' ), 10, 2 );

        // Quick edit actions
        add_action( 'admin_action_hilal_approve_report', array( $this, 'handle_approve_report' ) );
        add_action( 'admin_action_hilal_reject_report', array( $this, 'handle_reject_report' ) );
    }

    /**
     * Sighting Report columns
     *
     * @param array $columns Existing columns.
     * @return array
     */
    public function sighting_report_columns( $columns ) {
        $new_columns = array(
            'cb'          => $columns['cb'],
            'title'       => __( 'Report', 'hilal' ),
            'observer'    => __( 'Observer', 'hilal' ),
            'location'    => __( 'Location', 'hilal' ),
            'hijri_month' => __( 'Hijri Month', 'hilal' ),
            'photo'       => __( 'Photo', 'hilal' ),
            'status'      => __( 'Status', 'hilal' ),
            'date'        => __( 'Date', 'hilal' ),
        );

        return $new_columns;
    }

    /**
     * Sighting Report column content
     *
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     */
    public function sighting_report_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'observer':
                $name  = get_field( 'observer_name', $post_id );
                $email = '';
                $user_id = get_field( 'observer_user_id', $post_id );
                if ( $user_id ) {
                    $user = get_userdata( $user_id );
                    $email = $user ? $user->user_email : '';
                }
                echo '<strong>' . esc_html( $name ) . '</strong>';
                if ( $email ) {
                    echo '<br><small>' . esc_html( $email ) . '</small>';
                }
                break;

            case 'location':
                $name = get_field( 'location_name', $post_id );
                $lat  = get_field( 'location_lat', $post_id );
                $lng  = get_field( 'location_lng', $post_id );

                echo esc_html( $name );
                if ( $lat && $lng ) {
                    $map_url = sprintf(
                        'https://www.google.com/maps?q=%s,%s',
                        $lat,
                        $lng
                    );
                    echo '<br><a href="' . esc_url( $map_url ) . '" target="_blank" class="button-link">';
                    echo '<small>' . __( 'View on Map', 'hilal' ) . '</small>';
                    echo '</a>';
                }
                break;

            case 'hijri_month':
                $month_id = get_field( 'hijri_month_id', $post_id );
                if ( $month_id ) {
                    $month_name = get_field( 'month_name_en', $month_id );
                    $year       = get_field( 'hijri_year', $month_id );
                    echo esc_html( $month_name . ' ' . $year . ' AH' );
                } else {
                    echo '—';
                }
                break;

            case 'photo':
                $photo = get_field( 'photo', $post_id );
                if ( $photo && isset( $photo['sizes']['thumbnail'] ) ) {
                    echo '<a href="' . esc_url( $photo['url'] ) . '" target="_blank">';
                    echo '<img src="' . esc_url( $photo['sizes']['thumbnail'] ) . '" width="50" height="50" style="object-fit: cover;">';
                    echo '</a>';
                } else {
                    echo '—';
                }
                break;

            case 'status':
                $status = get_field( 'status', $post_id );
                $colors = array(
                    'pending'  => '#dba617',
                    'approved' => '#00a32a',
                    'rejected' => '#d63638',
                );
                $labels = array(
                    'pending'  => __( 'Pending', 'hilal' ),
                    'approved' => __( 'Approved', 'hilal' ),
                    'rejected' => __( 'Rejected', 'hilal' ),
                );

                $color = $colors[ $status ] ?? '#787c82';
                $label = $labels[ $status ] ?? $status;

                echo '<span style="color: ' . esc_attr( $color ) . '; font-weight: bold;">';
                echo esc_html( $label );
                echo '</span>';
                break;
        }
    }

    /**
     * Sighting Report sortable columns
     *
     * @param array $columns Sortable columns.
     * @return array
     */
    public function sighting_report_sortable( $columns ) {
        $columns['status'] = 'status';
        return $columns;
    }

    /**
     * Sighting Report row actions
     *
     * @param array   $actions Row actions.
     * @param WP_Post $post    Post object.
     * @return array
     */
    public function sighting_report_row_actions( $actions, $post ) {
        if ( 'sighting_report' !== $post->post_type ) {
            return $actions;
        }

        $status = get_field( 'status', $post->ID );

        if ( 'pending' === $status ) {
            $approve_url = wp_nonce_url(
                admin_url( 'admin.php?action=hilal_approve_report&post=' . $post->ID ),
                'hilal_approve_report_' . $post->ID
            );
            $reject_url = wp_nonce_url(
                admin_url( 'admin.php?action=hilal_reject_report&post=' . $post->ID ),
                'hilal_reject_report_' . $post->ID
            );

            $actions['approve'] = '<a href="' . esc_url( $approve_url ) . '" style="color: #00a32a;">' . __( 'Approve', 'hilal' ) . '</a>';
            $actions['reject']  = '<a href="' . esc_url( $reject_url ) . '" style="color: #d63638;">' . __( 'Reject', 'hilal' ) . '</a>';
        }

        return $actions;
    }

    /**
     * Handle approve report action
     */
    public function handle_approve_report() {
        $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

        // Security checks: nonce verification and capability check
        if ( ! $post_id ) {
            wp_die( __( 'Invalid post ID.', 'hilal' ) );
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'hilal_approve_report_' . $post_id ) ) {
            wp_die( __( 'Security check failed.', 'hilal' ) );
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            wp_die( __( 'You do not have permission to perform this action.', 'hilal' ) );
        }

        Hilal_Sighting_Report::update_status( $post_id, 'approved', '', get_current_user_id() );

        wp_safe_redirect( admin_url( 'edit.php?post_type=sighting_report&approved=1' ) );
        exit;
    }

    /**
     * Handle reject report action
     */
    public function handle_reject_report() {
        $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

        // Security checks: nonce verification and capability check
        if ( ! $post_id ) {
            wp_die( __( 'Invalid post ID.', 'hilal' ) );
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'hilal_reject_report_' . $post_id ) ) {
            wp_die( __( 'Security check failed.', 'hilal' ) );
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            wp_die( __( 'You do not have permission to perform this action.', 'hilal' ) );
        }

        Hilal_Sighting_Report::update_status( $post_id, 'rejected', '', get_current_user_id() );

        wp_safe_redirect( admin_url( 'edit.php?post_type=sighting_report&rejected=1' ) );
        exit;
    }

    /**
     * Announcement columns
     *
     * @param array $columns Existing columns.
     * @return array
     */
    public function announcement_columns( $columns ) {
        $new_columns = array(
            'cb'                 => $columns['cb'],
            'title'              => __( 'Title', 'hilal' ),
            'type'               => __( 'Type', 'hilal' ),
            'priority'           => __( 'Priority', 'hilal' ),
            'notification_sent'  => __( 'Notification', 'hilal' ),
            'date'               => __( 'Date', 'hilal' ),
        );

        return $new_columns;
    }

    /**
     * Announcement column content
     *
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     */
    public function announcement_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'type':
                $type   = get_field( 'type', $post_id );
                $labels = array(
                    'month_start'   => __( 'Month Start', 'hilal' ),
                    'moon_sighting' => __( 'Moon Sighting', 'hilal' ),
                    'islamic_event' => __( 'Islamic Event', 'hilal' ),
                    'general'       => __( 'General', 'hilal' ),
                );
                $colors = array(
                    'month_start'   => '#2271b1',
                    'moon_sighting' => '#dba617',
                    'islamic_event' => '#00a32a',
                    'general'       => '#787c82',
                );

                $label = $labels[ $type ] ?? $type;
                $color = $colors[ $type ] ?? '#787c82';

                echo '<span style="background: ' . esc_attr( $color ) . '; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 12px;">';
                echo esc_html( $label );
                echo '</span>';
                break;

            case 'priority':
                $priority = get_field( 'priority', $post_id );
                $labels   = array(
                    'high'   => __( 'High', 'hilal' ),
                    'medium' => __( 'Medium', 'hilal' ),
                    'low'    => __( 'Low', 'hilal' ),
                );
                $icons = array(
                    'high'   => '🔴',
                    'medium' => '🟡',
                    'low'    => '🟢',
                );

                echo ( $icons[ $priority ] ?? '' ) . ' ' . esc_html( $labels[ $priority ] ?? $priority );
                break;

            case 'notification_sent':
                $sent = get_field( 'notification_sent', $post_id );
                if ( $sent ) {
                    echo '<span style="color: #00a32a;">✓ ' . __( 'Sent', 'hilal' ) . '</span>';
                } else {
                    echo '<span style="color: #787c82;">—</span>';
                }
                break;
        }
    }

    /**
     * Hijri Month columns
     *
     * @param array $columns Existing columns.
     * @return array
     */
    public function hijri_month_columns( $columns ) {
        $new_columns = array(
            'cb'              => $columns['cb'],
            'title'           => __( 'Month', 'hilal' ),
            'month_number'    => __( '#', 'hilal' ),
            'hijri_year'      => __( 'Year', 'hilal' ),
            'gregorian_start' => __( 'Gregorian Start', 'hilal' ),
            'days_count'      => __( 'Days', 'hilal' ),
            'status'          => __( 'Status', 'hilal' ),
        );

        return $new_columns;
    }

    /**
     * Hijri Month column content
     *
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     */
    public function hijri_month_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'month_number':
                echo esc_html( get_field( 'month_number', $post_id ) );
                break;

            case 'hijri_year':
                echo esc_html( get_field( 'hijri_year', $post_id ) . ' AH' );
                break;

            case 'gregorian_start':
                $date = get_field( 'gregorian_start', $post_id );
                if ( $date ) {
                    echo esc_html( gmdate( 'j M Y', strtotime( $date ) ) );
                } else {
                    echo '—';
                }
                break;

            case 'days_count':
                $days = get_field( 'days_count', $post_id );
                echo $days ? esc_html( $days ) : '—';
                break;

            case 'status':
                $status = get_field( 'status', $post_id );
                $labels = array(
                    'estimated'        => __( 'Estimated', 'hilal' ),
                    'pending_sighting' => __( 'Pending Sighting', 'hilal' ),
                    'confirmed'        => __( 'Confirmed', 'hilal' ),
                );
                $colors = array(
                    'estimated'        => '#72aee6',
                    'pending_sighting' => '#dba617',
                    'confirmed'        => '#00a32a',
                );

                $label = $labels[ $status ] ?? $status;
                $color = $colors[ $status ] ?? '#787c82';

                echo '<span style="color: ' . esc_attr( $color ) . '; font-weight: bold;">';
                echo esc_html( $label );
                echo '</span>';
                break;
        }
    }

    /**
     * Hijri Month sortable columns
     *
     * @param array $columns Sortable columns.
     * @return array
     */
    public function hijri_month_sortable( $columns ) {
        $columns['month_number'] = 'month_number';
        $columns['hijri_year']   = 'hijri_year';
        return $columns;
    }

    /**
     * Islamic Event columns
     *
     * @param array $columns Existing columns.
     * @return array
     */
    public function islamic_event_columns( $columns ) {
        $new_columns = array(
            'cb'          => $columns['cb'],
            'title'       => __( 'Event', 'hilal' ),
            'hijri_date'  => __( 'Hijri Date', 'hilal' ),
            'category'    => __( 'Category', 'hilal' ),
            'recurring'   => __( 'Recurring', 'hilal' ),
        );

        return $new_columns;
    }

    /**
     * Islamic Event column content
     *
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     */
    public function islamic_event_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'hijri_date':
                $day   = get_field( 'hijri_day', $post_id );
                $month = get_field( 'hijri_month', $post_id );

                $month_name = Hilal_Hijri_Date::get_month_name( $month, 'en' );
                echo esc_html( $day . ' ' . $month_name );
                break;

            case 'category':
                $category = get_field( 'event_category', $post_id );
                $labels   = array(
                    'eid'           => __( 'Eid', 'hilal' ),
                    'fasting'       => __( 'Fasting', 'hilal' ),
                    'hajj'          => __( 'Hajj', 'hilal' ),
                    'special_night' => __( 'Special Night', 'hilal' ),
                    'historical'    => __( 'Historical', 'hilal' ),
                    'other'         => __( 'Other', 'hilal' ),
                );

                echo esc_html( $labels[ $category ] ?? $category );
                break;

            case 'recurring':
                $recurring = get_field( 'is_recurring', $post_id );
                echo $recurring ? '✓' : '—';
                break;
        }
    }
}
