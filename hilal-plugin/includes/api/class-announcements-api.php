<?php
/**
 * Announcements REST API
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Announcements API Class
 */
class Hilal_Announcements_API extends Hilal_API_Base {

    /**
     * Register routes
     */
    public function register_routes() {
        // GET /hilal/v1/announcements
        register_rest_route(
            $this->namespace,
            '/announcements',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_announcements' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'page'     => array(
                        'default'           => 1,
                        'type'              => 'integer',
                        'minimum'           => 1,
                        'sanitize_callback' => 'absint',
                    ),
                    'per_page' => array(
                        'default'           => 10,
                        'type'              => 'integer',
                        'minimum'           => 1,
                        'maximum'           => 100,
                        'sanitize_callback' => 'absint',
                    ),
                    'type'     => array(
                        'type'              => 'string',
                        'enum'              => array( 'month_start', 'moon_sighting', 'islamic_event', 'general' ),
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'priority' => array(
                        'type'              => 'string',
                        'enum'              => array( 'high', 'medium', 'low' ),
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );

        // GET /hilal/v1/announcements/{id}
        register_rest_route(
            $this->namespace,
            '/announcements/(?P<id>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_announcement' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'type'              => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );

        // GET /hilal/v1/announcements/latest
        register_rest_route(
            $this->namespace,
            '/announcements/latest',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_latest_announcement' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * Get announcements list
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_announcements( $request ) {
        $lang = $this->get_language( $request );

        $args = array(
            'posts_per_page' => $request->get_param( 'per_page' ),
            'paged'          => $request->get_param( 'page' ),
            'type'           => $request->get_param( 'type' ),
            'priority'       => $request->get_param( 'priority' ),
        );

        $result = Hilal_Announcement::get_announcements( $args );

        // Format for requested language
        $announcements = array_map( function( $item ) use ( $lang ) {
            return $this->format_for_language( $item, $lang );
        }, $result['announcements'] );

        return $this->success( array(
            'announcements' => $announcements,
            'pagination'    => array(
                'total'        => $result['total'],
                'total_pages'  => $result['pages'],
                'current_page' => $result['current_page'],
                'per_page'     => $args['posts_per_page'],
            ),
        ) );
    }

    /**
     * Get single announcement
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_announcement( $request ) {
        $id   = $request->get_param( 'id' );
        $lang = $this->get_language( $request );

        $announcement = Hilal_Announcement::get_by_id( $id );

        if ( ! $announcement ) {
            return $this->error(
                'not_found',
                __( 'Announcement not found.', 'hilal' ),
                404
            );
        }

        return $this->success( $this->format_for_language( $announcement, $lang ) );
    }

    /**
     * Get latest announcement
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_latest_announcement( $request ) {
        $lang = $this->get_language( $request );

        $announcement = Hilal_Announcement::get_latest();

        if ( ! $announcement ) {
            return $this->success( null );
        }

        return $this->success( $this->format_for_language( $announcement, $lang ) );
    }

    /**
     * Format announcement for requested language
     *
     * @param array  $announcement Announcement data.
     * @param string $lang         Language code.
     * @return array
     */
    private function format_for_language( $announcement, $lang ) {
        return array(
            'id'             => $announcement['id'],
            'slug'           => $announcement['slug'],
            'title'          => 'ar' === $lang ? $announcement['title_ar'] : $announcement['title_en'],
            'title_en'       => $announcement['title_en'],
            'title_ar'       => $announcement['title_ar'],
            'body'           => 'ar' === $lang ? $announcement['body_ar'] : $announcement['body_en'],
            'body_en'        => $announcement['body_en'],
            'body_ar'        => $announcement['body_ar'],
            'type'           => $announcement['type'],
            'type_label'     => $announcement['type_label'],
            'priority'       => $announcement['priority'],
            'hijri_month_id' => $announcement['hijri_month_id'],
            'thumbnail'      => $announcement['thumbnail'],
            'published_at'   => $announcement['published_at'],
            'published_date' => $announcement['published_date'],
            'url'            => $announcement['url'],
        );
    }
}
