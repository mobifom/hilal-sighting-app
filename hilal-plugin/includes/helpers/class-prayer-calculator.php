<?php
/**
 * Prayer Times Calculator
 *
 * Calculates Islamic prayer times based on location and date.
 * Implements standard calculation methods (MWL, ISNA, Egyptian, etc.)
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Prayer Calculator Class
 */
class Hilal_Prayer_Calculator {

    /**
     * AlAdhan API base URL
     */
    const ALADHAN_API_URL = 'https://api.aladhan.com/v1';

    /**
     * AlAdhan method IDs mapping
     *
     * @var array
     */
    private static $aladhan_methods = array(
        'mwl'       => 3,  // Muslim World League
        'isna'      => 2,  // Islamic Society of North America
        'egypt'     => 5,  // Egyptian General Authority of Survey
        'makkah'    => 4,  // Umm al-Qura, Makkah
        'karachi'   => 1,  // University of Islamic Sciences, Karachi
        'tehran'    => 7,  // Institute of Geophysics, University of Tehran
        'jafari'    => 0,  // Shia Ithna-Ashari
        'singapore' => 11, // Majlis Ugama Islam Singapura
    );

    /**
     * Calculation methods with their parameters
     *
     * @var array
     */
    private static $methods = array(
        'mwl'      => array(
            'name'       => 'Muslim World League',
            'fajr_angle' => 18,
            'isha_angle' => 17,
        ),
        'isna'     => array(
            'name'       => 'Islamic Society of North America',
            'fajr_angle' => 15,
            'isha_angle' => 15,
        ),
        'egypt'    => array(
            'name'       => 'Egyptian General Authority of Survey',
            'fajr_angle' => 19.5,
            'isha_angle' => 17.5,
        ),
        'makkah'   => array(
            'name'       => 'Umm al-Qura University, Makkah',
            'fajr_angle' => 18.5,
            'isha_angle' => 90, // 90 minutes after Maghrib
        ),
        'karachi'  => array(
            'name'       => 'University of Islamic Sciences, Karachi',
            'fajr_angle' => 18,
            'isha_angle' => 18,
        ),
        'tehran'   => array(
            'name'       => 'Institute of Geophysics, University of Tehran',
            'fajr_angle' => 17.7,
            'isha_angle' => 14,
        ),
        'jafari'   => array(
            'name'       => 'Shia Ithna Ashari (Jafari)',
            'fajr_angle' => 16,
            'isha_angle' => 14,
        ),
        'singapore' => array(
            'name'       => 'Islamic Religious Council of Singapore',
            'fajr_angle' => 20,
            'isha_angle' => 18,
        ),
    );

    /**
     * Asr calculation methods
     *
     * @var array
     */
    private static $asr_methods = array(
        'standard' => 1, // Shafi'i, Maliki, Hanbali
        'hanafi'   => 2, // Hanafi
    );

    /**
     * New Zealand major cities with coordinates
     *
     * @var array
     */
    public static $nz_cities = array(
        'auckland'      => array(
            'name'      => 'Auckland',
            'name_ar'   => 'أوكلاند',
            'lat'       => -36.8485,
            'lng'       => 174.7633,
            'timezone'  => 'Pacific/Auckland',
        ),
        'wellington'    => array(
            'name'      => 'Wellington',
            'name_ar'   => 'ولينغتون',
            'lat'       => -41.2865,
            'lng'       => 174.7762,
            'timezone'  => 'Pacific/Auckland',
        ),
        'christchurch'  => array(
            'name'      => 'Christchurch',
            'name_ar'   => 'كرايستشيرش',
            'lat'       => -43.5321,
            'lng'       => 172.6362,
            'timezone'  => 'Pacific/Auckland',
        ),
        'hamilton'      => array(
            'name'      => 'Hamilton',
            'name_ar'   => 'هاملتون',
            'lat'       => -37.7870,
            'lng'       => 175.2793,
            'timezone'  => 'Pacific/Auckland',
        ),
        'tauranga'      => array(
            'name'      => 'Tauranga',
            'name_ar'   => 'تاورانغا',
            'lat'       => -37.6878,
            'lng'       => 176.1651,
            'timezone'  => 'Pacific/Auckland',
        ),
        'dunedin'       => array(
            'name'      => 'Dunedin',
            'name_ar'   => 'دنيدن',
            'lat'       => -45.8788,
            'lng'       => 170.5028,
            'timezone'  => 'Pacific/Auckland',
        ),
        'palmerston'    => array(
            'name'      => 'Palmerston North',
            'name_ar'   => 'بالمرستون نورث',
            'lat'       => -40.3523,
            'lng'       => 175.6082,
            'timezone'  => 'Pacific/Auckland',
        ),
        'napier'        => array(
            'name'      => 'Napier',
            'name_ar'   => 'نابير',
            'lat'       => -39.4928,
            'lng'       => 176.9120,
            'timezone'  => 'Pacific/Auckland',
        ),
        'nelson'        => array(
            'name'      => 'Nelson',
            'name_ar'   => 'نيلسون',
            'lat'       => -41.2706,
            'lng'       => 173.2840,
            'timezone'  => 'Pacific/Auckland',
        ),
        'rotorua'       => array(
            'name'      => 'Rotorua',
            'name_ar'   => 'روتوروا',
            'lat'       => -38.1368,
            'lng'       => 176.2497,
            'timezone'  => 'Pacific/Auckland',
        ),
    );

    /**
     * Calculate prayer times for a given location and date
     *
     * @param float  $latitude  Latitude.
     * @param float  $longitude Longitude.
     * @param string $date      Date in Y-m-d format.
     * @param string $method    Calculation method.
     * @param string $asr       Asr calculation method ('standard' or 'hanafi').
     * @param string $timezone  Timezone string.
     * @return array
     */
    public static function calculate( $latitude, $longitude, $date = null, $method = 'mwl', $asr = 'standard', $timezone = 'Pacific/Auckland' ) {
        if ( is_null( $date ) ) {
            $date = gmdate( 'Y-m-d' );
        }

        $date_parts = explode( '-', $date );
        $year       = (int) $date_parts[0];
        $month      = (int) $date_parts[1];
        $day        = (int) $date_parts[2];

        // Get method parameters
        $method_params = isset( self::$methods[ $method ] ) ? self::$methods[ $method ] : self::$methods['mwl'];
        $asr_factor    = isset( self::$asr_methods[ $asr ] ) ? self::$asr_methods[ $asr ] : 1;

        // Calculate Julian Date
        $jd = self::julian_date( $year, $month, $day );

        // Calculate sun parameters
        $sun = self::sun_position( $jd );

        // Calculate prayer times in hours
        $times = array();

        // Fajr
        $times['fajr'] = self::compute_time(
            $latitude,
            $sun['declination'],
            $sun['equation'],
            180 - $method_params['fajr_angle'],
            true
        );

        // Sunrise
        $times['sunrise'] = self::compute_time(
            $latitude,
            $sun['declination'],
            $sun['equation'],
            180 - 0.833,
            true
        );

        // Dhuhr (noon)
        $times['dhuhr'] = 12 + $sun['equation'];

        // Asr
        $times['asr'] = self::compute_asr_time(
            $latitude,
            $sun['declination'],
            $sun['equation'],
            $asr_factor
        );

        // Maghrib (sunset)
        $times['maghrib'] = self::compute_time(
            $latitude,
            $sun['declination'],
            $sun['equation'],
            0.833,
            false
        );

        // Isha
        if ( $method_params['isha_angle'] >= 90 ) {
            // Isha is X minutes after Maghrib
            $times['isha'] = $times['maghrib'] + ( $method_params['isha_angle'] / 60 );
        } else {
            $times['isha'] = self::compute_time(
                $latitude,
                $sun['declination'],
                $sun['equation'],
                $method_params['isha_angle'],
                false
            );
        }

        // Adjust for timezone
        $tz          = new DateTimeZone( $timezone );
        $dt          = new DateTime( $date, $tz );
        $offset      = $tz->getOffset( $dt ) / 3600;
        $lng_diff    = $longitude / 15;

        foreach ( $times as $prayer => $time ) {
            $times[ $prayer ] = $time - $lng_diff + $offset;
        }

        // Format times
        $formatted = array();
        foreach ( $times as $prayer => $hours ) {
            $formatted[ $prayer ] = self::format_time( $hours );
        }

        return array(
            'date'       => $date,
            'location'   => array(
                'latitude'  => $latitude,
                'longitude' => $longitude,
                'timezone'  => $timezone,
            ),
            'method'     => array(
                'id'   => $method,
                'name' => $method_params['name'],
            ),
            'times'      => $formatted,
            'times_24h'  => array_map( array( __CLASS__, 'format_time_24h' ), $times ),
        );
    }

    /**
     * Fetch prayer times from AlAdhan.com API
     *
     * @param float  $latitude  Latitude.
     * @param float  $longitude Longitude.
     * @param string $date      Date in Y-m-d format.
     * @param string $method    Calculation method.
     * @param string $timezone  Timezone string.
     * @return array|WP_Error
     */
    public static function fetch_from_aladhan( $latitude, $longitude, $date = null, $method = 'mwl', $timezone = 'Pacific/Auckland' ) {
        if ( is_null( $date ) ) {
            $date = gmdate( 'Y-m-d' );
        }

        // Get AlAdhan method ID
        $method_id = isset( self::$aladhan_methods[ $method ] ) ? self::$aladhan_methods[ $method ] : 3;

        // Check cache first
        $cache_key = 'hilal_aladhan_' . md5( $latitude . $longitude . $date . $method );
        $cached    = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached;
        }

        // Convert date to timestamp
        $timestamp = strtotime( $date );

        // Build API URL
        $url = add_query_arg(
            array(
                'latitude'  => $latitude,
                'longitude' => $longitude,
                'method'    => $method_id,
                'timezone'  => $timezone,
            ),
            self::ALADHAN_API_URL . '/timings/' . $timestamp
        );

        // Make API request
        $response = wp_remote_get( $url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/json',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            // Fall back to local calculation
            return self::calculate( $latitude, $longitude, $date, $method, 'standard', $timezone );
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $code ) {
            // Fall back to local calculation
            return self::calculate( $latitude, $longitude, $date, $method, 'standard', $timezone );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( empty( $data ) || ! isset( $data['data']['timings'] ) ) {
            // Fall back to local calculation
            return self::calculate( $latitude, $longitude, $date, $method, 'standard', $timezone );
        }

        $api_times = $data['data']['timings'];
        $meta      = $data['data']['meta'];

        // Format times from API (they come in 24h format like "05:14")
        $times_24h = array(
            'fajr'    => self::extract_time( $api_times['Fajr'] ),
            'sunrise' => self::extract_time( $api_times['Sunrise'] ),
            'dhuhr'   => self::extract_time( $api_times['Dhuhr'] ),
            'asr'     => self::extract_time( $api_times['Asr'] ),
            'maghrib' => self::extract_time( $api_times['Maghrib'] ),
            'isha'    => self::extract_time( $api_times['Isha'] ),
        );

        // Format to 12h for display
        $times = array();
        foreach ( $times_24h as $prayer => $time_24h ) {
            $times[ $prayer ] = self::convert_24h_to_12h( $time_24h );
        }

        $method_params = isset( self::$methods[ $method ] ) ? self::$methods[ $method ] : self::$methods['mwl'];

        $result = array(
            'date'       => $date,
            'location'   => array(
                'latitude'  => $latitude,
                'longitude' => $longitude,
                'timezone'  => $timezone,
            ),
            'method'     => array(
                'id'   => $method,
                'name' => $method_params['name'],
            ),
            'times'      => $times,
            'times_24h'  => $times_24h,
            'source'     => 'aladhan',
        );

        // Cache for 6 hours
        set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );

        return $result;
    }

    /**
     * Extract time from AlAdhan format (may contain timezone info)
     *
     * @param string $time Time string like "05:14" or "05:14 (NZDT)".
     * @return string Time in HH:MM format.
     */
    private static function extract_time( $time ) {
        // Remove timezone info if present
        $time = preg_replace( '/\s*\([^)]+\)\s*/', '', $time );
        return trim( $time );
    }

    /**
     * Convert 24h time to 12h format
     *
     * @param string $time_24h Time in HH:MM format.
     * @return string Time in H:MM AM/PM format.
     */
    private static function convert_24h_to_12h( $time_24h ) {
        $parts = explode( ':', $time_24h );
        $h     = (int) $parts[0];
        $m     = (int) $parts[1];

        $period = $h >= 12 ? 'PM' : 'AM';
        $h      = $h % 12;
        if ( 0 === $h ) {
            $h = 12;
        }

        return sprintf( '%d:%02d %s', $h, $m, $period );
    }

    /**
     * Calculate prayer times for a New Zealand city
     * Uses AlAdhan.com API for accurate times with local calculation fallback.
     *
     * @param string $city   City slug.
     * @param string $date   Date in Y-m-d format.
     * @param string $method Calculation method.
     * @return array|WP_Error
     */
    public static function calculate_for_city( $city, $date = null, $method = 'mwl' ) {
        $city = strtolower( $city );

        if ( ! isset( self::$nz_cities[ $city ] ) ) {
            return new WP_Error(
                'invalid_city',
                __( 'Invalid city specified.', 'hilal' ),
                array( 'status' => 400 )
            );
        }

        $city_data = self::$nz_cities[ $city ];

        // Try AlAdhan.com API first for accurate times
        $times = self::fetch_from_aladhan(
            $city_data['lat'],
            $city_data['lng'],
            $date,
            $method,
            $city_data['timezone']
        );

        // If fetch failed or returned WP_Error, fall back to local calculation
        if ( is_wp_error( $times ) ) {
            $times = self::calculate(
                $city_data['lat'],
                $city_data['lng'],
                $date,
                $method,
                'standard',
                $city_data['timezone']
            );
        }

        $times['city'] = array(
            'slug'    => $city,
            'name'    => $city_data['name'],
            'name_ar' => $city_data['name_ar'],
        );

        return $times;
    }

    /**
     * Fetch prayer times for a city from AlAdhan.com API
     *
     * @param string $city_name City name.
     * @param string $country   Country name.
     * @param string $date      Date in Y-m-d format.
     * @param string $method    Calculation method.
     * @return array|WP_Error
     */
    public static function fetch_city_from_aladhan( $city_name, $country = 'New Zealand', $date = null, $method = 'mwl' ) {
        if ( is_null( $date ) ) {
            $date = gmdate( 'Y-m-d' );
        }

        // Get AlAdhan method ID
        $method_id = isset( self::$aladhan_methods[ $method ] ) ? self::$aladhan_methods[ $method ] : 3;

        // Extract date parts
        $date_parts = explode( '-', $date );

        // Build API URL
        $url = add_query_arg(
            array(
                'city'    => $city_name,
                'country' => $country,
                'method'  => $method_id,
            ),
            self::ALADHAN_API_URL . '/timingsByCity/' . $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0]
        );

        // Make API request
        $response = wp_remote_get( $url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/json',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $code ) {
            return new WP_Error(
                'api_error',
                __( 'Failed to fetch prayer times from API.', 'hilal' ),
                array( 'status' => $code )
            );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( empty( $data ) || ! isset( $data['data']['timings'] ) ) {
            return new WP_Error(
                'invalid_response',
                __( 'Invalid API response.', 'hilal' ),
                array( 'status' => 500 )
            );
        }

        return $data;
    }

    /**
     * Get monthly prayer timetable
     *
     * @param float  $latitude  Latitude.
     * @param float  $longitude Longitude.
     * @param int    $year      Year.
     * @param int    $month     Month.
     * @param string $method    Calculation method.
     * @param string $timezone  Timezone.
     * @return array
     */
    public static function get_monthly_timetable( $latitude, $longitude, $year, $month, $method = 'mwl', $timezone = 'Pacific/Auckland' ) {
        $days_in_month = cal_days_in_month( CAL_GREGORIAN, $month, $year );
        $timetable     = array();

        for ( $day = 1; $day <= $days_in_month; $day++ ) {
            $date              = sprintf( '%04d-%02d-%02d', $year, $month, $day );
            $times             = self::calculate( $latitude, $longitude, $date, $method, 'standard', $timezone );
            $timetable[ $day ] = $times['times'];
        }

        return array(
            'year'      => $year,
            'month'     => $month,
            'location'  => array(
                'latitude'  => $latitude,
                'longitude' => $longitude,
                'timezone'  => $timezone,
            ),
            'method'    => $method,
            'timetable' => $timetable,
        );
    }

    /**
     * Get available calculation methods
     *
     * @return array
     */
    public static function get_methods() {
        $methods = array();
        foreach ( self::$methods as $id => $data ) {
            $methods[ $id ] = $data['name'];
        }
        return $methods;
    }

    /**
     * Get available New Zealand cities
     *
     * @return array
     */
    public static function get_cities() {
        return self::$nz_cities;
    }

    /**
     * Calculate Julian Date
     *
     * @param int $year  Year.
     * @param int $month Month.
     * @param int $day   Day.
     * @return float
     */
    private static function julian_date( $year, $month, $day ) {
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
     * Calculate sun position
     *
     * @param float $jd Julian Date.
     * @return array
     */
    private static function sun_position( $jd ) {
        $d = $jd - 2451545.0;

        // Mean longitude of the Sun
        $g = fmod( 357.529 + 0.98560028 * $d, 360 );
        $q = fmod( 280.459 + 0.98564736 * $d, 360 );

        // Ecliptic longitude of the Sun
        $l = fmod( $q + 1.915 * sin( deg2rad( $g ) ) + 0.020 * sin( deg2rad( 2 * $g ) ), 360 );

        // Distance from Earth to Sun (not needed but kept for reference)
        // $r = 1.00014 - 0.01671 * cos(deg2rad($g)) - 0.00014 * cos(deg2rad(2 * $g));

        // Obliquity of the ecliptic
        $e = 23.439 - 0.00000036 * $d;

        // Declination of the Sun
        $declination = rad2deg( asin( sin( deg2rad( $e ) ) * sin( deg2rad( $l ) ) ) );

        // Right ascension
        $ra = rad2deg( atan2( cos( deg2rad( $e ) ) * sin( deg2rad( $l ) ), cos( deg2rad( $l ) ) ) ) / 15;

        // Equation of time
        $equation = $q / 15 - $ra;
        if ( $equation > 12 ) {
            $equation -= 24;
        }

        return array(
            'declination' => $declination,
            'equation'    => $equation,
        );
    }

    /**
     * Compute prayer time based on angle
     *
     * @param float $latitude    Latitude.
     * @param float $declination Sun declination.
     * @param float $equation    Equation of time.
     * @param float $angle       Angle.
     * @param bool  $morning     True for morning prayers, false for evening.
     * @return float
     */
    private static function compute_time( $latitude, $declination, $equation, $angle, $morning ) {
        $lat_rad = deg2rad( $latitude );
        $dec_rad = deg2rad( $declination );

        // Hour angle
        $cos_hour = ( sin( deg2rad( $angle ) ) - sin( $lat_rad ) * sin( $dec_rad ) ) /
                    ( cos( $lat_rad ) * cos( $dec_rad ) );

        // Clamp to valid range
        $cos_hour = max( -1, min( 1, $cos_hour ) );

        $hour_angle = rad2deg( acos( $cos_hour ) ) / 15;

        if ( $morning ) {
            return 12 - $hour_angle + $equation;
        }

        return 12 + $hour_angle + $equation;
    }

    /**
     * Compute Asr time
     *
     * @param float $latitude    Latitude.
     * @param float $declination Sun declination.
     * @param float $equation    Equation of time.
     * @param int   $factor      Asr factor (1 for standard, 2 for Hanafi).
     * @return float
     */
    private static function compute_asr_time( $latitude, $declination, $equation, $factor ) {
        $lat_rad = deg2rad( $latitude );
        $dec_rad = deg2rad( $declination );

        // Asr shadow angle
        $angle = -rad2deg( atan( 1 / ( $factor + tan( abs( $lat_rad - $dec_rad ) ) ) ) );

        return self::compute_time( $latitude, $declination, $equation, $angle, false );
    }

    /**
     * Format time as HH:MM AM/PM
     *
     * @param float $hours Hours in decimal.
     * @return string
     */
    private static function format_time( $hours ) {
        // Normalize hours
        while ( $hours < 0 ) {
            $hours += 24;
        }
        while ( $hours >= 24 ) {
            $hours -= 24;
        }

        $h   = floor( $hours );
        $m   = round( ( $hours - $h ) * 60 );

        if ( $m >= 60 ) {
            $m -= 60;
            $h++;
        }

        $period = $h >= 12 ? 'PM' : 'AM';
        $h      = $h % 12;
        if ( 0 === $h ) {
            $h = 12;
        }

        return sprintf( '%d:%02d %s', $h, $m, $period );
    }

    /**
     * Format time as HH:MM (24-hour)
     *
     * @param float $hours Hours in decimal.
     * @return string
     */
    private static function format_time_24h( $hours ) {
        while ( $hours < 0 ) {
            $hours += 24;
        }
        while ( $hours >= 24 ) {
            $hours -= 24;
        }

        $h = floor( $hours );
        $m = round( ( $hours - $h ) * 60 );

        if ( $m >= 60 ) {
            $m -= 60;
            $h++;
        }

        return sprintf( '%02d:%02d', $h, $m );
    }
}
