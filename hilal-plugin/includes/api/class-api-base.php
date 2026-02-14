<?php
/**
 * REST API Base Class
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal API Base Class
 */
abstract class Hilal_API_Base {

    /**
     * API namespace
     *
     * @var string
     */
    protected $namespace = 'hilal/v1';

    /**
     * Register routes
     */
    abstract public function register_routes();

    /**
     * Check if user is authenticated via JWT
     *
     * @param WP_REST_Request $request Request object.
     * @return bool|WP_Error
     */
    protected function check_jwt_auth( $request ) {
        // Check for JWT token in Authorization header
        $auth_header = $request->get_header( 'Authorization' );

        if ( empty( $auth_header ) ) {
            return new WP_Error(
                'rest_not_logged_in',
                __( 'Authentication required.', 'hilal' ),
                array( 'status' => 401 )
            );
        }

        // Verify JWT token (handled by JWT Authentication plugin)
        $user_id = get_current_user_id();

        if ( ! $user_id ) {
            return new WP_Error(
                'rest_forbidden',
                __( 'Invalid authentication token.', 'hilal' ),
                array( 'status' => 403 )
            );
        }

        return true;
    }

    /**
     * Get current authenticated user ID
     *
     * @return int
     */
    protected function get_current_user_id() {
        return get_current_user_id();
    }

    /**
     * Success response
     *
     * @param mixed $data    Response data.
     * @param int   $status  HTTP status code.
     * @return WP_REST_Response
     */
    protected function success( $data, $status = 200 ) {
        return new WP_REST_Response(
            array(
                'success' => true,
                'data'    => $data,
            ),
            $status
        );
    }

    /**
     * Error response
     *
     * @param string $code    Error code.
     * @param string $message Error message.
     * @param int    $status  HTTP status code.
     * @return WP_Error
     */
    protected function error( $code, $message, $status = 400 ) {
        return new WP_Error( $code, $message, array( 'status' => $status ) );
    }

    /**
     * Validate required parameters
     *
     * @param WP_REST_Request $request  Request object.
     * @param array           $required Required parameter names.
     * @return true|WP_Error
     */
    protected function validate_required( $request, $required ) {
        $params = $request->get_params();

        foreach ( $required as $field ) {
            if ( ! isset( $params[ $field ] ) || '' === $params[ $field ] ) {
                return $this->error(
                    'missing_parameter',
                    sprintf( __( 'Missing required parameter: %s', 'hilal' ), $field ),
                    400
                );
            }
        }

        return true;
    }

    /**
     * Sanitize text input
     *
     * @param string $input Input string.
     * @return string
     */
    protected function sanitize_text( $input ) {
        return sanitize_text_field( wp_unslash( $input ) );
    }

    /**
     * Sanitize textarea input
     *
     * @param string $input Input string.
     * @return string
     */
    protected function sanitize_textarea( $input ) {
        return sanitize_textarea_field( wp_unslash( $input ) );
    }

    /**
     * Get pagination parameters
     *
     * @param WP_REST_Request $request Request object.
     * @return array
     */
    protected function get_pagination_params( $request ) {
        return array(
            'page'     => max( 1, (int) $request->get_param( 'page' ) ?: 1 ),
            'per_page' => min( 100, max( 1, (int) $request->get_param( 'per_page' ) ?: 10 ) ),
        );
    }

    /**
     * Get language from request
     *
     * @param WP_REST_Request $request Request object.
     * @return string
     */
    protected function get_language( $request ) {
        $lang = $request->get_param( 'lang' );
        return in_array( $lang, array( 'en', 'ar' ), true ) ? $lang : 'en';
    }

    /**
     * Sanitize float value for REST API
     *
     * @param mixed $value The value to sanitize.
     * @return float
     */
    public function sanitize_float( $value ) {
        return floatval( $value );
    }

    /**
     * Sanitize integer value for REST API
     *
     * @param mixed $value The value to sanitize.
     * @return int
     */
    public function sanitize_int( $value ) {
        return intval( $value );
    }
}
