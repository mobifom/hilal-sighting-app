<?php
/**
 * Crescent Sighting REST API
 *
 * Simplified API for PDF attachment and details only.
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Sighting API Class
 */
class Hilal_Sighting_API extends Hilal_API_Base {

    /**
     * Register routes
     */
    public function register_routes() {
        // POST /hilal/v1/sighting-report (public)
        register_rest_route(
            $this->namespace,
            '/sighting-report',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'submit_sighting' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'details'       => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ),
                    'attachment_id' => array(
                        'type'              => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );

        // POST /hilal/v1/sighting/upload-attachment (public - for PDF upload)
        register_rest_route(
            $this->namespace,
            '/sighting/upload-attachment',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'upload_attachment' ),
                'permission_callback' => '__return_true',
            )
        );

        // GET /hilal/v1/sightings (public - approved only)
        register_rest_route(
            $this->namespace,
            '/sightings',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_approved_sightings' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'limit' => array(
                        'default'           => 20,
                        'type'              => 'integer',
                        'minimum'           => 1,
                        'maximum'           => 50,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );

        // GET /hilal/v1/sightings/approved (alias for mobile app)
        register_rest_route(
            $this->namespace,
            '/sightings/approved',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_approved_sightings' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'limit' => array(
                        'default'           => 20,
                        'type'              => 'integer',
                        'minimum'           => 1,
                        'maximum'           => 50,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );
    }

    /**
     * Submit a crescent sighting
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function submit_sighting( $request ) {
        $details = $request->get_param( 'details' );

        if ( empty( trim( $details ) ) ) {
            return $this->error(
                'missing_details',
                __( 'Please provide details about the sighting.', 'hilal' ),
                400
            );
        }

        // Rate limiting: Max 5 submissions per IP per day
        $ip_address = $this->get_client_ip();
        $today_count = $this->get_ip_submissions_today( $ip_address );
        if ( $today_count >= 5 ) {
            return $this->error(
                'rate_limited',
                __( 'You have reached the maximum number of submissions for today.', 'hilal' ),
                429
            );
        }

        // Prepare data
        $data = array(
            'details'       => $details,
            'attachment_id' => $request->get_param( 'attachment_id' ) ?: null,
        );

        $result = Hilal_Sighting_Report::create_report( $data );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return $this->success(
            array(
                'id'      => $result,
                'message' => __( 'Your crescent sighting has been submitted and is pending review.', 'hilal' ),
            ),
            201
        );
    }

    /**
     * Upload PDF attachment for sighting
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function upload_attachment( $request ) {
        $files = $request->get_file_params();

        if ( empty( $files['file'] ) ) {
            return $this->error(
                'no_file',
                __( 'No file provided.', 'hilal' ),
                400
            );
        }

        $file = $files['file'];

        // Validate file type - PDF only
        if ( $file['type'] !== 'application/pdf' ) {
            return $this->error(
                'invalid_type',
                __( 'Invalid file type. Only PDF files are allowed.', 'hilal' ),
                400
            );
        }

        // Validate file size (20MB max)
        if ( $file['size'] > 20 * 1024 * 1024 ) {
            return $this->error(
                'file_too_large',
                __( 'File is too large. Maximum size is 20MB.', 'hilal' ),
                400
            );
        }

        // Handle upload
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $upload = wp_handle_upload( $file, array( 'test_form' => false ) );

        if ( isset( $upload['error'] ) ) {
            return $this->error(
                'upload_failed',
                $upload['error'],
                500
            );
        }

        // Create attachment
        $attachment_id = wp_insert_attachment(
            array(
                'post_mime_type' => $upload['type'],
                'post_title'     => sanitize_file_name( $file['name'] ),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ),
            $upload['file']
        );

        if ( is_wp_error( $attachment_id ) ) {
            return $this->error(
                'attachment_failed',
                __( 'Failed to create attachment.', 'hilal' ),
                500
            );
        }

        return $this->success(
            array(
                'id'       => $attachment_id,
                'url'      => $upload['url'],
                'filename' => basename( $upload['file'] ),
            ),
            201
        );
    }

    /**
     * Get approved sightings
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_approved_sightings( $request ) {
        $limit = $request->get_param( 'limit' );

        $sightings = Hilal_Sighting_Report::get_by_status( 'approved', $limit );

        return $this->success( array( 'sightings' => $sightings ) );
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    private function get_client_ip() {
        $ip = '';
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }
        return $ip;
    }

    /**
     * Get count of submissions from an IP today
     *
     * @param string $ip IP address.
     * @return int
     */
    private function get_ip_submissions_today( $ip ) {
        global $wpdb;

        $today = gmdate( 'Y-m-d' );

        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*)
             FROM {$wpdb->posts} p
             WHERE p.post_type = 'sighting_report'
             AND p.post_status = 'publish'
             AND DATE(p.post_date) = %s",
            $today
        ) );

        return (int) $count;
    }
}
