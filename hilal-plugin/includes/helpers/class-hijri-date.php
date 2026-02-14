<?php
/**
 * Hijri Date Helper Class
 *
 * Provides Hijri date calculations and conversions.
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Hijri Date Helper
 */
class Hilal_Hijri_Date {

    /**
     * Hijri month names in English
     *
     * @var array
     */
    public static $months_en = array(
        1  => 'Muharram',
        2  => 'Safar',
        3  => 'Rabi\' al-Awwal',
        4  => 'Rabi\' al-Thani',
        5  => 'Jumada al-Awwal',
        6  => 'Jumada al-Thani',
        7  => 'Rajab',
        8  => 'Sha\'ban',
        9  => 'Ramadan',
        10 => 'Shawwal',
        11 => 'Dhul Qi\'dah',
        12 => 'Dhul Hijjah',
    );

    /**
     * Hijri month names in Arabic
     *
     * @var array
     */
    public static $months_ar = array(
        1  => 'محرم',
        2  => 'صفر',
        3  => 'ربيع الأول',
        4  => 'ربيع الثاني',
        5  => 'جمادى الأولى',
        6  => 'جمادى الآخرة',
        7  => 'رجب',
        8  => 'شعبان',
        9  => 'رمضان',
        10 => 'شوال',
        11 => 'ذو القعدة',
        12 => 'ذو الحجة',
    );

    /**
     * Convert Gregorian date to Hijri date (estimation)
     *
     * Note: This is an algorithmic approximation. Actual Hijri dates
     * depend on moon sighting and are stored in the hijri_month post type.
     *
     * @param int|null $year  Gregorian year.
     * @param int|null $month Gregorian month.
     * @param int|null $day   Gregorian day.
     * @return array Hijri date array with year, month, day.
     */
    public static function gregorian_to_hijri( $year = null, $month = null, $day = null ) {
        if ( is_null( $year ) ) {
            $year  = (int) gmdate( 'Y' );
            $month = (int) gmdate( 'n' );
            $day   = (int) gmdate( 'j' );
        }

        // Calculate Julian Day Number
        $jd = self::gregorian_to_jd( $year, $month, $day );

        // Convert to Hijri
        return self::jd_to_hijri( $jd );
    }

    /**
     * Convert Hijri date to Gregorian date (estimation)
     *
     * @param int $year  Hijri year.
     * @param int $month Hijri month.
     * @param int $day   Hijri day.
     * @return array Gregorian date array with year, month, day.
     */
    public static function hijri_to_gregorian( $year, $month, $day ) {
        $jd = self::hijri_to_jd( $year, $month, $day );
        return self::jd_to_gregorian( $jd );
    }

    /**
     * Get today's estimated Hijri date
     *
     * @return array
     */
    public static function get_today_hijri() {
        return self::gregorian_to_hijri();
    }

    /**
     * Get Hijri month name
     *
     * @param int    $month    Month number (1-12).
     * @param string $language Language code ('en' or 'ar').
     * @return string
     */
    public static function get_month_name( $month, $language = 'en' ) {
        $month  = (int) $month;
        $months = 'ar' === $language ? self::$months_ar : self::$months_en;

        return isset( $months[ $month ] ) ? $months[ $month ] : '';
    }

    /**
     * Format Hijri date as string
     *
     * @param int    $day      Day.
     * @param int    $month    Month.
     * @param int    $year     Year.
     * @param string $language Language code.
     * @return string
     */
    public static function format_date( $day, $month, $year, $language = 'en' ) {
        $month_name = self::get_month_name( $month, $language );

        if ( 'ar' === $language ) {
            // Arabic format: day month year
            $day_ar  = self::to_arabic_numerals( $day );
            $year_ar = self::to_arabic_numerals( $year );
            return sprintf( '%s %s %s هـ', $day_ar, $month_name, $year_ar );
        }

        return sprintf( '%d %s %d AH', $day, $month_name, $year );
    }

    /**
     * Convert number to Arabic numerals
     *
     * @param int|string $number Number to convert.
     * @return string
     */
    public static function to_arabic_numerals( $number ) {
        $western = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
        $arabic  = array( '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' );

        return str_replace( $western, $arabic, (string) $number );
    }

    /**
     * Convert Gregorian date to Julian Day Number
     *
     * @param int $year  Year.
     * @param int $month Month.
     * @param int $day   Day.
     * @return float
     */
    private static function gregorian_to_jd( $year, $month, $day ) {
        if ( $month <= 2 ) {
            $year--;
            $month += 12;
        }

        $a = floor( $year / 100 );
        $b = 2 - $a + floor( $a / 4 );

        return floor( 365.25 * ( $year + 4716 ) ) +
               floor( 30.6001 * ( $month + 1 ) ) +
               $day + $b - 1524.5;
    }

    /**
     * Convert Julian Day Number to Hijri date
     *
     * @param float $jd Julian Day Number.
     * @return array
     */
    private static function jd_to_hijri( $jd ) {
        $jd    = floor( $jd ) + 0.5;
        $l     = $jd - 1948439.5 + 10632;
        $n     = floor( ( $l - 1 ) / 10631 );
        $l     = $l - 10631 * $n + 354;
        $j     = floor( ( 10985 - $l ) / 5316 ) * floor( ( 50 * $l ) / 17719 ) +
                 floor( $l / 5670 ) * floor( ( 43 * $l ) / 15238 );
        $l     = $l - floor( ( 30 - $j ) / 15 ) * floor( ( 17719 * $j ) / 50 ) -
                 floor( $j / 16 ) * floor( ( 15238 * $j ) / 43 ) + 29;
        $month = floor( ( 24 * $l ) / 709 );
        $day   = $l - floor( ( 709 * $month ) / 24 );
        $year  = 30 * $n + $j - 30;

        return array(
            'year'  => (int) $year,
            'month' => (int) $month,
            'day'   => (int) $day,
        );
    }

    /**
     * Convert Hijri date to Julian Day Number
     *
     * @param int $year  Hijri year.
     * @param int $month Hijri month.
     * @param int $day   Hijri day.
     * @return float
     */
    private static function hijri_to_jd( $year, $month, $day ) {
        return floor( ( 11 * $year + 3 ) / 30 ) +
               354 * $year +
               30 * $month -
               floor( ( $month - 1 ) / 2 ) +
               $day + 1948440 - 385;
    }

    /**
     * Convert Julian Day Number to Gregorian date
     *
     * @param float $jd Julian Day Number.
     * @return array
     */
    private static function jd_to_gregorian( $jd ) {
        $z = floor( $jd + 0.5 );
        $f = ( $jd + 0.5 ) - $z;

        if ( $z < 2299161 ) {
            $a = $z;
        } else {
            $alpha = floor( ( $z - 1867216.25 ) / 36524.25 );
            $a     = $z + 1 + $alpha - floor( $alpha / 4 );
        }

        $b     = $a + 1524;
        $c     = floor( ( $b - 122.1 ) / 365.25 );
        $d     = floor( 365.25 * $c );
        $e     = floor( ( $b - $d ) / 30.6001 );
        $day   = $b - $d - floor( 30.6001 * $e ) + $f;
        $month = ( $e < 14 ) ? $e - 1 : $e - 13;
        $year  = ( $month > 2 ) ? $c - 4716 : $c - 4715;

        return array(
            'year'  => (int) $year,
            'month' => (int) $month,
            'day'   => (int) floor( $day ),
        );
    }

    /**
     * Get the confirmed Hijri month from database for a given Gregorian date
     *
     * @param string $gregorian_date Date in Y-m-d format.
     * @return WP_Post|null
     */
    public static function get_confirmed_month_for_date( $gregorian_date ) {
        $args = array(
            'post_type'      => 'hijri_month',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => 'gregorian_start',
                    'value'   => $gregorian_date,
                    'compare' => '<=',
                    'type'    => 'DATE',
                ),
                array(
                    'key'     => 'gregorian_end',
                    'value'   => $gregorian_date,
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
            ),
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            return $query->posts[0];
        }

        return null;
    }

    /**
     * Get current Hijri month with confirmation status
     *
     * @return array
     */
    public static function get_current_month_info() {
        $today = gmdate( 'Y-m-d' );

        // First check if we have a confirmed month in the database
        $confirmed_month = self::get_confirmed_month_for_date( $today );

        if ( $confirmed_month ) {
            $gregorian_start = get_field( 'gregorian_start', $confirmed_month->ID );
            $start_date      = new DateTime( $gregorian_start );
            $today_date      = new DateTime( $today );
            $diff            = $start_date->diff( $today_date );
            $day             = $diff->days + 1;

            return array(
                'source'          => 'confirmed',
                'year'            => (int) get_field( 'hijri_year', $confirmed_month->ID ),
                'month'           => (int) get_field( 'month_number', $confirmed_month->ID ),
                'day'             => $day,
                'month_name_en'   => get_field( 'month_name_en', $confirmed_month->ID ),
                'month_name_ar'   => get_field( 'month_name_ar', $confirmed_month->ID ),
                'status'          => get_field( 'status', $confirmed_month->ID ),
                'gregorian_start' => $gregorian_start,
                'post_id'         => $confirmed_month->ID,
            );
        }

        // Fall back to calculated date
        $hijri = self::get_today_hijri();

        return array(
            'source'        => 'calculated',
            'year'          => $hijri['year'],
            'month'         => $hijri['month'],
            'day'           => $hijri['day'],
            'month_name_en' => self::get_month_name( $hijri['month'], 'en' ),
            'month_name_ar' => self::get_month_name( $hijri['month'], 'ar' ),
            'status'        => 'estimated',
            'post_id'       => null,
        );
    }
}
