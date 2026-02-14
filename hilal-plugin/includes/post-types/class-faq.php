<?php
/**
 * FAQ Custom Post Type
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal FAQ Post Type Class
 */
class Hilal_FAQ {

    /**
     * Post type slug
     *
     * @var string
     */
    const POST_TYPE = 'faq';

    /**
     * FAQ categories
     *
     * @var array
     */
    public static $categories = array(
        'general'       => 'General',
        'moon_sighting' => 'Moon Sighting',
        'prayer_times'  => 'Prayer Times',
        'calendar'      => 'Islamic Calendar',
        'technical'     => 'Technical',
    );

    /**
     * Register the post type
     */
    public static function register() {
        $labels = array(
            'name'                  => _x( 'FAQs', 'Post type general name', 'hilal' ),
            'singular_name'         => _x( 'FAQ', 'Post type singular name', 'hilal' ),
            'menu_name'             => _x( 'FAQs', 'Admin Menu text', 'hilal' ),
            'name_admin_bar'        => _x( 'FAQ', 'Add New on Toolbar', 'hilal' ),
            'add_new'               => __( 'Add New', 'hilal' ),
            'add_new_item'          => __( 'Add New FAQ', 'hilal' ),
            'new_item'              => __( 'New FAQ', 'hilal' ),
            'edit_item'             => __( 'Edit FAQ', 'hilal' ),
            'view_item'             => __( 'View FAQ', 'hilal' ),
            'all_items'             => __( 'All FAQs', 'hilal' ),
            'search_items'          => __( 'Search FAQs', 'hilal' ),
            'not_found'             => __( 'No FAQs found.', 'hilal' ),
            'not_found_in_trash'    => __( 'No FAQs found in Trash.', 'hilal' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'faq' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 8,
            'menu_icon'          => 'dashicons-editor-help',
            'supports'           => array( 'title' ),
            'show_in_rest'       => true,
            'rest_base'          => 'faqs',
        );

        register_post_type( self::POST_TYPE, $args );

        // Register ACF fields
        if ( function_exists( 'acf_add_local_field_group' ) ) {
            self::register_acf_fields();
        } else {
            add_action( 'acf/init', array( __CLASS__, 'register_acf_fields' ) );
        }
    }

    /**
     * Register ACF fields for FAQ
     */
    public static function register_acf_fields() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group( array(
            'key'      => 'group_faq',
            'title'    => __( 'FAQ Details', 'hilal' ),
            'fields'   => array(
                // English Content Tab
                array(
                    'key'   => 'field_faq_tab_english',
                    'label' => __( 'English', 'hilal' ),
                    'type'  => 'tab',
                ),
                array(
                    'key'           => 'field_faq_question_en',
                    'label'         => __( 'Question (English)', 'hilal' ),
                    'name'          => 'question_en',
                    'type'          => 'text',
                    'required'      => 1,
                    'instructions'  => __( 'Enter the question in English', 'hilal' ),
                ),
                array(
                    'key'           => 'field_faq_answer_en',
                    'label'         => __( 'Answer (English)', 'hilal' ),
                    'name'          => 'answer_en',
                    'type'          => 'wysiwyg',
                    'required'      => 1,
                    'tabs'          => 'all',
                    'toolbar'       => 'full',
                    'media_upload'  => 1,
                ),

                // Arabic Content Tab
                array(
                    'key'   => 'field_faq_tab_arabic',
                    'label' => __( 'Arabic (عربي)', 'hilal' ),
                    'type'  => 'tab',
                ),
                array(
                    'key'           => 'field_faq_question_ar',
                    'label'         => __( 'Question (Arabic)', 'hilal' ),
                    'name'          => 'question_ar',
                    'type'          => 'text',
                    'required'      => 1,
                    'instructions'  => __( 'Enter the question in Arabic', 'hilal' ),
                ),
                array(
                    'key'           => 'field_faq_answer_ar',
                    'label'         => __( 'Answer (Arabic)', 'hilal' ),
                    'name'          => 'answer_ar',
                    'type'          => 'wysiwyg',
                    'required'      => 1,
                    'tabs'          => 'all',
                    'toolbar'       => 'full',
                    'media_upload'  => 1,
                ),

                // Settings Tab
                array(
                    'key'   => 'field_faq_tab_settings',
                    'label' => __( 'Settings', 'hilal' ),
                    'type'  => 'tab',
                ),
                array(
                    'key'           => 'field_faq_category',
                    'label'         => __( 'Category', 'hilal' ),
                    'name'          => 'category',
                    'type'          => 'select',
                    'required'      => 1,
                    'choices'       => array(
                        'general'       => __( 'General', 'hilal' ),
                        'moon_sighting' => __( 'Moon Sighting', 'hilal' ),
                        'prayer_times'  => __( 'Prayer Times', 'hilal' ),
                        'calendar'      => __( 'Islamic Calendar', 'hilal' ),
                        'technical'     => __( 'Technical', 'hilal' ),
                    ),
                    'default_value' => 'general',
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_faq_order',
                    'label'         => __( 'Display Order', 'hilal' ),
                    'name'          => 'display_order',
                    'type'          => 'number',
                    'instructions'  => __( 'Lower numbers appear first', 'hilal' ),
                    'default_value' => 10,
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_faq_featured',
                    'label'         => __( 'Featured', 'hilal' ),
                    'name'          => 'is_featured',
                    'type'          => 'true_false',
                    'instructions'  => __( 'Show this FAQ prominently on the homepage', 'hilal' ),
                    'ui'            => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => self::POST_TYPE,
                    ),
                ),
            ),
            'menu_order'            => 0,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
        ) );
    }

    /**
     * Get FAQs with filters
     *
     * @param array $args Query arguments.
     * @return array
     */
    public static function get_faqs( $args = array() ) {
        $defaults = array(
            'posts_per_page' => -1,
            'category'       => '',
            'featured_only'  => false,
        );

        $args = wp_parse_args( $args, $defaults );

        $query_args = array(
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => $args['posts_per_page'],
            'post_status'    => 'publish',
            'meta_key'       => 'display_order',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        );

        $meta_query = array();

        if ( ! empty( $args['category'] ) ) {
            $meta_query[] = array(
                'key'   => 'category',
                'value' => $args['category'],
            );
        }

        if ( $args['featured_only'] ) {
            $meta_query[] = array(
                'key'   => 'is_featured',
                'value' => '1',
            );
        }

        if ( ! empty( $meta_query ) ) {
            $query_args['meta_query'] = $meta_query;
        }

        $query = new WP_Query( $query_args );
        $faqs  = array();

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $faqs[] = self::format_faq_data( $post );
            }
        }

        return $faqs;
    }

    /**
     * Get FAQs grouped by category
     *
     * @return array
     */
    public static function get_grouped_faqs() {
        $all_faqs = self::get_faqs();
        $grouped  = array();

        foreach ( self::$categories as $key => $label ) {
            $grouped[ $key ] = array(
                'key'      => $key,
                'label'    => $label,
                'label_ar' => self::get_category_label_ar( $key ),
                'faqs'     => array(),
            );
        }

        foreach ( $all_faqs as $faq ) {
            $cat = $faq['category'] ?? 'general';
            if ( isset( $grouped[ $cat ] ) ) {
                $grouped[ $cat ]['faqs'][] = $faq;
            }
        }

        // Remove empty categories
        return array_filter( $grouped, function( $group ) {
            return ! empty( $group['faqs'] );
        } );
    }

    /**
     * Get Arabic label for category
     *
     * @param string $key Category key.
     * @return string
     */
    private static function get_category_label_ar( $key ) {
        $labels = array(
            'general'       => 'عام',
            'moon_sighting' => 'رؤية الهلال',
            'prayer_times'  => 'أوقات الصلاة',
            'calendar'      => 'التقويم الإسلامي',
            'technical'     => 'تقني',
        );
        return $labels[ $key ] ?? $key;
    }

    /**
     * Get featured FAQs
     *
     * @param int $limit Number of FAQs to return.
     * @return array
     */
    public static function get_featured( $limit = 5 ) {
        return self::get_faqs( array(
            'posts_per_page' => $limit,
            'featured_only'  => true,
        ) );
    }

    /**
     * Get FAQ by ID
     *
     * @param int $post_id Post ID.
     * @return array|null
     */
    public static function get_by_id( $post_id ) {
        $post = get_post( $post_id );

        if ( ! $post || self::POST_TYPE !== $post->post_type ) {
            return null;
        }

        return self::format_faq_data( $post );
    }

    /**
     * Search FAQs
     *
     * @param string $query  Search query.
     * @param string $lang   Language ('en' or 'ar').
     * @return array
     */
    public static function search( $query, $lang = 'en' ) {
        $all_faqs = self::get_faqs();
        $results  = array();
        $query    = strtolower( $query );

        foreach ( $all_faqs as $faq ) {
            $question = strtolower( $lang === 'ar' ? $faq['question_ar'] : $faq['question_en'] );
            $answer   = strtolower( $lang === 'ar' ? $faq['answer_ar'] : $faq['answer_en'] );

            if ( strpos( $question, $query ) !== false || strpos( $answer, $query ) !== false ) {
                $results[] = $faq;
            }
        }

        return $results;
    }

    /**
     * Format FAQ data for API/display
     *
     * @param WP_Post $post Post object.
     * @return array
     */
    public static function format_faq_data( $post ) {
        $category = get_field( 'category', $post->ID ) ?: 'general';

        return array(
            'id'            => $post->ID,
            'slug'          => $post->post_name,
            'question_en'   => get_field( 'question_en', $post->ID ),
            'question_ar'   => get_field( 'question_ar', $post->ID ),
            'answer_en'     => get_field( 'answer_en', $post->ID ),
            'answer_ar'     => get_field( 'answer_ar', $post->ID ),
            'category'      => $category,
            'category_label' => self::$categories[ $category ] ?? $category,
            'category_label_ar' => self::get_category_label_ar( $category ),
            'display_order' => (int) get_field( 'display_order', $post->ID ),
            'is_featured'   => (bool) get_field( 'is_featured', $post->ID ),
            'url'           => get_permalink( $post ),
        );
    }
}
