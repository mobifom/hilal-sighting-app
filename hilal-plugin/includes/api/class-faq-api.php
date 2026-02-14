<?php
/**
 * FAQ REST API
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal FAQ API Class
 */
class Hilal_FAQ_API extends Hilal_API_Base {

    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Get all FAQs
        register_rest_route(
            $this->namespace,
            '/faqs',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_faqs' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'category' => array(
                        'type'        => 'string',
                        'description' => 'Filter by category',
                    ),
                    'featured' => array(
                        'type'        => 'boolean',
                        'description' => 'Get only featured FAQs',
                        'default'     => false,
                    ),
                    'grouped' => array(
                        'type'        => 'boolean',
                        'description' => 'Group FAQs by category',
                        'default'     => false,
                    ),
                ),
            )
        );

        // Get single FAQ
        register_rest_route(
            $this->namespace,
            '/faqs/(?P<id>\d+)',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_faq' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'id' => array(
                        'type'        => 'integer',
                        'required'    => true,
                        'description' => 'FAQ ID',
                    ),
                ),
            )
        );

        // Search FAQs
        register_rest_route(
            $this->namespace,
            '/faqs/search',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'search_faqs' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'q' => array(
                        'type'        => 'string',
                        'required'    => true,
                        'description' => 'Search query',
                    ),
                    'lang' => array(
                        'type'        => 'string',
                        'default'     => 'en',
                        'enum'        => array( 'en', 'ar' ),
                        'description' => 'Language to search in',
                    ),
                ),
            )
        );

        // Get FAQ categories
        register_rest_route(
            $this->namespace,
            '/faqs/categories',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_categories' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * Get all FAQs
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_faqs( $request ) {
        $category = $request->get_param( 'category' );
        $featured = $request->get_param( 'featured' );
        $grouped  = $request->get_param( 'grouped' );

        if ( $grouped ) {
            $data = Hilal_FAQ::get_grouped_faqs();
            return $this->success( array_values( $data ) );
        }

        $args = array();

        if ( $category ) {
            $args['category'] = $category;
        }

        if ( $featured ) {
            $args['featured_only'] = true;
        }

        $faqs = Hilal_FAQ::get_faqs( $args );

        return $this->success( $faqs );
    }

    /**
     * Get single FAQ
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_faq( $request ) {
        $id  = $request->get_param( 'id' );
        $faq = Hilal_FAQ::get_by_id( $id );

        if ( ! $faq ) {
            return $this->error( 'faq_not_found', __( 'FAQ not found.', 'hilal' ), 404 );
        }

        return $this->success( $faq );
    }

    /**
     * Search FAQs
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function search_faqs( $request ) {
        $query = $request->get_param( 'q' );
        $lang  = $request->get_param( 'lang' );

        if ( strlen( $query ) < 2 ) {
            return $this->error( 'query_too_short', __( 'Search query must be at least 2 characters.', 'hilal' ), 400 );
        }

        $results = Hilal_FAQ::search( $query, $lang );

        return $this->success( array(
            'query'   => $query,
            'count'   => count( $results ),
            'results' => $results,
        ) );
    }

    /**
     * Get FAQ categories
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_categories( $request ) {
        $categories = array();

        foreach ( Hilal_FAQ::$categories as $key => $label ) {
            $categories[] = array(
                'key'      => $key,
                'label_en' => $label,
                'label_ar' => $this->get_category_label_ar( $key ),
            );
        }

        return $this->success( $categories );
    }

    /**
     * Get Arabic label for category
     *
     * @param string $key Category key.
     * @return string
     */
    private function get_category_label_ar( $key ) {
        $labels = array(
            'general'       => 'عام',
            'moon_sighting' => 'رؤية الهلال',
            'prayer_times'  => 'أوقات الصلاة',
            'calendar'      => 'التقويم الإسلامي',
            'technical'     => 'تقني',
        );
        return $labels[ $key ] ?? $key;
    }
}
