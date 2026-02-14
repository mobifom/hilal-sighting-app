<?php
/**
 * Push Notifications Handler
 *
 * Handles sending push notifications via Expo Push API
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Push Notifications Class
 */
class Hilal_Push_Notifications {

    /**
     * Expo Push API URL
     */
    const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Table name for device tokens
     */
    private $table_name;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'hilal_device_tokens';

        // Create table on plugin activation
        register_activation_hook( HILAL_PLUGIN_FILE, array( $this, 'create_table' ) );

        // Hook into announcement publish
        add_action( 'publish_announcement', array( $this, 'on_announcement_publish' ), 10, 2 );

        // Register REST API endpoint
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Create device tokens table
     */
    public function create_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            token varchar(255) NOT NULL,
            platform varchar(10) NOT NULL DEFAULT 'ios',
            user_id bigint(20) unsigned DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY token (token),
            KEY platform (platform),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route(
            'hilal/v1',
            '/device-token',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'register_device_token' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'token'    => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'platform' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'enum'              => array( 'ios', 'android' ),
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );

        register_rest_route(
            'hilal/v1',
            '/device-token',
            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'unregister_device_token' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'token' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );

        // Admin endpoint to send notifications
        register_rest_route(
            'hilal/v1',
            '/admin/send-notification',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'admin_send_notification' ),
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
                'args'                => array(
                    'title'   => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'body'    => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ),
                    'type'    => array(
                        'type'              => 'string',
                        'default'           => 'announcement',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'data'    => array(
                        'type'    => 'object',
                        'default' => array(),
                    ),
                ),
            )
        );
    }

    /**
     * Register a device token
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function register_device_token( $request ) {
        global $wpdb;

        $token    = $request->get_param( 'token' );
        $platform = $request->get_param( 'platform' );
        $user_id  = get_current_user_id() ?: null;

        // Ensure table exists
        $this->create_table();

        // Check if token already exists
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE token = %s",
                $token
            )
        );

        if ( $existing ) {
            $wpdb->update(
                $this->table_name,
                array(
                    'platform'   => $platform,
                    'user_id'    => $user_id,
                    'updated_at' => current_time( 'mysql' ),
                ),
                array( 'token' => $token ),
                array( '%s', '%d', '%s' ),
                array( '%s' )
            );
        } else {
            $wpdb->insert(
                $this->table_name,
                array(
                    'token'      => $token,
                    'platform'   => $platform,
                    'user_id'    => $user_id,
                    'created_at' => current_time( 'mysql' ),
                    'updated_at' => current_time( 'mysql' ),
                ),
                array( '%s', '%s', '%d', '%s', '%s' )
            );
        }

        return new WP_REST_Response(
            array(
                'success' => true,
                'message' => __( 'Device token registered successfully.', 'hilal' ),
            ),
            200
        );
    }

    /**
     * Unregister a device token
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function unregister_device_token( $request ) {
        global $wpdb;

        $token = $request->get_param( 'token' );

        $wpdb->delete(
            $this->table_name,
            array( 'token' => $token ),
            array( '%s' )
        );

        return new WP_REST_Response(
            array(
                'success' => true,
                'message' => __( 'Device token unregistered.', 'hilal' ),
            ),
            200
        );
    }

    /**
     * Handle announcement publish
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     */
    public function on_announcement_publish( $post_id, $post ) {
        $send_notification = get_post_meta( $post_id, '_send_notification', true );
        $notification_sent = get_post_meta( $post_id, '_notification_sent', true );

        if ( ! $send_notification || $notification_sent ) {
            return;
        }

        $title_en = get_field( 'title_en', $post_id ) ?: $post->post_title;
        $title_ar = get_field( 'title_ar', $post_id ) ?: $title_en;
        $body_en  = get_field( 'body_en', $post_id );
        $body_ar  = get_field( 'body_ar', $post_id );
        $type     = get_field( 'type', $post_id ) ?: 'announcement';

        $body_en_clean = wp_trim_words( wp_strip_all_tags( $body_en ), 20, '...' );
        $body_ar_clean = wp_trim_words( wp_strip_all_tags( $body_ar ), 20, '...' );

        $notification_type = $this->get_notification_type( $type );

        $result = $this->send_to_all(
            $title_en,
            $body_en_clean,
            $notification_type,
            array(
                'announcement_id' => $post_id,
                'title_ar'        => $title_ar,
                'body_ar'         => $body_ar_clean,
            )
        );

        if ( $result['success'] > 0 ) {
            update_post_meta( $post_id, '_notification_sent', true );
            update_post_meta( $post_id, '_notification_sent_at', current_time( 'mysql' ) );
            update_post_meta( $post_id, '_notification_recipients', $result['success'] );
        }
    }

    /**
     * Map announcement type to notification type
     *
     * @param string $type Announcement type.
     * @return string
     */
    private function get_notification_type( $type ) {
        $map = array(
            'month_start'   => 'month_start',
            'moon_sighting' => 'moon_sighting',
            'islamic_event' => 'islamic_event',
            'general'       => 'announcement',
        );
        return $map[ $type ] ?? 'announcement';
    }

    /**
     * Send notification to all registered devices
     *
     * @param string $title English title.
     * @param string $body  English body.
     * @param string $type  Notification type.
     * @param array  $data  Additional data.
     * @return array Result with success/failure counts.
     */
    public function send_to_all( $title, $body, $type = 'announcement', $data = array() ) {
        global $wpdb;

        $tokens = $wpdb->get_col( "SELECT token FROM {$this->table_name}" );

        if ( empty( $tokens ) ) {
            return array( 'success' => 0, 'failed' => 0 );
        }

        return $this->send_notifications( $tokens, $title, $body, $type, $data );
    }

    /**
     * Send notifications to specific tokens
     */
    public function send_notifications( $tokens, $title, $body, $type = 'announcement', $data = array() ) {
        if ( empty( $tokens ) ) {
            return array( 'success' => 0, 'failed' => 0 );
        }

        $messages = array();
        foreach ( $tokens as $token ) {
            if ( ! $this->is_valid_expo_token( $token ) ) {
                continue;
            }

            $messages[] = array(
                'to'        => $token,
                'title'     => $title,
                'body'      => $body,
                'sound'     => 'default',
                'priority'  => 'high',
                'data'      => array_merge( array( 'type' => $type ), $data ),
                'channelId' => $this->get_channel_id( $type ),
            );
        }

        if ( empty( $messages ) ) {
            return array( 'success' => 0, 'failed' => 0 );
        }

        $batch_size = 100;
        $batches    = array_chunk( $messages, $batch_size );
        $success    = 0;
        $failed     = 0;

        foreach ( $batches as $batch ) {
            $response = wp_remote_post(
                self::EXPO_PUSH_URL,
                array(
                    'timeout' => 30,
                    'headers' => array(
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/json',
                    ),
                    'body'    => wp_json_encode( $batch ),
                )
            );

            if ( is_wp_error( $response ) ) {
                $failed += count( $batch );
                error_log( 'Expo Push Error: ' . $response->get_error_message() );
                continue;
            }

            $response_body = json_decode( wp_remote_retrieve_body( $response ), true );

            if ( isset( $response_body['data'] ) && is_array( $response_body['data'] ) ) {
                foreach ( $response_body['data'] as $index => $result ) {
                    if ( 'ok' === $result['status'] ) {
                        $success++;
                    } else {
                        $failed++;
                        if ( isset( $result['details']['error'] ) &&
                             in_array( $result['details']['error'], array( 'DeviceNotRegistered', 'InvalidCredentials' ), true ) ) {
                            $this->remove_invalid_token( $batch[ $index ]['to'] );
                        }
                    }
                }
            }
        }

        return array( 'success' => $success, 'failed' => $failed );
    }

    private function is_valid_expo_token( $token ) {
        return preg_match( '/^ExponentPushToken\[.+\]$/', $token ) ||
               preg_match( '/^[a-zA-Z0-9-_]+$/', $token );
    }

    private function get_channel_id( $type ) {
        $channels = array(
            'announcement'    => 'announcements',
            'moon_sighting'   => 'moon_sighting',
            'month_start'     => 'announcements',
            'islamic_event'   => 'islamic_events',
            'prayer_reminder' => 'prayer_reminder',
        );
        return $channels[ $type ] ?? 'announcements';
    }

    private function remove_invalid_token( $token ) {
        global $wpdb;
        $wpdb->delete( $this->table_name, array( 'token' => $token ), array( '%s' ) );
    }

    public function admin_send_notification( $request ) {
        $title = $request->get_param( 'title' );
        $body  = $request->get_param( 'body' );
        $type  = $request->get_param( 'type' );
        $data  = $request->get_param( 'data' );

        $result = $this->send_to_all( $title, $body, $type, $data );

        return new WP_REST_Response(
            array(
                'success' => true,
                'message' => sprintf(
                    __( 'Notification sent to %d devices. %d failed.', 'hilal' ),
                    $result['success'],
                    $result['failed']
                ),
                'result'  => $result,
            ),
            200
        );
    }

    public function get_device_count() {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name}" );
    }

    public function get_device_count_by_platform() {
        global $wpdb;
        $results = $wpdb->get_results(
            "SELECT platform, COUNT(*) as count FROM {$this->table_name} GROUP BY platform",
            ARRAY_A
        );
        $counts = array( 'ios' => 0, 'android' => 0 );
        foreach ( $results as $row ) {
            $counts[ $row['platform'] ] = (int) $row['count'];
        }
        return $counts;
    }
}

// Initialize
new Hilal_Push_Notifications();
