<?php
/**
 * Customizer Options
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register customizer options
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function hilal_customize_register( $wp_customize ) {
    // Hilal Settings Section
    $wp_customize->add_section( 'hilal_settings', array(
        'title'    => __( 'Hilal Settings', 'hilal' ),
        'priority' => 30,
    ) );

    // Default Language
    $wp_customize->add_setting( 'hilal_default_language', array(
        'default'           => 'en',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'hilal_default_language', array(
        'label'    => __( 'Default Language', 'hilal' ),
        'section'  => 'hilal_settings',
        'type'     => 'select',
        'choices'  => array(
            'en' => __( 'English', 'hilal' ),
            'ar' => __( 'Arabic', 'hilal' ),
        ),
    ) );

    // Primary Color
    $wp_customize->add_setting( 'hilal_primary_color', array(
        'default'           => '#1a5f4a',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hilal_primary_color', array(
        'label'    => __( 'Primary Color', 'hilal' ),
        'section'  => 'hilal_settings',
    ) ) );

    // Secondary Color
    $wp_customize->add_setting( 'hilal_secondary_color', array(
        'default'           => '#c9a227',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hilal_secondary_color', array(
        'label'    => __( 'Secondary Color', 'hilal' ),
        'section'  => 'hilal_settings',
    ) ) );

    // Show Prayer Times Widget
    $wp_customize->add_setting( 'hilal_show_prayer_widget', array(
        'default'           => true,
        'sanitize_callback' => 'hilal_sanitize_checkbox',
    ) );

    $wp_customize->add_control( 'hilal_show_prayer_widget', array(
        'label'    => __( 'Show Prayer Times on Home', 'hilal' ),
        'section'  => 'hilal_settings',
        'type'     => 'checkbox',
    ) );

    // Contact Email
    $wp_customize->add_setting( 'hilal_contact_email', array(
        'default'           => get_option( 'admin_email' ),
        'sanitize_callback' => 'sanitize_email',
    ) );

    $wp_customize->add_control( 'hilal_contact_email', array(
        'label'    => __( 'Contact Email', 'hilal' ),
        'section'  => 'hilal_settings',
        'type'     => 'email',
    ) );

    // Footer Copyright Text
    $wp_customize->add_setting( 'hilal_footer_text', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'hilal_footer_text', array(
        'label'       => __( 'Footer Copyright Text', 'hilal' ),
        'description' => __( 'Leave empty for default text.', 'hilal' ),
        'section'     => 'hilal_settings',
        'type'        => 'text',
    ) );

    // Page Titles Section
    $wp_customize->add_section( 'hilal_page_titles', array(
        'title'    => __( 'Page Titles', 'hilal' ),
        'priority' => 32,
    ) );

    // Calendar Page Title
    $wp_customize->add_setting( 'hilal_calendar_title', array(
        'default'           => 'Hijri Calendar',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'hilal_calendar_title', array(
        'label'   => __( 'Calendar Page Title', 'hilal' ),
        'section' => 'hilal_page_titles',
        'type'    => 'text',
    ) );

    // Calendar Page Subtitle
    $wp_customize->add_setting( 'hilal_calendar_subtitle', array(
        'default'           => 'Complete year with Gregorian dates',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'hilal_calendar_subtitle', array(
        'label'   => __( 'Calendar Page Subtitle', 'hilal' ),
        'section' => 'hilal_page_titles',
        'type'    => 'text',
    ) );

    // Announcements Page Title
    $wp_customize->add_setting( 'hilal_announcements_title', array(
        'default'           => 'Announcements',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'hilal_announcements_title', array(
        'label'   => __( 'Announcements Page Title', 'hilal' ),
        'section' => 'hilal_page_titles',
        'type'    => 'text',
    ) );

    // Sightings Page Title
    $wp_customize->add_setting( 'hilal_sightings_title', array(
        'default'           => 'Crescent Sightings',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'hilal_sightings_title', array(
        'label'   => __( 'Sightings Page Title', 'hilal' ),
        'section' => 'hilal_page_titles',
        'type'    => 'text',
    ) );

    // Social Media Section
    $wp_customize->add_section( 'hilal_social', array(
        'title'    => __( 'Social Media Links', 'hilal' ),
        'priority' => 35,
    ) );

    // Facebook
    $wp_customize->add_setting( 'hilal_facebook', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'hilal_facebook', array(
        'label'   => __( 'Facebook URL', 'hilal' ),
        'section' => 'hilal_social',
        'type'    => 'url',
    ) );

    // Twitter
    $wp_customize->add_setting( 'hilal_twitter', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'hilal_twitter', array(
        'label'   => __( 'Twitter URL', 'hilal' ),
        'section' => 'hilal_social',
        'type'    => 'url',
    ) );

    // Instagram
    $wp_customize->add_setting( 'hilal_instagram', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'hilal_instagram', array(
        'label'   => __( 'Instagram URL', 'hilal' ),
        'section' => 'hilal_social',
        'type'    => 'url',
    ) );

    // WhatsApp
    $wp_customize->add_setting( 'hilal_whatsapp', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'hilal_whatsapp', array(
        'label'       => __( 'WhatsApp Number', 'hilal' ),
        'description' => __( 'Include country code, e.g., +64123456789', 'hilal' ),
        'section'     => 'hilal_social',
        'type'        => 'text',
    ) );
}
add_action( 'customize_register', 'hilal_customize_register' );

/**
 * Sanitize checkbox value
 *
 * @param bool $checked Checkbox value.
 * @return bool
 */
function hilal_sanitize_checkbox( $checked ) {
    return ( isset( $checked ) && true === $checked ) ? true : false;
}

/**
 * Output custom CSS from customizer settings
 */
function hilal_customizer_css() {
    $primary   = get_theme_mod( 'hilal_primary_color', '#1a5f4a' );
    $secondary = get_theme_mod( 'hilal_secondary_color', '#c9a227' );

    if ( '#1a5f4a' !== $primary || '#c9a227' !== $secondary ) {
        ?>
        <style type="text/css">
            :root {
                <?php if ( '#1a5f4a' !== $primary ) : ?>
                    --hilal-primary: <?php echo esc_attr( $primary ); ?>;
                <?php endif; ?>
                <?php if ( '#c9a227' !== $secondary ) : ?>
                    --hilal-secondary: <?php echo esc_attr( $secondary ); ?>;
                <?php endif; ?>
            }
        </style>
        <?php
    }
}
add_action( 'wp_head', 'hilal_customizer_css' );
