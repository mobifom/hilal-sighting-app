<?php
/**
 * SEO Functions for Hilal Theme
 *
 * Comprehensive SEO optimization for moon sighting in New Zealand
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * SEO Keywords Configuration
 */
function hilal_get_seo_keywords() {
    return array(
        // Primary Keywords
        'moon sighting New Zealand',
        'hilal sighting NZ',
        'crescent moon New Zealand',
        'Islamic moon sighting',
        'hilal New Zealand',

        // Ramadan Keywords
        'Ramadan start New Zealand',
        'Ramadan 2026 New Zealand',
        'when does Ramadan start NZ',
        'Ramadan moon sighting NZ',
        'beginning of Ramadan New Zealand',
        'Ramadan dates New Zealand',

        // Eid Keywords
        'Eid New Zealand',
        'Eid al-Fitr New Zealand',
        'Eid al-Adha New Zealand',
        'Eid 2026 NZ',
        'when is Eid New Zealand',
        'Eid dates NZ',
        'Eid ul Fitr NZ',
        'Eid ul Adha NZ',

        // Islamic Calendar Keywords
        'Islamic calendar New Zealand',
        'Hijri calendar NZ',
        'Islamic dates New Zealand',
        'Hijri dates NZ',
        'Muslim calendar New Zealand',

        // Prayer Times Keywords
        'prayer times New Zealand',
        'salah times NZ',
        'namaz times New Zealand',
        'Auckland prayer times',
        'Wellington prayer times',
        'Christchurch prayer times',
        'Muslim prayer times NZ',
        'fajr time New Zealand',
        'maghrib time NZ',

        // Qibla Keywords
        'Qibla direction New Zealand',
        'Qibla finder NZ',
        'Mecca direction New Zealand',
        'Kaaba direction NZ',

        // Mosque Keywords
        'mosques New Zealand',
        'masjid NZ',
        'Islamic centers New Zealand',
        'Muslim community NZ',

        // General Islamic Keywords
        'Muslims in New Zealand',
        'Islam New Zealand',
        'Islamic community NZ',
        'halal New Zealand',
    );
}

/**
 * Get page-specific meta description
 *
 * @return string
 */
function hilal_get_meta_description() {
    $description = 'Official moon sighting information for New Zealand Muslims. Get accurate Ramadan dates, Eid announcements, prayer times, and Islamic calendar.';

    if ( is_front_page() ) {
        $description = 'Hilal NZ - Official moon sighting platform for New Zealand. Accurate Islamic calendar, Ramadan and Eid dates, prayer times, Qibla direction, and crescent sighting reports.';
    } elseif ( is_page_template( 'page-calendar.php' ) ) {
        $description = 'Islamic Hijri Calendar for New Zealand. View confirmed moon sighting dates, upcoming Islamic months, Ramadan, and Eid dates for NZ Muslims.';
    } elseif ( is_page_template( 'page-prayer-times.php' ) ) {
        $description = 'Accurate prayer times for New Zealand mosques and cities. Find Fajr, Dhuhr, Asr, Maghrib, and Isha times for Auckland, Wellington, Christchurch, and more.';
    } elseif ( is_page_template( 'page-qibla.php' ) ) {
        $description = 'Find Qibla direction from anywhere in New Zealand. Use our compass to locate the direction of Kaaba in Mecca for your prayers.';
    } elseif ( is_page_template( 'page-announcements.php' ) ) {
        $description = 'Official announcements for New Zealand Muslims. Moon sighting confirmations, Ramadan start dates, Eid announcements, and Islamic event notifications.';
    } elseif ( is_page_template( 'page-sighting-report.php' ) ) {
        $description = 'Report a moon sighting in New Zealand. Submit your crescent moon observation to help determine Islamic month beginnings.';
    } elseif ( is_page_template( 'page-faq.php' ) ) {
        $description = 'Frequently asked questions about moon sighting, Islamic calendar, prayer times, and Qibla direction in New Zealand.';
    } elseif ( is_singular( 'announcement' ) ) {
        $description = wp_trim_words( get_field( 'body_en' ), 25, '...' );
    }

    return $description;
}

/**
 * Get page-specific title
 *
 * @return string
 */
function hilal_get_seo_title() {
    $site_name = 'Hilal NZ';

    if ( is_front_page() ) {
        return 'Moon Sighting New Zealand - Islamic Calendar, Ramadan & Eid Dates | ' . $site_name;
    } elseif ( is_page_template( 'page-calendar.php' ) ) {
        return 'Islamic Calendar New Zealand - Hijri Dates & Moon Sighting | ' . $site_name;
    } elseif ( is_page_template( 'page-prayer-times.php' ) ) {
        return 'Prayer Times New Zealand - Salah Times for NZ Mosques | ' . $site_name;
    } elseif ( is_page_template( 'page-qibla.php' ) ) {
        return 'Qibla Direction New Zealand - Find Mecca Direction | ' . $site_name;
    } elseif ( is_page_template( 'page-announcements.php' ) ) {
        return 'Moon Sighting Announcements NZ - Ramadan & Eid Dates | ' . $site_name;
    } elseif ( is_page_template( 'page-sighting-report.php' ) ) {
        return 'Report Moon Sighting - Hilal Observation New Zealand | ' . $site_name;
    } elseif ( is_page_template( 'page-faq.php' ) ) {
        return 'FAQ - Moon Sighting & Islamic Calendar Questions | ' . $site_name;
    }

    return get_the_title() . ' | ' . $site_name;
}

/**
 * Output SEO meta tags in head
 */
function hilal_output_seo_meta() {
    $keywords    = hilal_get_seo_keywords();
    $description = hilal_get_meta_description();
    $title       = hilal_get_seo_title();
    $url         = get_permalink();
    $site_name   = 'Hilal NZ';
    $image       = HILAL_THEME_URI . '/assets/images/hilal-og-image.png';

    // Check for featured image
    if ( is_singular() && has_post_thumbnail() ) {
        $image = get_the_post_thumbnail_url( get_the_ID(), 'large' );
    }
    ?>

    <!-- Primary Meta Tags -->
    <meta name="title" content="<?php echo esc_attr( $title ); ?>">
    <meta name="description" content="<?php echo esc_attr( $description ); ?>">
    <meta name="keywords" content="<?php echo esc_attr( implode( ', ', array_slice( $keywords, 0, 20 ) ) ); ?>">
    <meta name="author" content="Hilal NZ">
    <meta name="robots" content="index, follow">
    <meta name="language" content="English">
    <meta name="geo.region" content="NZ">
    <meta name="geo.country" content="New Zealand">

    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo esc_url( $url ); ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo esc_url( $url ); ?>">
    <meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
    <meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
    <meta property="og:image" content="<?php echo esc_url( $image ); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr( $site_name ); ?>">
    <meta property="og:locale" content="en_NZ">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo esc_url( $url ); ?>">
    <meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>">
    <meta name="twitter:image" content="<?php echo esc_url( $image ); ?>">

    <!-- Additional SEO Meta -->
    <meta name="theme-color" content="#D4AF37">
    <meta name="msapplication-TileColor" content="#D4AF37">
    <meta name="application-name" content="Hilal NZ">
    <meta name="apple-mobile-web-app-title" content="Hilal NZ">

    <?php
}
add_action( 'wp_head', 'hilal_output_seo_meta', 1 );

/**
 * Output JSON-LD structured data
 */
function hilal_output_structured_data() {
    $site_url  = home_url();
    $site_name = 'Hilal NZ';
    $logo      = HILAL_THEME_URI . '/assets/images/hilal-logo.png';

    // Organization Schema
    $organization = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'Organization',
        'name'        => $site_name,
        'url'         => $site_url,
        'logo'        => $logo,
        'description' => 'Official moon sighting platform for New Zealand Muslims. Providing accurate Islamic calendar, prayer times, and Qibla direction.',
        'address'     => array(
            '@type'           => 'PostalAddress',
            'addressCountry'  => 'NZ',
            'addressRegion'   => 'New Zealand',
        ),
        'sameAs'      => array(
            'https://facebook.com/hilalnz',
            'https://twitter.com/hilalnz',
            'https://instagram.com/hilalnz',
        ),
    );

    // WebSite Schema with SearchAction
    $website = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'WebSite',
        'name'            => $site_name,
        'url'             => $site_url,
        'description'     => 'Moon sighting and Islamic calendar platform for New Zealand',
        'potentialAction' => array(
            '@type'       => 'SearchAction',
            'target'      => $site_url . '/?s={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ),
    );

    echo '<script type="application/ld+json">' . wp_json_encode( $organization ) . '</script>' . "\n";
    echo '<script type="application/ld+json">' . wp_json_encode( $website ) . '</script>' . "\n";

    // Page-specific schemas
    if ( is_page_template( 'page-faq.php' ) ) {
        hilal_output_faq_schema();
    }

    if ( is_page_template( 'page-calendar.php' ) ) {
        hilal_output_calendar_schema();
    }

    if ( is_singular( 'announcement' ) ) {
        hilal_output_article_schema();
    }
}
add_action( 'wp_head', 'hilal_output_structured_data', 2 );

/**
 * Output FAQ structured data
 */
function hilal_output_faq_schema() {
    if ( ! class_exists( 'Hilal_FAQ' ) ) {
        return;
    }

    $faqs = Hilal_FAQ::get_faqs( array( 'posts_per_page' => 10 ) );

    if ( empty( $faqs ) ) {
        return;
    }

    $faq_schema = array(
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => array(),
    );

    foreach ( $faqs as $faq ) {
        $faq_schema['mainEntity'][] = array(
            '@type'          => 'Question',
            'name'           => $faq['question_en'],
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => wp_strip_all_tags( $faq['answer_en'] ),
            ),
        );
    }

    echo '<script type="application/ld+json">' . wp_json_encode( $faq_schema ) . '</script>' . "\n";
}

/**
 * Output Calendar/Event structured data
 */
function hilal_output_calendar_schema() {
    if ( ! class_exists( 'Hilal_Hijri_Month' ) || ! class_exists( 'Hilal_Hijri_Date' ) ) {
        return;
    }

    $current = Hilal_Hijri_Date::get_current_month_info();
    $year    = $current['year'] ?? (int) gmdate( 'Y' ) - 579; // Approximate Hijri year
    $months  = Hilal_Hijri_Month::get_year_months( $year );

    if ( empty( $months ) ) {
        return;
    }

    foreach ( array_slice( $months, 0, 3 ) as $month ) {
        $event_schema = array(
            '@context'    => 'https://schema.org',
            '@type'       => 'Event',
            'name'        => $month['month_name_en'] . ' ' . $month['hijri_year'] . ' AH',
            'description' => 'Beginning of ' . $month['month_name_en'] . ' in the Islamic calendar',
            'startDate'   => $month['gregorian_start'],
            'endDate'     => $month['gregorian_end'],
            'eventStatus' => 'https://schema.org/EventScheduled',
            'location'    => array(
                '@type'   => 'Place',
                'name'    => 'New Zealand',
                'address' => array(
                    '@type'          => 'PostalAddress',
                    'addressCountry' => 'NZ',
                ),
            ),
            'organizer'   => array(
                '@type' => 'Organization',
                'name'  => 'Hilal NZ',
                'url'   => home_url(),
            ),
        );

        echo '<script type="application/ld+json">' . wp_json_encode( $event_schema ) . '</script>' . "\n";
    }
}

/**
 * Output Article structured data for announcements
 */
function hilal_output_article_schema() {
    global $post;

    $article_schema = array(
        '@context'         => 'https://schema.org',
        '@type'            => 'NewsArticle',
        'headline'         => get_field( 'title_en', $post->ID ),
        'description'      => wp_trim_words( get_field( 'body_en', $post->ID ), 30, '...' ),
        'datePublished'    => get_the_date( 'c', $post ),
        'dateModified'     => get_the_modified_date( 'c', $post ),
        'author'           => array(
            '@type' => 'Organization',
            'name'  => 'Hilal NZ',
        ),
        'publisher'        => array(
            '@type' => 'Organization',
            'name'  => 'Hilal NZ',
            'logo'  => array(
                '@type' => 'ImageObject',
                'url'   => HILAL_THEME_URI . '/assets/images/hilal-logo.png',
            ),
        ),
        'mainEntityOfPage' => array(
            '@type' => '@WebPage',
            '@id'   => get_permalink( $post ),
        ),
    );

    if ( has_post_thumbnail( $post ) ) {
        $article_schema['image'] = get_the_post_thumbnail_url( $post, 'large' );
    }

    echo '<script type="application/ld+json">' . wp_json_encode( $article_schema ) . '</script>' . "\n";
}

/**
 * Add hreflang tags for multilingual SEO
 */
function hilal_output_hreflang_tags() {
    $url = get_permalink();
    ?>
    <link rel="alternate" hreflang="en" href="<?php echo esc_url( $url ); ?>">
    <link rel="alternate" hreflang="ar" href="<?php echo esc_url( add_query_arg( 'lang', 'ar', $url ) ); ?>">
    <link rel="alternate" hreflang="x-default" href="<?php echo esc_url( $url ); ?>">
    <?php
}
add_action( 'wp_head', 'hilal_output_hreflang_tags', 3 );

/**
 * Modify document title
 *
 * @param array $title Title parts.
 * @return array
 */
function hilal_document_title_parts( $title ) {
    if ( is_front_page() ) {
        $title['title'] = 'Moon Sighting New Zealand - Islamic Calendar, Ramadan & Eid Dates';
    }

    return $title;
}
add_filter( 'document_title_parts', 'hilal_document_title_parts' );

/**
 * Add SEO-friendly breadcrumbs
 */
function hilal_breadcrumbs() {
    if ( is_front_page() ) {
        return;
    }

    $breadcrumbs = array(
        array(
            'name' => 'Home',
            'url'  => home_url(),
        ),
    );

    if ( is_page() ) {
        $breadcrumbs[] = array(
            'name' => get_the_title(),
            'url'  => get_permalink(),
        );
    } elseif ( is_singular( 'announcement' ) ) {
        $breadcrumbs[] = array(
            'name' => 'Announcements',
            'url'  => home_url( '/announcements/' ),
        );
        $breadcrumbs[] = array(
            'name' => get_field( 'title_en' ),
            'url'  => get_permalink(),
        );
    }

    // Output breadcrumb schema
    $schema = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => array(),
    );

    foreach ( $breadcrumbs as $i => $crumb ) {
        $schema['itemListElement'][] = array(
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $crumb['name'],
            'item'     => $crumb['url'],
        );
    }

    echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";

    // Output HTML breadcrumbs
    echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
    echo '<ol itemscope itemtype="https://schema.org/BreadcrumbList">';

    foreach ( $breadcrumbs as $i => $crumb ) {
        $is_last = $i === count( $breadcrumbs ) - 1;
        echo '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';

        if ( $is_last ) {
            echo '<span itemprop="name">' . esc_html( $crumb['name'] ) . '</span>';
        } else {
            echo '<a itemprop="item" href="' . esc_url( $crumb['url'] ) . '"><span itemprop="name">' . esc_html( $crumb['name'] ) . '</span></a>';
            echo '<span class="separator">/</span>';
        }

        echo '<meta itemprop="position" content="' . ( $i + 1 ) . '">';
        echo '</li>';
    }

    echo '</ol>';
    echo '</nav>';
}

/**
 * Generate XML Sitemap entries
 */
function hilal_sitemap_entries( $post_type ) {
    $entries = array();

    // Add static pages
    $pages = array(
        'calendar'         => 0.9,
        'prayer-times'     => 0.9,
        'announcements'    => 0.8,
        'qibla'            => 0.7,
        'crescent-sighting' => 0.7,
        'faq'              => 0.6,
    );

    foreach ( $pages as $slug => $priority ) {
        $page = get_page_by_path( $slug );
        if ( $page ) {
            $entries[] = array(
                'loc'        => get_permalink( $page ),
                'lastmod'    => get_the_modified_date( 'c', $page ),
                'changefreq' => 'weekly',
                'priority'   => $priority,
            );
        }
    }

    return $entries;
}

/**
 * Add preconnect hints for performance
 */
function hilal_resource_hints( $urls, $relation_type ) {
    if ( 'preconnect' === $relation_type ) {
        $urls[] = array(
            'href' => 'https://fonts.googleapis.com',
        );
        $urls[] = array(
            'href' => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous',
        );
    }

    return $urls;
}
add_filter( 'wp_resource_hints', 'hilal_resource_hints', 10, 2 );

/**
 * Add security headers
 */
function hilal_security_headers() {
    if ( ! is_admin() ) {
        header( 'X-Content-Type-Options: nosniff' );
        header( 'X-Frame-Options: SAMEORIGIN' );
        header( 'X-XSS-Protection: 1; mode=block' );
        header( 'Referrer-Policy: strict-origin-when-cross-origin' );
    }
}
add_action( 'send_headers', 'hilal_security_headers' );
