<?php
/**
 * Theme Header
 *
 * @package Hilal
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary">
        <?php esc_html_e( 'Skip to content', 'hilal' ); ?>
    </a>

    <!-- Top Banner -->
    <div class="top-banner">
        <div class="banner-bg"></div>
        <div class="container">
            <div class="banner-content">
                <div class="banner-badge">
                    <span class="badge-dot"></span>
                    <span>Official</span>
                </div>
                <span class="banner-text">
                    <strong>Hilal NZ</strong> — Your trusted source for Islamic moon sighting in New Zealand
                </span>
                <a href="/announcements/" class="banner-btn">
                    View Announcements
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <header id="masthead" class="site-header">
        <div class="container">
            <div class="header-inner">
                <div class="site-branding">
                    <?php if ( has_custom_logo() ) : ?>
                        <?php the_custom_logo(); ?>
                    <?php else : ?>
                        <span class="site-logo">&#9790;</span>
                    <?php endif; ?>

                    <div class="site-title-wrap">
                        <h1 class="site-title">
                            <a href="/">Hilal</a>
                        </h1>
                    </div>
                </div>

                <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                    <span class="dashicons dashicons-menu"></span>
                </button>

                <nav id="site-navigation" class="main-navigation">
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'primary',
                        'menu_id'        => 'primary-menu',
                        'menu_class'     => 'nav-menu',
                        'container'      => false,
                        'fallback_cb'    => 'hilal_fallback_menu',
                    ) );
                    ?>

                </nav>
            </div>
        </div>
    </header>

    <main id="primary" class="site-main">
