<?php
/**
 * Hijri Month Custom Post Type
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Hijri Month Post Type Class
 */
class Hilal_Hijri_Month {

    /**
     * Post type slug
     *
     * @var string
     */
    const POST_TYPE = 'hijri_month';

    /**
     * Register the post type
     */
    public static function register() {
        $labels = array(
            'name'                  => _x( 'Hijri Months', 'Post type general name', 'hilal' ),
            'singular_name'         => _x( 'Hijri Month', 'Post type singular name', 'hilal' ),
            'menu_name'             => _x( 'Hijri Calendar', 'Admin Menu text', 'hilal' ),
            'name_admin_bar'        => _x( 'Hijri Month', 'Add New on Toolbar', 'hilal' ),
            'add_new'               => __( 'Add New', 'hilal' ),
            'add_new_item'          => __( 'Add New Hijri Month', 'hilal' ),
            'new_item'              => __( 'New Hijri Month', 'hilal' ),
            'edit_item'             => __( 'Edit Hijri Month', 'hilal' ),
            'view_item'             => __( 'View Hijri Month', 'hilal' ),
            'all_items'             => __( 'All Months', 'hilal' ),
            'search_items'          => __( 'Search Hijri Months', 'hilal' ),
            'parent_item_colon'     => __( 'Parent Hijri Month:', 'hilal' ),
            'not_found'             => __( 'No Hijri months found.', 'hilal' ),
            'not_found_in_trash'    => __( 'No Hijri months found in Trash.', 'hilal' ),
            'archives'              => __( 'Hijri Month Archives', 'hilal' ),
            'filter_items_list'     => __( 'Filter Hijri months list', 'hilal' ),
            'items_list_navigation' => __( 'Hijri months list navigation', 'hilal' ),
            'items_list'            => __( 'Hijri months list', 'hilal' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'hijri-month' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => array( 'title', 'thumbnail' ),
            'show_in_rest'       => true,
            'rest_base'          => 'hijri-months',
        );

        register_post_type( self::POST_TYPE, $args );

        // Register ACF fields - call directly if ACF is already loaded, otherwise hook
        if ( function_exists( 'acf_add_local_field_group' ) ) {
            self::register_acf_fields();
        } else {
            add_action( 'acf/init', array( __CLASS__, 'register_acf_fields' ) );
        }
    }

    /**
     * Register ACF fields for Hijri Month
     */
    public static function register_acf_fields() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group( array(
            'key'      => 'group_hijri_month',
            'title'    => __( 'Hijri Month Details', 'hilal' ),
            'fields'   => array(
                array(
                    'key'           => 'field_month_number',
                    'label'         => __( 'Month Number', 'hilal' ),
                    'name'          => 'month_number',
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
                    'key'           => 'field_hijri_year',
                    'label'         => __( 'Hijri Year', 'hilal' ),
                    'name'          => 'hijri_year',
                    'type'          => 'number',
                    'required'      => 1,
                    'min'           => 1400,
                    'max'           => 1500,
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_month_name_en',
                    'label'         => __( 'Month Name (English)', 'hilal' ),
                    'name'          => 'month_name_en',
                    'type'          => 'text',
                    'required'      => 1,
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_month_name_ar',
                    'label'         => __( 'Month Name (Arabic)', 'hilal' ),
                    'name'          => 'month_name_ar',
                    'type'          => 'text',
                    'required'      => 1,
                    'wrapper'       => array( 'width' => '50' ),
                ),
                array(
                    'key'           => 'field_gregorian_start',
                    'label'         => __( 'Gregorian Start Date', 'hilal' ),
                    'name'          => 'gregorian_start',
                    'type'          => 'date_picker',
                    'required'      => 1,
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                    'wrapper'       => array( 'width' => '33' ),
                ),
                array(
                    'key'           => 'field_gregorian_end',
                    'label'         => __( 'Gregorian End Date', 'hilal' ),
                    'name'          => 'gregorian_end',
                    'type'          => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                    'wrapper'       => array( 'width' => '33' ),
                ),
                array(
                    'key'           => 'field_days_count',
                    'label'         => __( 'Days Count', 'hilal' ),
                    'name'          => 'days_count',
                    'type'          => 'select',
                    'choices'       => array(
                        29 => '29 days',
                        30 => '30 days',
                    ),
                    'wrapper'       => array( 'width' => '33' ),
                ),
                array(
                    'key'           => 'field_status',
                    'label'         => __( 'Confirmation Status', 'hilal' ),
                    'name'          => 'status',
                    'type'          => 'select',
                    'required'      => 1,
                    'choices'       => array(
                        'estimated'       => __( 'Estimated', 'hilal' ),
                        'pending_sighting' => __( 'Pending Sighting', 'hilal' ),
                        'confirmed'       => __( 'Confirmed by Sighting', 'hilal' ),
                    ),
                    'default_value' => 'estimated',
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
     * Get all months for a specific Hijri year
     *
     * @param int $year Hijri year.
     * @return array
     */
    public static function get_year_months( $year ) {
        $args = array(
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => 12,
            'post_status'    => 'publish',
            'meta_key'       => 'hijri_year',
            'meta_value'     => $year,
            'orderby'        => 'meta_value_num',
            'meta_query'     => array(
                array(
                    'key'     => 'month_number',
                    'compare' => 'EXISTS',
                ),
            ),
        );

        // Add secondary ordering by month_number
        add_filter( 'posts_orderby', array( __CLASS__, 'order_by_month_number' ) );

        $query  = new WP_Query( $args );
        $months = array();

        remove_filter( 'posts_orderby', array( __CLASS__, 'order_by_month_number' ) );

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $months[] = self::format_month_data( $post );
            }
        }

        return $months;
    }

    /**
     * Order by month number
     *
     * @param string $orderby SQL orderby clause.
     * @return string
     */
    public static function order_by_month_number( $orderby ) {
        global $wpdb;
        return "(SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = {$wpdb->posts}.ID AND meta_key = 'month_number') + 0 ASC";
    }

    /**
     * Get current Hijri month
     *
     * @return array|null
     */
    public static function get_current_month() {
        $today = gmdate( 'Y-m-d' );

        $args = array(
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => 'gregorian_start',
                    'value'   => $today,
                    'compare' => '<=',
                    'type'    => 'DATE',
                ),
                array(
                    'key'     => 'gregorian_end',
                    'value'   => $today,
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
            ),
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            return self::format_month_data( $query->posts[0] );
        }

        return null;
    }

    /**
     * Format month data for API/display
     *
     * @param WP_Post $post Post object.
     * @return array
     */
    public static function format_month_data( $post ) {
        return array(
            'id'              => $post->ID,
            'month_number'    => (int) get_field( 'month_number', $post->ID ),
            'hijri_year'      => (int) get_field( 'hijri_year', $post->ID ),
            'month_name_en'   => get_field( 'month_name_en', $post->ID ),
            'month_name_ar'   => get_field( 'month_name_ar', $post->ID ),
            'gregorian_start' => get_field( 'gregorian_start', $post->ID ),
            'gregorian_end'   => get_field( 'gregorian_end', $post->ID ),
            'days_count'      => (int) get_field( 'days_count', $post->ID ),
            'status'          => get_field( 'status', $post->ID ),
        );
    }

    /**
     * Get upcoming months from the current month
     *
     * @param int $current_month Current Hijri month number (1-12).
     * @param int $count Number of months to return.
     * @return array Array of upcoming months.
     */
    public static function get_upcoming_months( $current_month = 1, $count = 5 ) {
        $today = gmdate( 'Y-m-d' );

        $args = array(
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => $count,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => 'gregorian_start',
                    'value'   => $today,
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
            ),
            'orderby'        => 'meta_value',
            'meta_key'       => 'gregorian_start',
            'order'          => 'ASC',
        );

        $query  = new WP_Query( $args );
        $months = array();

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $months[] = self::format_month_data( $post );
            }
        }

        // If no upcoming months found, return static data based on month numbers
        if ( empty( $months ) ) {
            $months_en = array(
                1 => 'Muharram', 2 => 'Safar', 3 => "Rabi' al-Awwal",
                4 => "Rabi' al-Thani", 5 => 'Jumada al-Awwal', 6 => 'Jumada al-Thani',
                7 => 'Rajab', 8 => "Sha'ban", 9 => 'Ramadan',
                10 => 'Shawwal', 11 => "Dhul Qi'dah", 12 => 'Dhul Hijjah',
            );
            $months_ar = array(
                1 => 'محرم', 2 => 'صفر', 3 => 'ربيع الأول',
                4 => 'ربيع الثاني', 5 => 'جمادى الأولى', 6 => 'جمادى الآخرة',
                7 => 'رجب', 8 => 'شعبان', 9 => 'رمضان',
                10 => 'شوال', 11 => 'ذو القعدة', 12 => 'ذو الحجة',
            );

            for ( $i = 0; $i < $count; $i++ ) {
                $month_num = ( ( $current_month - 1 + $i ) % 12 ) + 1;
                $months[] = array(
                    'id'            => 0,
                    'month_number'  => $month_num,
                    'month_name_en' => $months_en[ $month_num ],
                    'month_name_ar' => $months_ar[ $month_num ],
                    'status'        => 'estimated',
                );
            }
        }

        return $months;
    }

    /**
     * Create default months for a Hijri year
     *
     * @param int $year Hijri year.
     * @return array Created post IDs.
     */
    public static function create_year_months( $year ) {
        $months_en = Hilal_Hijri_Date::$months_en;
        $months_ar = Hilal_Hijri_Date::$months_ar;
        $created   = array();

        // Get estimated start date for the year
        $gregorian = Hilal_Hijri_Date::hijri_to_gregorian( $year, 1, 1 );
        $start     = new DateTime( sprintf( '%04d-%02d-%02d', $gregorian['year'], $gregorian['month'], $gregorian['day'] ) );

        for ( $month = 1; $month <= 12; $month++ ) {
            // Check if month already exists
            $existing = get_posts( array(
                'post_type'   => self::POST_TYPE,
                'meta_query'  => array(
                    'relation' => 'AND',
                    array( 'key' => 'hijri_year', 'value' => $year ),
                    array( 'key' => 'month_number', 'value' => $month ),
                ),
                'numberposts' => 1,
            ) );

            if ( ! empty( $existing ) ) {
                continue;
            }

            // Estimate 29 or 30 days alternating
            $days = ( $month % 2 === 1 ) ? 30 : 29;
            $end  = clone $start;
            $end->modify( '+' . ( $days - 1 ) . ' days' );

            $post_id = wp_insert_post( array(
                'post_type'   => self::POST_TYPE,
                'post_title'  => sprintf( '%s %d AH', $months_en[ $month ], $year ),
                'post_status' => 'publish',
            ) );

            if ( ! is_wp_error( $post_id ) ) {
                update_field( 'month_number', $month, $post_id );
                update_field( 'hijri_year', $year, $post_id );
                update_field( 'month_name_en', $months_en[ $month ], $post_id );
                update_field( 'month_name_ar', $months_ar[ $month ], $post_id );
                update_field( 'gregorian_start', $start->format( 'Y-m-d' ), $post_id );
                update_field( 'gregorian_end', $end->format( 'Y-m-d' ), $post_id );
                update_field( 'days_count', $days, $post_id );
                update_field( 'status', 'estimated', $post_id );

                $created[] = $post_id;
            }

            // Move to next month
            $start = clone $end;
            $start->modify( '+1 day' );
        }

        return $created;
    }
}
