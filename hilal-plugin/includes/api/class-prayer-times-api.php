<?php
/**
 * Prayer Times REST API
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Prayer Times API Class
 */
class Hilal_Prayer_Times_API extends Hilal_API_Base {

    /**
     * Register routes
     */
    public function register_routes() {
        // GET /hilal/v1/prayer-times
        register_rest_route(
            $this->namespace,
            '/prayer-times',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_prayer_times' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'lat'    => array(
                        'type'              => 'number',
                        'minimum'           => -90,
                        'maximum'           => 90,
                        'sanitize_callback' => array( $this, 'sanitize_float' ),
                    ),
                    'lng'    => array(
                        'type'              => 'number',
                        'minimum'           => -180,
                        'maximum'           => 180,
                        'sanitize_callback' => array( $this, 'sanitize_float' ),
                    ),
                    'city'   => array(
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'date'   => array(
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'method' => array(
                        'type'              => 'string',
                        'default'           => 'mwl',
                        'enum'              => array( 'mwl', 'isna', 'egypt', 'makkah', 'karachi', 'tehran', 'jafari', 'singapore' ),
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );

        // GET /hilal/v1/prayer-times/city/{city}
        register_rest_route(
            $this->namespace,
            '/prayer-times/city/(?P<city>[a-z]+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_prayer_times_by_city' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'city'   => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'date'   => array(
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'method' => array(
                        'type'              => 'string',
                        'default'           => 'mwl',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );

        // GET /hilal/v1/prayer-times/monthly
        register_rest_route(
            $this->namespace,
            '/prayer-times/monthly',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_monthly_timetable' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'lat'    => array(
                        'type'              => 'number',
                        'sanitize_callback' => array( $this, 'sanitize_float' ),
                    ),
                    'lng'    => array(
                        'type'              => 'number',
                        'sanitize_callback' => array( $this, 'sanitize_float' ),
                    ),
                    'city'   => array(
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'year'   => array(
                        'type'              => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'month'  => array(
                        'type'              => 'integer',
                        'minimum'           => 1,
                        'maximum'           => 12,
                        'sanitize_callback' => 'absint',
                    ),
                    'method' => array(
                        'type'              => 'string',
                        'default'           => 'mwl',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );

        // GET /hilal/v1/prayer-times/cities
        register_rest_route(
            $this->namespace,
            '/prayer-times/cities',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_cities' ),
                'permission_callback' => '__return_true',
            )
        );

        // GET /hilal/v1/prayer-times/methods
        register_rest_route(
            $this->namespace,
            '/prayer-times/methods',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_methods' ),
                'permission_callback' => '__return_true',
            )
        );

        // GET /hilal/v1/prayer-times/mosques
        register_rest_route(
            $this->namespace,
            '/prayer-times/mosques',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_mosques' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'region' => array(
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'city'   => array(
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );

        // GET /hilal/v1/prayer-times/my-masjid/{masjid_id}
        register_rest_route(
            $this->namespace,
            '/prayer-times/my-masjid/(?P<masjid_id>[a-zA-Z0-9-]+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_my_masjid_times' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'masjid_id' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );

        // GET /hilal/v1/prayer-times/mosque/{mosque_id}
        register_rest_route(
            $this->namespace,
            '/prayer-times/mosque/(?P<mosque_id>[a-zA-Z0-9-]+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_mosque_prayer_times' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'mosque_id' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'date' => array(
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );
    }

    /**
     * Get prayer times by coordinates or city
     * Uses AlAdhan.com API for accurate times with local calculation fallback.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_prayer_times( $request ) {
        $lat    = $request->get_param( 'lat' );
        $lng    = $request->get_param( 'lng' );
        $city   = $request->get_param( 'city' );
        $date   = $request->get_param( 'date' ) ?: gmdate( 'Y-m-d' );
        $method = $request->get_param( 'method' ) ?: 'mwl';

        // Validate date format
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
            return $this->error(
                'invalid_date',
                __( 'Invalid date format. Use YYYY-MM-DD.', 'hilal' ),
                400
            );
        }

        // If city is provided, use city coordinates
        if ( $city ) {
            $result = Hilal_Prayer_Calculator::calculate_for_city( $city, $date, $method );

            if ( is_wp_error( $result ) ) {
                return $result;
            }

            return $this->success( $this->format_prayer_response( $result ) );
        }

        // Otherwise, require lat/lng
        if ( is_null( $lat ) || is_null( $lng ) ) {
            return $this->error(
                'missing_location',
                __( 'Either city or lat/lng coordinates are required.', 'hilal' ),
                400
            );
        }

        // Use AlAdhan.com API for accurate times
        $result = Hilal_Prayer_Calculator::fetch_from_aladhan(
            $lat,
            $lng,
            $date,
            $method,
            'Pacific/Auckland'
        );

        // Fall back to local calculation if API fails
        if ( is_wp_error( $result ) ) {
            $result = Hilal_Prayer_Calculator::calculate(
                $lat,
                $lng,
                $date,
                $method,
                'standard',
                'Pacific/Auckland'
            );
        }

        return $this->success( $this->format_prayer_response( $result ) );
    }

    /**
     * Get prayer times by city
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_prayer_times_by_city( $request ) {
        $city   = $request->get_param( 'city' );
        $date   = $request->get_param( 'date' ) ?: gmdate( 'Y-m-d' );
        $method = $request->get_param( 'method' );

        $result = Hilal_Prayer_Calculator::calculate_for_city( $city, $date, $method );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return $this->success( $this->format_prayer_response( $result ) );
    }

    /**
     * Get monthly prayer timetable
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_monthly_timetable( $request ) {
        $lat    = $request->get_param( 'lat' );
        $lng    = $request->get_param( 'lng' );
        $city   = $request->get_param( 'city' );
        $year   = $request->get_param( 'year' ) ?: (int) gmdate( 'Y' );
        $month  = $request->get_param( 'month' ) ?: (int) gmdate( 'n' );
        $method = $request->get_param( 'method' );

        // Get coordinates
        if ( $city ) {
            $cities = Hilal_Prayer_Calculator::get_cities();
            $city   = strtolower( $city );

            if ( ! isset( $cities[ $city ] ) ) {
                return $this->error(
                    'invalid_city',
                    __( 'Invalid city specified.', 'hilal' ),
                    400
                );
            }

            $lat      = $cities[ $city ]['lat'];
            $lng      = $cities[ $city ]['lng'];
            $timezone = $cities[ $city ]['timezone'];
        } else {
            if ( is_null( $lat ) || is_null( $lng ) ) {
                return $this->error(
                    'missing_location',
                    __( 'Either city or lat/lng coordinates are required.', 'hilal' ),
                    400
                );
            }
            $timezone = 'Pacific/Auckland';
        }

        $result = Hilal_Prayer_Calculator::get_monthly_timetable(
            $lat,
            $lng,
            $year,
            $month,
            $method,
            $timezone
        );

        return $this->success( $result );
    }

    /**
     * Get available cities
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_cities( $request ) {
        $lang   = $this->get_language( $request );
        $cities = Hilal_Prayer_Calculator::get_cities();

        $formatted = array();
        foreach ( $cities as $slug => $data ) {
            $formatted[] = array(
                'slug'     => $slug,
                'name'     => 'ar' === $lang ? $data['name_ar'] : $data['name'],
                'name_en'  => $data['name'],
                'name_ar'  => $data['name_ar'],
                'lat'      => $data['lat'],
                'lng'      => $data['lng'],
                'timezone' => $data['timezone'],
            );
        }

        return $this->success( array( 'cities' => $formatted ) );
    }

    /**
     * Get available calculation methods
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_methods( $request ) {
        $methods = Hilal_Prayer_Calculator::get_methods();

        $formatted = array();
        foreach ( $methods as $id => $name ) {
            $formatted[] = array(
                'id'   => $id,
                'name' => $name,
            );
        }

        return $this->success( array( 'methods' => $formatted ) );
    }

    /**
     * Get all New Zealand mosques
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_mosques( $request ) {
        $lang   = $this->get_language( $request );
        $region = $request->get_param( 'region' );
        $city   = $request->get_param( 'city' );

        $mosques = $this->get_nz_mosques();

        // Filter by region if provided
        if ( $region ) {
            $mosques = array_filter( $mosques, function ( $m ) use ( $region ) {
                return strtolower( $m['region'] ) === strtolower( $region );
            });
        }

        // Filter by city if provided
        if ( $city ) {
            $mosques = array_filter( $mosques, function ( $m ) use ( $city ) {
                return strtolower( $m['city'] ) === strtolower( $city );
            });
        }

        // Format for language
        $formatted = array_map( function ( $m ) use ( $lang ) {
            return array(
                'id'              => $m['id'],
                'name'            => 'ar' === $lang ? $m['name_ar'] : $m['name'],
                'name_en'         => $m['name'],
                'name_ar'         => $m['name_ar'],
                'address'         => $m['address'],
                'city'            => $m['city'],
                'region'          => $m['region'],
                'lat'             => $m['lat'],
                'lng'             => $m['lng'],
                'phone'           => $m['phone'] ?? null,
                'website'         => $m['website'] ?? null,
                'my_masjid_id'    => $m['my_masjid_id'] ?? null,
                'has_iqamah_times' => ! empty( $m['iqamah'] ) || ! empty( $m['my_masjid_id'] ),
                'iqamah_config'   => $m['iqamah'] ?? null,
            );
        }, array_values( $mosques ) );

        // Get unique regions and cities
        $all_mosques = $this->get_nz_mosques();
        $regions = array_unique( array_column( $all_mosques, 'region' ) );
        $cities  = array_unique( array_column( $all_mosques, 'city' ) );

        sort( $regions );
        sort( $cities );

        return $this->success( array(
            'mosques' => $formatted,
            'regions' => array_values( $regions ),
            'cities'  => array_values( $cities ),
            'total'   => count( $formatted ),
        ) );
    }

    /**
     * Calculate iqamah time from config
     *
     * @param string $adhan_time Adhan time in HH:MM format (24h).
     * @param mixed  $iqamah_config Iqamah config (fixed time, +minutes, or seasonal array).
     * @return string Iqamah time in HH:MM format.
     */
    private function calculate_iqamah_time( $adhan_time, $iqamah_config ) {
        if ( empty( $iqamah_config ) ) {
            return null;
        }

        // Handle seasonal times
        if ( is_array( $iqamah_config ) && isset( $iqamah_config['summer'] ) ) {
            // Determine if it's summer (Oct-Mar in NZ) or winter (Apr-Sep)
            $month = (int) gmdate( 'n' );
            $is_summer = $month >= 10 || $month <= 3;
            return $is_summer ? $iqamah_config['summer'] : $iqamah_config['winter'];
        }

        // Handle fixed time
        if ( is_string( $iqamah_config ) && strpos( $iqamah_config, '+' ) !== 0 ) {
            return $iqamah_config;
        }

        // Handle +minutes format
        if ( is_string( $iqamah_config ) && strpos( $iqamah_config, '+' ) === 0 ) {
            $minutes_to_add = (int) substr( $iqamah_config, 1 );
            $parts = explode( ':', $adhan_time );
            $adhan_minutes = ( (int) $parts[0] * 60 ) + (int) $parts[1];
            $iqamah_minutes = $adhan_minutes + $minutes_to_add;

            $hours = floor( $iqamah_minutes / 60 ) % 24;
            $mins = $iqamah_minutes % 60;

            return sprintf( '%02d:%02d', $hours, $mins );
        }

        return null;
    }

    /**
     * Convert 24h time to 12h format
     *
     * @param string $time_24h Time in HH:MM format.
     * @return string Time in H:MM AM/PM format.
     */
    private function format_time_12h( $time_24h ) {
        if ( empty( $time_24h ) ) {
            return null;
        }
        $parts = explode( ':', $time_24h );
        $h = (int) $parts[0];
        $m = (int) $parts[1];

        $period = $h >= 12 ? 'PM' : 'AM';
        $h = $h % 12;
        if ( 0 === $h ) {
            $h = 12;
        }

        return sprintf( '%d:%02d %s', $h, $m, $period );
    }

    /**
     * Get prayer times with iqamah for a specific mosque
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_mosque_prayer_times( $request ) {
        $mosque_id = $request->get_param( 'mosque_id' );
        $date      = $request->get_param( 'date' ) ?: gmdate( 'Y-m-d' );
        $lang      = $this->get_language( $request );

        // Find the mosque
        $mosques = $this->get_nz_mosques();
        $mosque  = null;

        foreach ( $mosques as $m ) {
            if ( $m['id'] === $mosque_id ) {
                $mosque = $m;
                break;
            }
        }

        if ( ! $mosque ) {
            return $this->error(
                'invalid_mosque',
                __( 'Mosque not found.', 'hilal' ),
                404
            );
        }

        // Get prayer times from AlAdhan.com API
        $prayer_times = Hilal_Prayer_Calculator::fetch_from_aladhan(
            $mosque['lat'],
            $mosque['lng'],
            $date,
            'mwl',
            'Pacific/Auckland'
        );

        // Fall back to local calculation if API fails
        if ( is_wp_error( $prayer_times ) ) {
            $prayer_times = Hilal_Prayer_Calculator::calculate(
                $mosque['lat'],
                $mosque['lng'],
                $date,
                'mwl',
                'standard',
                'Pacific/Auckland'
            );
        }

        // Calculate iqamah times if available
        $iqamah_times = array();
        $iqamah_times_12h = array();

        if ( ! empty( $mosque['iqamah'] ) ) {
            $prayers = array( 'fajr', 'dhuhr', 'asr', 'maghrib', 'isha' );

            foreach ( $prayers as $prayer ) {
                if ( isset( $mosque['iqamah'][ $prayer ] ) && isset( $prayer_times['times_24h'][ $prayer ] ) ) {
                    $iqamah_24h = $this->calculate_iqamah_time(
                        $prayer_times['times_24h'][ $prayer ],
                        $mosque['iqamah'][ $prayer ]
                    );
                    if ( $iqamah_24h ) {
                        $iqamah_times[ $prayer ] = $iqamah_24h;
                        $iqamah_times_12h[ $prayer ] = $this->format_time_12h( $iqamah_24h );
                    }
                }
            }

            // Handle Jumuah separately
            if ( isset( $mosque['iqamah']['jumuah'] ) ) {
                $jumuah_config = $mosque['iqamah']['jumuah'];
                if ( is_array( $jumuah_config ) && isset( $jumuah_config['summer'] ) ) {
                    $month = (int) gmdate( 'n' );
                    $is_summer = $month >= 10 || $month <= 3;
                    $jumuah_time = $is_summer ? $jumuah_config['summer'] : $jumuah_config['winter'];
                } else {
                    $jumuah_time = $jumuah_config;
                }
                $iqamah_times['jumuah'] = $jumuah_time;
                $iqamah_times_12h['jumuah'] = $this->format_time_12h( $jumuah_time );
            }
        }

        // Build response
        $response = array(
            'mosque'       => array(
                'id'       => $mosque['id'],
                'name'     => 'ar' === $lang ? $mosque['name_ar'] : $mosque['name'],
                'name_en'  => $mosque['name'],
                'name_ar'  => $mosque['name_ar'],
                'address'  => $mosque['address'],
                'city'     => $mosque['city'],
                'region'   => $mosque['region'],
                'lat'      => $mosque['lat'],
                'lng'      => $mosque['lng'],
                'phone'    => $mosque['phone'] ?? null,
                'website'  => $mosque['website'] ?? null,
            ),
            'date'         => $date,
            'times'        => $prayer_times['times'],
            'times_24h'    => $prayer_times['times_24h'],
            'iqamah'       => $iqamah_times_12h,
            'iqamah_24h'   => $iqamah_times,
            'has_iqamah'   => ! empty( $iqamah_times ),
            'source'       => isset( $prayer_times['source'] ) ? $prayer_times['source'] : 'calculation',
        );

        // Add next prayer info
        $now = new DateTime( 'now', new DateTimeZone( 'Pacific/Auckland' ) );
        $response['next_prayer'] = $this->get_next_prayer( $prayer_times['times_24h'], $now );

        return $this->success( $response );
    }

    /**
     * Get prayer times from my-masjid.com
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_my_masjid_times( $request ) {
        $masjid_id = $request->get_param( 'masjid_id' );

        // Check cache first
        $cache_key = 'hilal_mymasjid_' . $masjid_id;
        $cached    = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $this->success( $cached );
        }

        // Fetch from my-masjid.com
        // Note: my-masjid.com uses JavaScript rendering, so we'd need to either:
        // 1. Use a headless browser to scrape
        // 2. Find their API endpoints
        // 3. Use cached/manual data entry
        //
        // For now, return stored mosque iqama times from database or manual entry
        $mosque_times = get_option( 'hilal_mymasjid_times_' . $masjid_id );

        if ( ! $mosque_times ) {
            // Try to fetch via API (if available) - my-masjid.com timing screen URL
            $times = $this->fetch_my_masjid_times( $masjid_id );

            if ( is_wp_error( $times ) ) {
                return $times;
            }

            $mosque_times = $times;
        }

        // Cache for 30 minutes
        set_transient( $cache_key, $mosque_times, 30 * MINUTE_IN_SECONDS );

        return $this->success( $mosque_times );
    }

    /**
     * Fetch times from my-masjid.com
     *
     * @param string $masjid_id My-Masjid UUID.
     * @return array|WP_Error
     */
    private function fetch_my_masjid_times( $masjid_id ) {
        // My-masjid.com timing screen URL
        $url = 'https://time.my-masjid.com/api/TimingScreen/getTimingsForMasjid/' . $masjid_id;

        $response = wp_remote_get( $url, array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'application/json',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $this->error(
                'fetch_failed',
                __( 'Could not fetch times from my-masjid.com', 'hilal' ),
                503
            );
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $code ) {
            // Return fallback calculated times
            return $this->error(
                'mymasjid_unavailable',
                __( 'My-masjid.com times unavailable, using calculated times.', 'hilal' ),
                503
            );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( empty( $data ) ) {
            return $this->error(
                'invalid_response',
                __( 'Invalid response from my-masjid.com', 'hilal' ),
                503
            );
        }

        // Parse the response - format varies based on my-masjid API
        // This is a best-effort parsing based on common API structures
        $times = array(
            'times' => array(
                'fajr'    => array(
                    'adhan' => $data['fajr']['adhan'] ?? $data['Fajr'] ?? '--:--',
                    'iqama' => $data['fajr']['iqamah'] ?? $data['FajrIqamah'] ?? '--:--',
                ),
                'sunrise' => $data['sunrise'] ?? $data['Sunrise'] ?? '--:--',
                'dhuhr'   => array(
                    'adhan' => $data['dhuhr']['adhan'] ?? $data['Dhuhr'] ?? '--:--',
                    'iqama' => $data['dhuhr']['iqamah'] ?? $data['DhuhrIqamah'] ?? '--:--',
                ),
                'asr'     => array(
                    'adhan' => $data['asr']['adhan'] ?? $data['Asr'] ?? '--:--',
                    'iqama' => $data['asr']['iqamah'] ?? $data['AsrIqamah'] ?? '--:--',
                ),
                'maghrib' => array(
                    'adhan' => $data['maghrib']['adhan'] ?? $data['Maghrib'] ?? '--:--',
                    'iqama' => $data['maghrib']['iqamah'] ?? $data['MaghribIqamah'] ?? '--:--',
                ),
                'isha'    => array(
                    'adhan' => $data['isha']['adhan'] ?? $data['Isha'] ?? '--:--',
                    'iqama' => $data['isha']['iqamah'] ?? $data['IshaIqamah'] ?? '--:--',
                ),
            ),
            'source'      => 'my-masjid',
            'lastUpdated' => gmdate( 'c' ),
        );

        // Check for Jumuah times
        if ( isset( $data['jumuah'] ) || isset( $data['Jumuah'] ) ) {
            $times['times']['jumuah'] = array(
                'adhan' => $data['jumuah']['adhan'] ?? $data['Jumuah'] ?? null,
                'iqama' => $data['jumuah']['iqamah'] ?? $data['JumuahIqamah'] ?? null,
            );
        }

        return $times;
    }

    /**
     * Get NZ mosques data with iqamah times
     *
     * Iqamah times format: array of times for each prayer
     * Times can be:
     * - Fixed time: '13:30' (24h format)
     * - Minutes after adhan: '+10' (10 mins after adhan)
     * - Seasonal: array('summer' => '13:30', 'winter' => '12:30')
     *
     * @return array
     */
    public function get_nz_mosques() {
        return array(
            // WHANGAREI
            array(
                'id'       => 'whangarei-islamic',
                'name'     => 'Northland Muslim Community Islamic Centre',
                'name_ar'  => 'المركز الإسلامي لمجتمع المسلمين في نورثلاند',
                'address'  => '11C Porowini Avenue',
                'city'     => 'Whangarei',
                'region'   => 'Northland',
                'lat'      => -35.7251,
                'lng'      => 174.3237,
                'phone'    => '021 864 832',
            ),

            // AUCKLAND - North Shore
            array(
                'id'       => 'nsia',
                'name'     => 'North Shore Islamic Association (NSIA)',
                'name_ar'  => 'الجمعية الإسلامية نورث شور',
                'address'  => '58 Akoranga Drive, Northcote',
                'city'     => 'Auckland',
                'region'   => 'Auckland',
                'lat'      => -36.7931,
                'lng'      => 174.7503,
                'phone'    => '027 501 9699',
                'website'  => 'https://nsia.org.nz',
                'iqamah'   => array(
                    'fajr'    => '+20',
                    'dhuhr'   => '+15',
                    'asr'     => '+10',
                    'maghrib' => '+5',
                    'isha'    => '+15',
                    'jumuah'  => array( 'summer' => '13:40', 'winter' => '12:40' ),
                ),
            ),
            array(
                'id'      => 'north-shore-islamic',
                'name'    => 'North Shore Islamic Centre (Glenfield)',
                'name_ar' => 'المركز الإسلامي نورث شور (غلينفيلد)',
                'address' => '9B Kaimahi Drive, Glenfield',
                'city'    => 'Auckland',
                'region'  => 'Auckland',
                'lat'     => -36.7797,
                'lng'     => 174.7227,
                'iqamah'  => array(
                    'fajr'    => '+15',
                    'dhuhr'   => '+10',
                    'asr'     => '+10',
                    'maghrib' => '+5',
                    'isha'    => '+10',
                ),
            ),

            // AUCKLAND - Central/South
            array(
                'id'           => 'maunatul-islam',
                'name'         => 'Maunatul Islam New Zealand',
                'name_ar'      => 'منوات الإسلام نيوزيلندا',
                'address'      => '45 Thomas Road, Mangere',
                'city'         => 'Auckland',
                'region'       => 'Auckland',
                'lat'          => -36.9679,
                'lng'          => 174.7877,
                'my_masjid_id' => 'aa874dd0-feeb-4bf2-93fa-f112ffc87ab6',
                'iqamah'       => array(
                    'fajr'    => '+20',
                    'dhuhr'   => '+15',
                    'asr'     => '+10',
                    'maghrib' => '+5',
                    'isha'    => '+15',
                ),
            ),
            array(
                'id'      => 'al-maqtoum-airport',
                'name'    => 'Al Maqtoum Airport Masjid',
                'name_ar' => 'مسجد آل مقتوم بالمطار',
                'address' => '91 Westney Road, Mangere',
                'city'    => 'Auckland',
                'region'  => 'Auckland',
                'lat'     => -36.9717,
                'lng'     => 174.7894,
                'iqamah'  => array(
                    'fajr'    => '+15',
                    'dhuhr'   => '+10',
                    'asr'     => '+10',
                    'maghrib' => '+5',
                    'isha'    => '+10',
                ),
            ),
            array(
                'id'      => 'masjid-e-umar',
                'name'    => 'Masjid e Umar',
                'name_ar' => 'مسجد عمر',
                'address' => '185-187 Stoddard Road, Mt Roskill',
                'city'    => 'Auckland',
                'region'  => 'Auckland',
                'lat'     => -36.9151,
                'lng'     => 174.7322,
                'website' => 'https://masjideumar.co.nz',
                'iqamah'  => array(
                    'fajr'    => '+20',
                    'dhuhr'   => '+15',
                    'asr'     => '+10',
                    'maghrib' => '+5',
                    'isha'    => '+15',
                ),
            ),
            array(
                'id'      => 'masjid-at-taqwa',
                'name'    => 'Masjid At-Taqwa',
                'name_ar' => 'مسجد التقوى',
                'address' => '58 Grayson Avenue, Manukau',
                'city'    => 'Auckland',
                'region'  => 'Auckland',
                'lat'     => -36.9933,
                'lng'     => 174.8795,
                'website' => 'https://masjidattaqwa.co.nz',
                'iqamah'  => array(
                    'fajr'    => '+15',
                    'dhuhr'   => '+10',
                    'asr'     => '+10',
                    'maghrib' => '+5',
                    'isha'    => '+10',
                ),
            ),
            array(
                'id'      => 'ponsonby-masjid',
                'name'    => 'Ponsonby Masjid (NZMA)',
                'name_ar' => 'مسجد بونسونبي',
                'address' => '17 Vermont Street, Ponsonby',
                'city'    => 'Auckland',
                'region'  => 'Auckland',
                'lat'     => -36.8527,
                'lng'     => 174.7455,
                'iqamah'  => array(
                    'fajr'    => '+20',
                    'dhuhr'   => '+15',
                    'asr'     => '+10',
                    'maghrib' => '+5',
                    'isha'    => '+15',
                ),
            ),

            // HAMILTON
            array(
                'id'      => 'jamii-masjid-hamilton',
                'name'    => 'Jamii Masjid Hamilton',
                'name_ar' => 'مسجد جامعي هاملتون',
                'address' => '921 Heaphy Terrace',
                'city'    => 'Hamilton',
                'region'  => 'Waikato',
                'lat'     => -37.7945,
                'lng'     => 175.2795,
                'website' => 'https://www.waikatomuslims.org.nz',
                'iqamah'  => array(
                    'fajr'    => '+20',
                    'dhuhr'   => '+15',
                    'asr'     => '+10',
                    'maghrib' => '+5',
                    'isha'    => '+15',
                ),
            ),
            array(
                'id'      => 'hamilton-west-islamic',
                'name'    => 'Hamilton West Islamic Centre',
                'name_ar' => 'المركز الإسلامي غرب هاملتون',
                'address' => '45 Bandon Street, Frankton',
                'city'    => 'Hamilton',
                'region'  => 'Waikato',
                'lat'     => -37.7877,
                'lng'     => 175.2611,
            ),

            // TAURANGA
            array(
                'id'      => 'tauranga-masjid',
                'name'    => 'Tauranga Masjid',
                'name_ar' => 'مسجد تاورانغا',
                'address' => '85 18th Avenue',
                'city'    => 'Tauranga',
                'region'  => 'Bay of Plenty',
                'lat'     => -37.6988,
                'lng'     => 176.1477,
            ),

            // PALMERSTON NORTH
            array(
                'id'      => 'palmerston-north-islamic',
                'name'    => 'Palmerston North Islamic Centre',
                'name_ar' => 'المركز الإسلامي بالمرستون نورث',
                'address' => '81 Cook Street',
                'city'    => 'Palmerston North',
                'region'  => 'Manawatu-Wanganui',
                'lat'     => -40.3523,
                'lng'     => 175.6082,
            ),

            // WELLINGTON
            array(
                'id'      => 'wellington-masjid',
                'name'    => 'Wellington Masjid (Kilbirnie)',
                'name_ar' => 'مسجد ويلنغتون (كيلبيرني)',
                'address' => '7-11 Queens Drive, Kilbirnie',
                'city'    => 'Wellington',
                'region'  => 'Wellington',
                'lat'     => -41.3144,
                'lng'     => 174.8055,
                'phone'   => '04-387 4226',
                'website' => 'https://iman.org.nz',
                'iqamah'  => array(
                    'fajr'    => '+20',
                    'dhuhr'   => '+15',
                    'asr'     => '+10',
                    'maghrib' => '+5',
                    'isha'    => '+15',
                ),
            ),
            array(
                'id'      => 'hutt-valley-islamic',
                'name'    => 'Hutt Valley Islamic Centre',
                'name_ar' => 'المركز الإسلامي هت فالي',
                'address' => '14-20 Hunter Street',
                'city'    => 'Lower Hutt',
                'region'  => 'Wellington',
                'lat'     => -41.2097,
                'lng'     => 174.9082,
            ),

            // CHRISTCHURCH
            array(
                'id'      => 'masjid-al-noor-chch',
                'name'    => 'Masjid Al Noor (Al-Noor Mosque)',
                'name_ar' => 'مسجد النور',
                'address' => '101 Deans Avenue, Riccarton',
                'city'    => 'Christchurch',
                'region'  => 'Canterbury',
                'lat'     => -43.5347,
                'lng'     => 172.6077,
                'phone'   => '03-348 3930',
                'website' => 'https://macnz.org',
                'iqamah'  => array(
                    'fajr'    => '+20',
                    'dhuhr'   => '+15',
                    'asr'     => '+10',
                    'maghrib' => '+5',
                    'isha'    => '+15',
                ),
            ),
            array(
                'id'      => 'linwood-islamic',
                'name'    => 'Linwood Islamic Centre (Masjid Al-Shuhada)',
                'name_ar' => 'المركز الإسلامي لينوود (مسجد الشهداء)',
                'address' => '223 Linwood Avenue',
                'city'    => 'Christchurch',
                'region'  => 'Canterbury',
                'lat'     => -43.5344,
                'lng'     => 172.6788,
            ),

            // DUNEDIN
            array(
                'id'      => 'masjid-al-huda',
                'name'    => 'Masjid Al Huda',
                'name_ar' => 'مسجد الهدى',
                'address' => '21 Clyde Street',
                'city'    => 'Dunedin',
                'region'  => 'Otago',
                'lat'     => -45.8744,
                'lng'     => 170.5027,
                'phone'   => '03-477 1838',
            ),

            // INVERCARGILL
            array(
                'id'      => 'southland-masjid',
                'name'    => 'Southland Muslim Association Community Centre',
                'name_ar' => 'مركز مجتمع جمعية المسلمين ساوثلاند',
                'address' => '31 Fairview Avenue',
                'city'    => 'Invercargill',
                'region'  => 'Southland',
                'lat'     => -46.4132,
                'lng'     => 168.3475,
                'phone'   => '027 311 7962',
                'website' => 'https://sma.org.nz',
            ),
        );
    }

    /**
     * Format prayer times response
     *
     * @param array $result Calculator result.
     * @return array
     */
    private function format_prayer_response( $result ) {
        $response = array(
            'date'     => $result['date'],
            'location' => $result['location'],
            'method'   => $result['method'],
            'times'    => $result['times'],
            'source'   => isset( $result['source'] ) ? $result['source'] : 'calculation',
        );

        if ( isset( $result['city'] ) ) {
            $response['city'] = $result['city'];
        }

        // Add next prayer info
        $now = new DateTime( 'now', new DateTimeZone( $result['location']['timezone'] ?? 'Pacific/Auckland' ) );
        $response['next_prayer'] = $this->get_next_prayer( $result['times_24h'], $now );

        return $response;
    }

    /**
     * Get next prayer info
     *
     * @param array    $times_24h Prayer times in 24h format.
     * @param DateTime $now       Current time.
     * @return array|null
     */
    private function get_next_prayer( $times_24h, $now ) {
        $current_time = $now->format( 'H:i' );
        $prayers      = array( 'fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha' );

        foreach ( $prayers as $prayer ) {
            if ( isset( $times_24h[ $prayer ] ) && $times_24h[ $prayer ] > $current_time ) {
                // Calculate time until
                $prayer_time = DateTime::createFromFormat(
                    'Y-m-d H:i',
                    $now->format( 'Y-m-d' ) . ' ' . $times_24h[ $prayer ],
                    $now->getTimezone()
                );

                $diff    = $now->diff( $prayer_time );
                $minutes = ( $diff->h * 60 ) + $diff->i;

                return array(
                    'name'         => $prayer,
                    'time'         => $times_24h[ $prayer ],
                    'minutes_until' => $minutes,
                );
            }
        }

        // Next prayer is Fajr tomorrow
        if ( isset( $times_24h['fajr'] ) ) {
            $tomorrow    = clone $now;
            $tomorrow->modify( '+1 day' );
            $prayer_time = DateTime::createFromFormat(
                'Y-m-d H:i',
                $tomorrow->format( 'Y-m-d' ) . ' ' . $times_24h['fajr'],
                $now->getTimezone()
            );

            $diff    = $now->diff( $prayer_time );
            $minutes = ( $diff->h * 60 ) + $diff->i + ( $diff->d * 24 * 60 );

            return array(
                'name'         => 'fajr',
                'time'         => $times_24h['fajr'],
                'minutes_until' => $minutes,
                'tomorrow'     => true,
            );
        }

        return null;
    }
}
