<?php
/**
 * Announcement Custom Post Type
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Announcement Post Type Class
 */
class Hilal_Announcement {

    /**
     * Post type slug
     *
     * @var string
     */
    const POST_TYPE = 'announcement';

    /**
     * Announcement types
     *
     * @var array
     */
    public static $types = array(
        'month_start'   => 'Month Start',
        'moon_sighting' => 'Moon Sighting',
        'islamic_event' => 'Islamic Event',
        'general'       => 'General',
    );

    /**
     * Priority levels
     *
     * @var array
     */
    public static $priorities = array(
        'high'   => 'High',
        'medium' => 'Medium',
        'low'    => 'Low',
    );

    /**
     * Register the post type
     */
    public static function register() {
        $labels = array(
            'name'                  => _x( 'Announcements', 'Post type general name', 'hilal' ),
            'singular_name'         => _x( 'Announcement', 'Post type singular name', 'hilal' ),
            'menu_name'             => _x( 'Announcements', 'Admin Menu text', 'hilal' ),
            'name_admin_bar'        => _x( 'Announcement', 'Add New on Toolbar', 'hilal' ),
            'add_new'               => __( 'Add New', 'hilal' ),
            'add_new_item'          => __( 'Add New Announcement', 'hilal' ),
            'new_item'              => __( 'New Announcement', 'hilal' ),
            'edit_item'             => __( 'Edit Announcement', 'hilal' ),
            'view_item'             => __( 'View Announcement', 'hilal' ),
            'all_items'             => __( 'All Announcements', 'hilal' ),
            'search_items'          => __( 'Search Announcements', 'hilal' ),
            'not_found'             => __( 'No announcements found.', 'hilal' ),
            'not_found_in_trash'    => __( 'No announcements found in Trash.', 'hilal' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'announcement' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 7,
            'menu_icon'          => 'dashicons-megaphone',
            'supports'           => array( 'title', 'thumbnail' ),
            'show_in_rest'       => true,
            'rest_base'          => 'announcements',
        );

        register_post_type( self::POST_TYPE, $args );

        // Register ACF fields
        if ( function_exists( 'acf_add_local_field_group' ) ) { self::register_acf_fields(); } else { add_action( 'acf/init', array( __CLASS__, 'register_acf_fields' ) ); }

        // Handle notification on publish
        add_action( 'transition_post_status', array( __CLASS__, 'on_publish' ), 10, 3 );
    }

    /**
     * Register ACF fields for Announcement
     */
    public static function register_acf_fields() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group( array(
            'key'      => 'group_announcement',
            'title'    => __( 'Announcement Details', 'hilal' ),
            'fields'   => array(
                // English Content Tab
                array(
                    'key'   => 'field_tab_english',
                    'label' => __( 'English', 'hilal' ),
                    'type'  => 'tab',
                ),
                array(
                    'key'           => 'field_title_en',
                    'label'         => __( 'Title (English)', 'hilal' ),
                    'name'          => 'title_en',
                    'type'          => 'text',
                    'required'      => 1,
                ),
                array(
                    'key'           => 'field_body_en',
                    'label'         => __( 'Content (English)', 'hilal' ),
                    'name'          => 'body_en',
                    'type'          => 'wysiwyg',
                    'required'      => 1,
                    'tabs'          => 'all',
                    'toolbar'       => 'full',
                    'media_upload'  => 1,
                ),

                // Arabic Content Tab
                array(
                    'key'   => 'field_tab_arabic',
                    'label' => __( 'Arabic (عربي)', 'hilal' ),
                    'type'  => 'tab',
                ),
                array(
                    'key'           => 'field_title_ar',
                    'label'         => __( 'Title (Arabic)', 'hilal' ),
                    'name'          => 'title_ar',
                    'type'          => 'text',
                    'required'      => 1,
                ),
                array(
                    'key'           => 'field_body_ar',
                    'label'         => __( 'Content (Arabic)', 'hilal' ),
                    'name'          => 'body_ar',
                    'type'          => 'wysiwyg',
                    'required'      => 1,
                    'tabs'          => 'all',
                    'toolbar'       => 'full',
                    'media_upload'  => 1,
                ),

                // Settings Tab
                array(
                    'key'   => 'field_tab_settings',
                    'label' => __( 'Settings', 'hilal' ),
                    'type'  => 'tab',
                ),
                array(
                    'key'           => 'field_type',
                    'label'         => __( 'Announcement Type', 'hilal' ),
                    'name'          => 'type',
                    'type'          => 'select',
                    'required'      => 1,
                    'choices'       => array(
                        'month_start'   => __( 'Month Start', 'hilal' ),
                        'moon_sighting' => __( 'Moon Sighting', 'hilal' ),
                        'islamic_event' => __( 'Islamic Event', 'hilal' ),
                        'general'       => __( 'General', 'hilal' ),
                    ),
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_priority',
                    'label'         => __( 'Priority', 'hilal' ),
                    'name'          => 'priority',
                    'type'          => 'select',
                    'required'      => 1,
                    'choices'       => array(
                        'high'   => __( 'High', 'hilal' ),
                        'medium' => __( 'Medium', 'hilal' ),
                        'low'    => __( 'Low', 'hilal' ),
                    ),
                    'default_value' => 'medium',
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_hijri_month_id',
                    'label'         => __( 'Related Hijri Month', 'hilal' ),
                    'name'          => 'hijri_month_id',
                    'type'          => 'post_object',
                    'post_type'     => array( 'hijri_month' ),
                    'return_format' => 'id',
                    'allow_null'    => 1,
                ),

                // Notifications Tab
                array(
                    'key'   => 'field_tab_notifications',
                    'label' => __( 'Notifications', 'hilal' ),
                    'type'  => 'tab',
                ),
                array(
                    'key'           => 'field_send_notification',
                    'label'         => __( 'Send Push Notification', 'hilal' ),
                    'name'          => 'send_notification',
                    'type'          => 'true_false',
                    'instructions'  => __( 'Send push notification to all mobile app users when this announcement is published.', 'hilal' ),
                    'ui'            => 1,
                    'wrapper'       => array( 'width' => '33' ),
                ),
                array(
                    'key'           => 'field_send_email',
                    'label'         => __( 'Send Email', 'hilal' ),
                    'name'          => 'send_email',
                    'type'          => 'true_false',
                    'instructions'  => __( 'Send email to all subscribers.', 'hilal' ),
                    'ui'            => 1,
                    'wrapper'       => array( 'width' => '33' ),
                ),
                array(
                    'key'           => 'field_notification_sent',
                    'label'         => __( 'Notification Sent', 'hilal' ),
                    'name'          => 'notification_sent',
                    'type'          => 'true_false',
                    'instructions'  => __( 'Automatically set to true after notifications are sent.', 'hilal' ),
                    'ui'            => 1,
                    'default_value' => 0,
                    'wrapper'       => array( 'width' => '33' ),
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
     * Handle actions on publish
     *
     * @param string  $new_status New post status.
     * @param string  $old_status Old post status.
     * @param WP_Post $post       Post object.
     */
    public static function on_publish( $new_status, $old_status, $post ) {
        if ( self::POST_TYPE !== $post->post_type ) {
            return;
        }

        if ( 'publish' !== $new_status || 'publish' === $old_status ) {
            return;
        }

        $notification_sent = get_field( 'notification_sent', $post->ID );
        if ( $notification_sent ) {
            return;
        }

        $send_push  = get_field( 'send_notification', $post->ID );
        $send_email = get_field( 'send_email', $post->ID );

        if ( $send_push ) {
            do_action( 'hilal_send_push_notification', $post->ID );
        }

        if ( $send_email ) {
            do_action( 'hilal_send_email_notification', $post->ID );
        }

        if ( $send_push || $send_email ) {
            update_field( 'notification_sent', true, $post->ID );
        }
    }

    /**
     * Get announcements with filters
     *
     * @param array $args Query arguments.
     * @return array
     */
    public static function get_announcements( $args = array() ) {
        $defaults = array(
            'posts_per_page' => 10,
            'paged'          => 1,
            'type'           => '',
            'priority'       => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $query_args = array(
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => $args['posts_per_page'],
            'paged'          => $args['paged'],
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        $meta_query = array();

        if ( ! empty( $args['type'] ) ) {
            $meta_query[] = array(
                'key'   => 'type',
                'value' => $args['type'],
            );
        }

        if ( ! empty( $args['priority'] ) ) {
            $meta_query[] = array(
                'key'   => 'priority',
                'value' => $args['priority'],
            );
        }

        if ( ! empty( $meta_query ) ) {
            $query_args['meta_query'] = $meta_query;
        }

        $query         = new WP_Query( $query_args );
        $announcements = array();

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $announcements[] = self::format_announcement_data( $post );
            }
        }

        return array(
            'announcements' => $announcements,
            'total'         => $query->found_posts,
            'pages'         => $query->max_num_pages,
            'current_page'  => $args['paged'],
        );
    }

    /**
     * Get latest announcement
     *
     * @return array|null
     */
    public static function get_latest() {
        $result = self::get_announcements( array( 'posts_per_page' => 1 ) );
        return ! empty( $result['announcements'] ) ? $result['announcements'][0] : null;
    }

    /**
     * Get recent announcements
     *
     * @param int $count Number of announcements to return.
     * @return array
     */
    public static function get_recent( $count = 4 ) {
        $result = self::get_announcements( array( 'posts_per_page' => $count ) );
        return $result['announcements'] ?? array();
    }

    /**
     * Get all announcements with optional type filter
     *
     * @param string $type Optional type filter.
     * @return array
     */
    public static function get_all( $type = '' ) {
        $args = array( 'posts_per_page' => -1 );
        if ( ! empty( $type ) ) {
            $args['type'] = $type;
        }
        $result = self::get_announcements( $args );
        return $result['announcements'] ?? array();
    }

    /**
     * Get announcement by ID
     *
     * @param int $post_id Post ID.
     * @return array|null
     */
    public static function get_by_id( $post_id ) {
        $post = get_post( $post_id );

        if ( ! $post || self::POST_TYPE !== $post->post_type ) {
            return null;
        }

        return self::format_announcement_data( $post );
    }

    /**
     * Format announcement data for API/display
     *
     * @param WP_Post $post Post object.
     * @return array
     */
    public static function format_announcement_data( $post ) {
        $thumbnail    = get_post_thumbnail_id( $post->ID );
        $thumbnail_url = $thumbnail ? wp_get_attachment_image_url( $thumbnail, 'large' ) : null;

        return array(
            'id'             => $post->ID,
            'slug'           => $post->post_name,
            'title_en'       => get_field( 'title_en', $post->ID ),
            'title_ar'       => get_field( 'title_ar', $post->ID ),
            'body_en'        => get_field( 'body_en', $post->ID ),
            'body_ar'        => get_field( 'body_ar', $post->ID ),
            'type'           => get_field( 'type', $post->ID ),
            'type_label'     => self::$types[ get_field( 'type', $post->ID ) ] ?? '',
            'priority'       => get_field( 'priority', $post->ID ),
            'hijri_month_id' => get_field( 'hijri_month_id', $post->ID ),
            'thumbnail'      => $thumbnail_url,
            'published_at'   => get_the_date( 'c', $post ),
            'published_date' => get_the_date( 'j F Y', $post ),
            'url'            => get_permalink( $post ),
        );
    }

    /**
     * Create a month start announcement
     *
     * @param int    $hijri_month_id Hijri month post ID.
     * @param string $body_en        English body content.
     * @param string $body_ar        Arabic body content.
     * @return int|WP_Error
     */
    public static function create_month_start_announcement( $hijri_month_id, $body_en = '', $body_ar = '' ) {
        $month = get_post( $hijri_month_id );
        if ( ! $month ) {
            return new WP_Error( 'invalid_month', __( 'Invalid Hijri month.', 'hilal' ) );
        }

        $month_name_en = get_field( 'month_name_en', $hijri_month_id );
        $month_name_ar = get_field( 'month_name_ar', $hijri_month_id );
        $hijri_year    = get_field( 'hijri_year', $hijri_month_id );

        $title_en = sprintf( 'Beginning of %s %d AH', $month_name_en, $hijri_year );
        $title_ar = sprintf( 'بداية شهر %s %d هـ', $month_name_ar, $hijri_year );

        if ( empty( $body_en ) ) {
            $body_en = sprintf(
                'The Hilal (new crescent moon) for the month of %s %d AH has been sighted. The first day of %s begins tonight.',
                $month_name_en,
                $hijri_year,
                $month_name_en
            );
        }

        if ( empty( $body_ar ) ) {
            $body_ar = sprintf(
                'تم رؤية هلال شهر %s %d هـ. يبدأ أول يوم من %s الليلة.',
                $month_name_ar,
                $hijri_year,
                $month_name_ar
            );
        }

        $post_id = wp_insert_post( array(
            'post_type'   => self::POST_TYPE,
            'post_title'  => $title_en,
            'post_status' => 'draft',
        ) );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        update_field( 'title_en', $title_en, $post_id );
        update_field( 'title_ar', $title_ar, $post_id );
        update_field( 'body_en', $body_en, $post_id );
        update_field( 'body_ar', $body_ar, $post_id );
        update_field( 'type', 'month_start', $post_id );
        update_field( 'priority', 'high', $post_id );
        update_field( 'hijri_month_id', $hijri_month_id, $post_id );
        update_field( 'send_notification', true, $post_id );
        update_field( 'send_email', true, $post_id );

        return $post_id;
    }
}
