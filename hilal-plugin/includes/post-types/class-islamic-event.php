<?php
/**
 * Islamic Event Custom Post Type
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Islamic Event Post Type Class
 */
class Hilal_Islamic_Event {

    /**
     * Post type slug
     *
     * @var string
     */
    const POST_TYPE = 'islamic_event';

    /**
     * Register the post type
     */
    public static function register() {
        $labels = array(
            'name'                  => _x( 'Islamic Events', 'Post type general name', 'hilal' ),
            'singular_name'         => _x( 'Islamic Event', 'Post type singular name', 'hilal' ),
            'menu_name'             => _x( 'Islamic Events', 'Admin Menu text', 'hilal' ),
            'name_admin_bar'        => _x( 'Islamic Event', 'Add New on Toolbar', 'hilal' ),
            'add_new'               => __( 'Add New', 'hilal' ),
            'add_new_item'          => __( 'Add New Islamic Event', 'hilal' ),
            'new_item'              => __( 'New Islamic Event', 'hilal' ),
            'edit_item'             => __( 'Edit Islamic Event', 'hilal' ),
            'view_item'             => __( 'View Islamic Event', 'hilal' ),
            'all_items'             => __( 'All Events', 'hilal' ),
            'search_items'          => __( 'Search Islamic Events', 'hilal' ),
            'not_found'             => __( 'No Islamic events found.', 'hilal' ),
            'not_found_in_trash'    => __( 'No Islamic events found in Trash.', 'hilal' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'islamic-event' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 8,
            'menu_icon'          => 'dashicons-star-filled',
            'supports'           => array( 'title', 'thumbnail' ),
            'show_in_rest'       => true,
            'rest_base'          => 'islamic-events',
        );

        register_post_type( self::POST_TYPE, $args );

        // Register ACF fields
        if ( function_exists( 'acf_add_local_field_group' ) ) { self::register_acf_fields(); } else { add_action( 'acf/init', array( __CLASS__, 'register_acf_fields' ) ); }
    }

    /**
     * Register ACF fields for Islamic Event
     */
    public static function register_acf_fields() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group( array(
            'key'      => 'group_islamic_event',
            'title'    => __( 'Islamic Event Details', 'hilal' ),
            'fields'   => array(
                array(
                    'key'           => 'field_event_name_en',
                    'label'         => __( 'Event Name (English)', 'hilal' ),
                    'name'          => 'event_name_en',
                    'type'          => 'text',
                    'required'      => 1,
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_event_name_ar',
                    'label'         => __( 'Event Name (Arabic)', 'hilal' ),
                    'name'          => 'event_name_ar',
                    'type'          => 'text',
                    'required'      => 1,
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_hijri_day',
                    'label'         => __( 'Hijri Day', 'hilal' ),
                    'name'          => 'hijri_day',
                    'type'          => 'number',
                    'required'      => 1,
                    'min'           => 1,
                    'max'           => 30,
                    'wrapper'       => array( 'width' => '25' ),
                ),
                array(
                    'key'           => 'field_hijri_month',
                    'label'         => __( 'Hijri Month', 'hilal' ),
                    'name'          => 'hijri_month',
                    'type'          => 'select',
                    'required'      => 1,
                    'choices'       => array(
                        1  => '1 - Muharram',
                        2  => '2 - Safar',
                        3  => '3 - Rabi\' al-Awwal',
                        4  => '4 - Rabi\' al-Thani',
                        5  => '5 - Jumada al-Awwal',
                        6  => '6 - Jumada al-Thani',
                        7  => '7 - Rajab',
                        8  => '8 - Sha\'ban',
                        9  => '9 - Ramadan',
                        10 => '10 - Shawwal',
                        11 => '11 - Dhul Qi\'dah',
                        12 => '12 - Dhul Hijjah',
                    ),
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_is_recurring',
                    'label'         => __( 'Recurring Yearly', 'hilal' ),
                    'name'          => 'is_recurring',
                    'type'          => 'true_false',
                    'instructions'  => __( 'This event occurs every year on the same Hijri date.', 'hilal' ),
                    'default_value' => 1,
                    'ui'            => 1,
                    'wrapper'       => array( 'width' => '25' ),
                ),
                array(
                    'key'           => 'field_description_en',
                    'label'         => __( 'Description (English)', 'hilal' ),
                    'name'          => 'description_en',
                    'type'          => 'textarea',
                    'rows'          => 4,
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_description_ar',
                    'label'         => __( 'Description (Arabic)', 'hilal' ),
                    'name'          => 'description_ar',
                    'type'          => 'textarea',
                    'rows'          => 4,
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_event_category',
                    'label'         => __( 'Event Category', 'hilal' ),
                    'name'          => 'event_category',
                    'type'          => 'select',
                    'choices'       => array(
                        'eid'           => __( 'Eid', 'hilal' ),
                        'fasting'       => __( 'Fasting', 'hilal' ),
                        'hajj'          => __( 'Hajj', 'hilal' ),
                        'special_night' => __( 'Special Night', 'hilal' ),
                        'historical'    => __( 'Historical', 'hilal' ),
                        'other'         => __( 'Other', 'hilal' ),
                    ),
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_duration_days',
                    'label'         => __( 'Duration (Days)', 'hilal' ),
                    'name'          => 'duration_days',
                    'type'          => 'number',
                    'instructions'  => __( 'Number of days for multi-day events (e.g., 3 for Eid al-Adha).', 'hilal' ),
                    'min'           => 1,
                    'max'           => 30,
                    'default_value' => 1,
                    'wrapper'       => array( 'width' => '50' ),
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
     * Get all events
     *
     * @return array
     */
    public static function get_all_events() {
        $args = array(
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'meta_value_num',
            'meta_key'       => 'hijri_month',
            'order'          => 'ASC',
        );

        $query  = new WP_Query( $args );
        $events = array();

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $events[] = self::format_event_data( $post );
            }
        }

        // Sort by month then day
        usort( $events, function( $a, $b ) {
            if ( $a['hijri_month'] === $b['hijri_month'] ) {
                return $a['hijri_day'] <=> $b['hijri_day'];
            }
            return $a['hijri_month'] <=> $b['hijri_month'];
        } );

        return $events;
    }

    /**
     * Get events for a specific Hijri month
     *
     * @param int $month Hijri month number.
     * @return array
     */
    public static function get_events_for_month( $month ) {
        $args = array(
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_key'       => 'hijri_month',
            'meta_value'     => $month,
            'orderby'        => 'meta_value_num',
            'meta_key'       => 'hijri_day',
            'order'          => 'ASC',
        );

        $query  = new WP_Query( $args );
        $events = array();

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $events[] = self::format_event_data( $post );
            }
        }

        return $events;
    }

    /**
     * Get upcoming events based on current Hijri date
     *
     * @param int $limit Number of events to return.
     * @return array
     */
    public static function get_upcoming_events( $limit = 5 ) {
        $current = Hilal_Hijri_Date::get_current_month_info();
        $month   = $current['month'];
        $day     = $current['day'];

        $all_events = self::get_all_events();
        $upcoming   = array();

        // Events in current month after today
        foreach ( $all_events as $event ) {
            if ( $event['hijri_month'] === $month && $event['hijri_day'] >= $day ) {
                $upcoming[] = $event;
            }
        }

        // Events in upcoming months
        for ( $m = $month + 1; $m <= 12; $m++ ) {
            foreach ( $all_events as $event ) {
                if ( $event['hijri_month'] === $m ) {
                    $upcoming[] = $event;
                }
            }
        }

        // Events in next year (months 1 to current month - 1)
        for ( $m = 1; $m < $month; $m++ ) {
            foreach ( $all_events as $event ) {
                if ( $event['hijri_month'] === $m ) {
                    $upcoming[] = $event;
                }
            }
        }

        // Events before today in current month (for next year)
        foreach ( $all_events as $event ) {
            if ( $event['hijri_month'] === $month && $event['hijri_day'] < $day ) {
                $upcoming[] = $event;
            }
        }

        return array_slice( $upcoming, 0, $limit );
    }

    /**
     * Get event by ID
     *
     * @param int $post_id Post ID.
     * @return array|null
     */
    public static function get_by_id( $post_id ) {
        $post = get_post( $post_id );

        if ( ! $post || self::POST_TYPE !== $post->post_type ) {
            return null;
        }

        return self::format_event_data( $post );
    }

    /**
     * Format event data for API/display
     *
     * @param WP_Post $post Post object.
     * @return array
     */
    public static function format_event_data( $post ) {
        $thumbnail    = get_post_thumbnail_id( $post->ID );
        $thumbnail_url = $thumbnail ? wp_get_attachment_image_url( $thumbnail, 'large' ) : null;

        return array(
            'id'             => $post->ID,
            'event_name_en'  => get_field( 'event_name_en', $post->ID ),
            'event_name_ar'  => get_field( 'event_name_ar', $post->ID ),
            'hijri_day'      => (int) get_field( 'hijri_day', $post->ID ),
            'hijri_month'    => (int) get_field( 'hijri_month', $post->ID ),
            'hijri_month_name_en' => Hilal_Hijri_Date::get_month_name( get_field( 'hijri_month', $post->ID ), 'en' ),
            'hijri_month_name_ar' => Hilal_Hijri_Date::get_month_name( get_field( 'hijri_month', $post->ID ), 'ar' ),
            'is_recurring'   => (bool) get_field( 'is_recurring', $post->ID ),
            'description_en' => get_field( 'description_en', $post->ID ),
            'description_ar' => get_field( 'description_ar', $post->ID ),
            'event_category' => get_field( 'event_category', $post->ID ),
            'duration_days'  => (int) get_field( 'duration_days', $post->ID ) ?: 1,
            'thumbnail'      => $thumbnail_url,
        );
    }

    /**
     * Create default Islamic events
     *
     * @return array Created post IDs.
     */
    public static function create_default_events() {
        $default_events = array(
            array(
                'event_name_en'  => 'Islamic New Year',
                'event_name_ar'  => 'رأس السنة الهجرية',
                'hijri_day'      => 1,
                'hijri_month'    => 1,
                'description_en' => 'The first day of Muharram marks the beginning of the Islamic New Year.',
                'description_ar' => 'اليوم الأول من محرم يمثل بداية السنة الهجرية الجديدة.',
                'event_category' => 'historical',
            ),
            array(
                'event_name_en'  => 'Day of Ashura',
                'event_name_ar'  => 'يوم عاشوراء',
                'hijri_day'      => 10,
                'hijri_month'    => 1,
                'description_en' => 'The tenth day of Muharram, a significant day of fasting.',
                'description_ar' => 'اليوم العاشر من محرم، يوم صيام مهم.',
                'event_category' => 'fasting',
            ),
            array(
                'event_name_en'  => 'Mawlid al-Nabi (Prophet\'s Birthday)',
                'event_name_ar'  => 'المولد النبوي الشريف',
                'hijri_day'      => 12,
                'hijri_month'    => 3,
                'description_en' => 'The birthday of Prophet Muhammad (peace be upon him).',
                'description_ar' => 'ذكرى مولد النبي محمد صلى الله عليه وسلم.',
                'event_category' => 'historical',
            ),
            array(
                'event_name_en'  => 'Isra and Mi\'raj',
                'event_name_ar'  => 'الإسراء والمعراج',
                'hijri_day'      => 27,
                'hijri_month'    => 7,
                'description_en' => 'The Night Journey of Prophet Muhammad (peace be upon him).',
                'description_ar' => 'ذكرى رحلة الإسراء والمعراج للنبي محمد صلى الله عليه وسلم.',
                'event_category' => 'special_night',
            ),
            array(
                'event_name_en'  => 'Beginning of Ramadan',
                'event_name_ar'  => 'بداية شهر رمضان',
                'hijri_day'      => 1,
                'hijri_month'    => 9,
                'description_en' => 'The first day of Ramadan, the month of fasting.',
                'description_ar' => 'اليوم الأول من رمضان، شهر الصيام.',
                'event_category' => 'fasting',
            ),
            array(
                'event_name_en'  => 'Laylat al-Qadr (Night of Power)',
                'event_name_ar'  => 'ليلة القدر',
                'hijri_day'      => 27,
                'hijri_month'    => 9,
                'description_en' => 'The Night of Power, better than a thousand months.',
                'description_ar' => 'ليلة القدر، خير من ألف شهر.',
                'event_category' => 'special_night',
            ),
            array(
                'event_name_en'  => 'Eid al-Fitr',
                'event_name_ar'  => 'عيد الفطر',
                'hijri_day'      => 1,
                'hijri_month'    => 10,
                'description_en' => 'The Festival of Breaking the Fast, celebrating the end of Ramadan.',
                'description_ar' => 'عيد الفطر المبارك، احتفالاً بنهاية شهر رمضان.',
                'event_category' => 'eid',
                'duration_days'  => 3,
            ),
            array(
                'event_name_en'  => 'Day of Arafah',
                'event_name_ar'  => 'يوم عرفة',
                'hijri_day'      => 9,
                'hijri_month'    => 12,
                'description_en' => 'The Day of Arafah, the holiest day of the Islamic calendar.',
                'description_ar' => 'يوم عرفة، أقدس يوم في التقويم الإسلامي.',
                'event_category' => 'hajj',
            ),
            array(
                'event_name_en'  => 'Eid al-Adha',
                'event_name_ar'  => 'عيد الأضحى',
                'hijri_day'      => 10,
                'hijri_month'    => 12,
                'description_en' => 'The Festival of Sacrifice, commemorating Ibrahim\'s willingness to sacrifice his son.',
                'description_ar' => 'عيد الأضحى المبارك، إحياءً لذكرى استعداد النبي إبراهيم للتضحية بابنه.',
                'event_category' => 'eid',
                'duration_days'  => 4,
            ),
        );

        $created = array();

        foreach ( $default_events as $event_data ) {
            // Check if event already exists
            $existing = get_posts( array(
                'post_type'   => self::POST_TYPE,
                'meta_query'  => array(
                    'relation' => 'AND',
                    array( 'key' => 'hijri_day', 'value' => $event_data['hijri_day'] ),
                    array( 'key' => 'hijri_month', 'value' => $event_data['hijri_month'] ),
                ),
                'numberposts' => 1,
            ) );

            if ( ! empty( $existing ) ) {
                continue;
            }

            $post_id = wp_insert_post( array(
                'post_type'   => self::POST_TYPE,
                'post_title'  => $event_data['event_name_en'],
                'post_status' => 'publish',
            ) );

            if ( ! is_wp_error( $post_id ) ) {
                update_field( 'event_name_en', $event_data['event_name_en'], $post_id );
                update_field( 'event_name_ar', $event_data['event_name_ar'], $post_id );
                update_field( 'hijri_day', $event_data['hijri_day'], $post_id );
                update_field( 'hijri_month', $event_data['hijri_month'], $post_id );
                update_field( 'description_en', $event_data['description_en'], $post_id );
                update_field( 'description_ar', $event_data['description_ar'], $post_id );
                update_field( 'event_category', $event_data['event_category'], $post_id );
                update_field( 'is_recurring', true, $post_id );
                if ( isset( $event_data['duration_days'] ) ) {
                    update_field( 'duration_days', $event_data['duration_days'], $post_id );
                }

                $created[] = $post_id;
            }
        }

        return $created;
    }
}
