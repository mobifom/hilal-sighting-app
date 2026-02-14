<?php
/**
 * Hilal Theme Functions
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Theme constants
define( 'HILAL_THEME_VERSION', '1.0.0' );
define( 'HILAL_THEME_DIR', get_template_directory() );
define( 'HILAL_THEME_URI', get_template_directory_uri() );

/**
 * Theme Setup
 */
function hilal_theme_setup() {
    // Add theme support
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'customize-selective-refresh-widgets' );

    // Custom logo support
    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 100,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    // Register navigation menus
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'hilal' ),
        'footer'  => __( 'Footer Menu', 'hilal' ),
    ) );

    // Load text domain
    load_theme_textdomain( 'hilal', HILAL_THEME_DIR . '/languages' );

    // Set content width
    global $content_width;
    if ( ! isset( $content_width ) ) {
        $content_width = 1200;
    }
}
add_action( 'after_setup_theme', 'hilal_theme_setup' );

/**
 * Enqueue Scripts and Styles
 */
function hilal_enqueue_assets() {
    // Main stylesheet
    wp_enqueue_style(
        'hilal-style',
        get_stylesheet_uri(),
        array(),
        HILAL_THEME_VERSION
    );

    // Google Fonts (Arabic support)
    wp_enqueue_style(
        'hilal-fonts',
        'https://fonts.googleapis.com/css2?family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap',
        array(),
        null
    );


    // Main JavaScript
    wp_enqueue_script(
        'hilal-main',
        HILAL_THEME_URI . '/assets/js/main.js',
        array(),
        HILAL_THEME_VERSION,
        true
    );

    // Localize script with data
    wp_localize_script( 'hilal-main', 'hilalData', array(
        'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
        'restUrl'  => rest_url( 'hilal/v1/' ),
        'nonce'    => wp_create_nonce( 'wp_rest' ),
        'language' => hilal_get_language(),
        'strings'  => array(
            'loading'     => __( 'Loading...', 'hilal' ),
            'error'       => __( 'An error occurred. Please try again.', 'hilal' ),
            'locationDenied' => __( 'Location access was denied.', 'hilal' ),
        ),
    ) );

    // Prayer times page
    if ( is_page_template( 'page-prayer-times.php' ) ) {
        wp_enqueue_script(
            'hilal-prayer-times',
            HILAL_THEME_URI . '/assets/js/prayer-times.js',
            array( 'hilal-main' ),
            HILAL_THEME_VERSION,
            true
        );
    }

    // Qibla page
    if ( is_page_template( 'page-qibla.php' ) ) {
        wp_enqueue_script(
            'hilal-qibla',
            HILAL_THEME_URI . '/assets/js/qibla.js',
            array( 'hilal-main' ),
            HILAL_THEME_VERSION,
            true
        );
    }

    // Sighting report form
    if ( is_page_template( 'page-sighting-report.php' ) ) {
        wp_enqueue_script(
            'hilal-sighting-form',
            HILAL_THEME_URI . '/assets/js/sighting-form.js',
            array( 'hilal-main' ),
            HILAL_THEME_VERSION,
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'hilal_enqueue_assets' );

/**
 * Check if current language is Arabic
 *
 * @return bool
 */
function hilal_is_arabic() {
    return false;
}

/**
 * Get current language
 *
 * @return string
 */
function hilal_get_language() {
    return 'en';
}

/**
 * Add body classes
 *
 * @param array $classes Body classes.
 * @return array
 */
function hilal_body_classes( $classes ) {
    $classes[] = 'lang-en';
    return $classes;
}
add_filter( 'body_class', 'hilal_body_classes' );

/**
 * Get bilingual field value
 *
 * @param string $field_name Field name without language suffix.
 * @param int    $post_id    Post ID.
 * @param string $lang       Language code.
 * @return string
 */
function hilal_get_bilingual_field( $field_name, $post_id = null, $lang = null ) {
    if ( is_null( $lang ) ) {
        $lang = hilal_get_language();
    }

    $field = $field_name . '_' . $lang;
    $value = get_field( $field, $post_id );

    // Fallback to English if Arabic not available
    if ( empty( $value ) && 'ar' === $lang ) {
        $value = get_field( $field_name . '_en', $post_id );
    }

    return $value;
}

/**
 * Display Hijri date
 *
 * @param string $lang Language code.
 */
function hilal_display_hijri_date( $lang = null ) {
    if ( is_null( $lang ) ) {
        $lang = hilal_get_language();
    }

    if ( ! class_exists( 'Hilal_Hijri_Date' ) ) {
        return;
    }

    $current = Hilal_Hijri_Date::get_current_month_info();

    $formatted = Hilal_Hijri_Date::format_date(
        $current['day'],
        $current['month'],
        $current['year'],
        $lang
    );

    echo esc_html( $formatted );
}

/**
 * Get Hijri date info
 *
 * @return array
 */
function hilal_get_hijri_date_info() {
    if ( ! class_exists( 'Hilal_Hijri_Date' ) ) {
        return array();
    }

    return Hilal_Hijri_Date::get_current_month_info();
}

/**
 * Register widget areas
 */
function hilal_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Sidebar', 'hilal' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Add widgets here.', 'hilal' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer 1', 'hilal' ),
        'id'            => 'footer-1',
        'description'   => __( 'First footer widget area.', 'hilal' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer 2', 'hilal' ),
        'id'            => 'footer-2',
        'description'   => __( 'Second footer widget area.', 'hilal' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );
}
add_action( 'widgets_init', 'hilal_widgets_init' );

/**
 * Customize excerpt length
 *
 * @param int $length Excerpt length.
 * @return int
 */
function hilal_excerpt_length( $length ) {
    return 20;
}
add_filter( 'excerpt_length', 'hilal_excerpt_length' );


/**
 * Convert Western numerals to Arabic numerals
 *
 * @param mixed $number Number to convert.
 * @return string Arabic numerals.
 */
function hilal_convert_to_arabic_numerals( $number ) {
    $western = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
    $arabic  = array( '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' );

    return str_replace( $western, $arabic, (string) $number );
}

/**
 * Format date for display
 *
 * @param string $date Date string (Y-m-d format).
 * @param string $lang Language code (kept for backward compatibility).
 * @return string Formatted date.
 */
function hilal_format_date( $date, $lang = null ) {
    if ( empty( $date ) ) {
        return '';
    }

    $timestamp = strtotime( $date );
    if ( ! $timestamp ) {
        return $date;
    }

    return gmdate( 'M j, Y', $timestamp );
}

/**
 * Include template functions
 */
require_once HILAL_THEME_DIR . '/inc/template-functions.php';

/**
 * Include customizer options
 */
require_once HILAL_THEME_DIR . '/inc/customizer.php';

/**
 * Create required pages automatically
 */
function hilal_create_required_pages() {
    // Only run once
    if ( get_option( 'hilal_pages_created' ) ) {
        return;
    }

    $pages = array(
        array(
            'title'    => 'Crescent Sighting',
            'slug'     => 'crescent-sighting',
            'template' => 'page-sighting-report.php',
        ),
        array(
            'title'    => 'Crescent Sightings',
            'slug'     => 'crescent-sightings',
            'template' => 'page-sighting-reports.php',
        ),
        array(
            'title'    => 'Calendar',
            'slug'     => 'calendar',
            'template' => 'page-calendar.php',
        ),
        array(
            'title'    => 'Announcements',
            'slug'     => 'announcements',
            'template' => 'page-announcements.php',
        ),
        array(
            'title'    => 'Prayer Times',
            'slug'     => 'prayer-times',
            'template' => 'page-prayer-times.php',
        ),
        array(
            'title'    => 'Qibla',
            'slug'     => 'qibla',
            'template' => 'page-qibla.php',
        ),
    );

    foreach ( $pages as $page_data ) {
        // Check if page exists
        $existing = get_page_by_path( $page_data['slug'] );

        if ( ! $existing ) {
            $page_id = wp_insert_post( array(
                'post_title'   => $page_data['title'],
                'post_name'    => $page_data['slug'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ) );

            if ( $page_id && ! is_wp_error( $page_id ) ) {
                update_post_meta( $page_id, '_wp_page_template', $page_data['template'] );
            }
        }
    }

    // Mark as done
    update_option( 'hilal_pages_created', true );
}
add_action( 'init', 'hilal_create_required_pages' );
