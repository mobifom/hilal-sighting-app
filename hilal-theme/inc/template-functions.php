<?php
/**
 * Template Functions
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Fallback menu when no primary menu is set
 */
function hilal_fallback_menu() {
    $current_url = trailingslashit( home_url( add_query_arg( array(), $_SERVER['REQUEST_URI'] ) ) );
    $current_path = wp_parse_url( $current_url, PHP_URL_PATH );

    $menu_items = array(
        ''                     => 'Home',
        '/calendar/'           => 'Calendar',
        '/announcements/'      => 'Announcements',
        '/prayer-times/'       => 'Prayer Times',
        '/qibla/'              => 'Qibla',
        '/crescent-sightings/' => 'Sightings',
    );
    ?>
    <ul class="nav-menu">
        <?php foreach ( $menu_items as $path => $label ) :
            $url = '/' . ltrim( $path, '/' );
            $is_active = false;

            if ( $path === '' ) {
                $is_active = ( is_front_page() || is_home() );
                $url = '/';
            } else {
                $is_active = ( strpos( $current_path, $path ) !== false );
            }
        ?>
            <li class="<?php echo $is_active ? 'active' : ''; ?>">
                <a href="<?php echo esc_attr( $url ); ?>" class="<?php echo $is_active ? 'active' : ''; ?>">
                    <?php echo esc_html( $label ); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
}

/**
 * Display status badge
 *
 * @param string $status Status value.
 * @param string $lang   Language code.
 */
function hilal_status_badge( $status, $lang = null ) {
    if ( is_null( $lang ) ) {
        $lang = hilal_get_language();
    }

    $labels = array(
        'en' => array(
            'confirmed'        => 'Confirmed',
            'pending_sighting' => 'Pending',
            'estimated'        => 'Estimated',
            'pending'          => 'Pending Review',
            'approved'         => 'Approved',
            'rejected'         => 'Rejected',
        ),
        'ar' => array(
            'confirmed'        => 'مؤكد',
            'pending_sighting' => 'قيد الانتظار',
            'estimated'        => 'تقديري',
            'pending'          => 'قيد المراجعة',
            'approved'         => 'مقبول',
            'rejected'         => 'مرفوض',
        ),
    );

    $label = $labels[ $lang ][ $status ] ?? $status;

    printf(
        '<span class="status-badge %s">%s</span>',
        esc_attr( $status ),
        esc_html( $label )
    );
}

/**
 * Display priority badge
 *
 * @param string $priority Priority value.
 * @param string $lang     Language code.
 */
function hilal_priority_badge( $priority, $lang = null ) {
    if ( is_null( $lang ) ) {
        $lang = hilal_get_language();
    }

    $labels = array(
        'en' => array(
            'high'   => 'High',
            'medium' => 'Medium',
            'low'    => 'Low',
        ),
        'ar' => array(
            'high'   => 'عالي',
            'medium' => 'متوسط',
            'low'    => 'منخفض',
        ),
    );

    $icons = array(
        'high'   => '&#128308;',
        'medium' => '&#128993;',
        'low'    => '&#128994;',
    );

    $label = $labels[ $lang ][ $priority ] ?? $priority;
    $icon  = $icons[ $priority ] ?? '';

    printf(
        '<span class="priority-badge %s">%s %s</span>',
        esc_attr( $priority ),
        $icon,
        esc_html( $label )
    );
}

/**
 * Display announcement type badge
 *
 * @param string $type Type value.
 * @param string $lang Language code.
 */
function hilal_type_badge( $type, $lang = null ) {
    if ( is_null( $lang ) ) {
        $lang = hilal_get_language();
    }

    $labels = array(
        'en' => array(
            'month_start'   => 'Month Start',
            'moon_sighting' => 'Moon Sighting',
            'islamic_event' => 'Islamic Event',
            'general'       => 'General',
        ),
        'ar' => array(
            'month_start'   => 'بداية الشهر',
            'moon_sighting' => 'رؤية الهلال',
            'islamic_event' => 'مناسبة إسلامية',
            'general'       => 'عام',
        ),
    );

    $label = $labels[ $lang ][ $type ] ?? $type;

    printf(
        '<span class="type-badge %s">%s</span>',
        esc_attr( $type ),
        esc_html( $label )
    );
}

// Note: hilal_format_date is defined in functions.php - removed duplicate here

/**
 * Get prayer name translation
 *
 * @param string $prayer Prayer name.
 * @param string $lang   Language code.
 * @return string
 */
function hilal_get_prayer_name( $prayer, $lang = null ) {
    if ( is_null( $lang ) ) {
        $lang = hilal_get_language();
    }

    $names = array(
        'en' => array(
            'fajr'    => 'Fajr',
            'sunrise' => 'Sunrise',
            'dhuhr'   => 'Dhuhr',
            'asr'     => 'Asr',
            'maghrib' => 'Maghrib',
            'isha'    => 'Isha',
        ),
        'ar' => array(
            'fajr'    => 'الفجر',
            'sunrise' => 'الشروق',
            'dhuhr'   => 'الظهر',
            'asr'     => 'العصر',
            'maghrib' => 'المغرب',
            'isha'    => 'العشاء',
        ),
    );

    return $names[ $lang ][ $prayer ] ?? $prayer;
}

/**
 * Get sky condition translation
 *
 * @param string $condition Condition value.
 * @param string $lang      Language code.
 * @return string
 */
function hilal_get_sky_condition( $condition, $lang = null ) {
    if ( is_null( $lang ) ) {
        $lang = hilal_get_language();
    }

    $conditions = array(
        'en' => array(
            'clear'         => 'Clear',
            'partly_cloudy' => 'Partly Cloudy',
            'cloudy'        => 'Cloudy',
            'hazy'          => 'Hazy',
            'other'         => 'Other',
        ),
        'ar' => array(
            'clear'         => 'صافٍ',
            'partly_cloudy' => 'غائم جزئياً',
            'cloudy'        => 'غائم',
            'hazy'          => 'ضبابي',
            'other'         => 'أخرى',
        ),
    );

    return $conditions[ $lang ][ $condition ] ?? $condition;
}

/**
 * Get visibility method translation
 *
 * @param string $method Method value.
 * @param string $lang   Language code.
 * @return string
 */
function hilal_get_visibility_method( $method, $lang = null ) {
    if ( is_null( $lang ) ) {
        $lang = hilal_get_language();
    }

    $methods = array(
        'en' => array(
            'naked_eye'  => 'Naked Eye',
            'binoculars' => 'Binoculars',
            'telescope'  => 'Telescope',
        ),
        'ar' => array(
            'naked_eye'  => 'العين المجردة',
            'binoculars' => 'المنظار',
            'telescope'  => 'التلسكوب',
        ),
    );

    return $methods[ $lang ][ $method ] ?? $method;
}

/**
 * Check if user can submit sighting reports
 *
 * @return bool
 */
function hilal_can_submit_report() {
    return is_user_logged_in();
}

/**
 * Get login required message
 *
 * @param string $lang Language code.
 * @return string
 */
function hilal_login_required_message( $lang = null ) {
    if ( is_null( $lang ) ) {
        $lang = hilal_get_language();
    }

    if ( 'ar' === $lang ) {
        return 'يجب تسجيل الدخول لإرسال تقرير رؤية الهلال.';
    }

    return 'You must be logged in to submit a moon sighting report.';
}

/**
 * Share buttons
 *
 * @param string $title Title to share.
 * @param string $url   URL to share.
 */
function hilal_share_buttons( $title, $url ) {
    $encoded_title = urlencode( $title );
    $encoded_url   = urlencode( $url );
    ?>
    <div class="share-buttons">
        <a href="https://wa.me/?text=<?php echo $encoded_title . '%20' . $encoded_url; ?>"
           target="_blank" class="share-btn whatsapp" title="Share on WhatsApp">
            WhatsApp
        </a>
        <a href="https://twitter.com/intent/tweet?text=<?php echo $encoded_title; ?>&url=<?php echo $encoded_url; ?>"
           target="_blank" class="share-btn twitter" title="Share on Twitter">
            Twitter
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $encoded_url; ?>"
           target="_blank" class="share-btn facebook" title="Share on Facebook">
            Facebook
        </a>
        <a href="mailto:?subject=<?php echo $encoded_title; ?>&body=<?php echo $encoded_url; ?>"
           class="share-btn email" title="Share via Email">
            Email
        </a>
    </div>
    <?php
}
