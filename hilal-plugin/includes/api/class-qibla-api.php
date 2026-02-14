<?php
/**
 * Qibla Direction REST API
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Qibla API Class
 */
class Hilal_Qibla_API extends Hilal_API_Base {

    /**
     * Kaaba coordinates
     */
    const KAABA_LAT = 21.4225;
    const KAABA_LNG = 39.8262;

    /**
     * Register routes
     */
    public function register_routes() {
        // GET /hilal/v1/qibla
        register_rest_route(
            $this->namespace,
            '/qibla',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_qibla_direction' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'lat'  => array(
                        'required'          => true,
                        'type'              => 'number',
                        'minimum'           => -90,
                        'maximum'           => 90,
                        'sanitize_callback' => array( $this, 'sanitize_float' ),
                    ),
                    'lng'  => array(
                        'required'          => true,
                        'type'              => 'number',
                        'minimum'           => -180,
                        'maximum'           => 180,
                        'sanitize_callback' => array( $this, 'sanitize_float' ),
                    ),
                ),
            )
        );

        // GET /hilal/v1/qibla/city/{city}
        register_rest_route(
            $this->namespace,
            '/qibla/city/(?P<city>[a-z]+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_qibla_by_city' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'city' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );
    }

    /**
     * Get Qibla direction for coordinates
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_qibla_direction( $request ) {
        $lat = $request->get_param( 'lat' );
        $lng = $request->get_param( 'lng' );

        $result = $this->calculate_qibla( $lat, $lng );

        return $this->success( $result );
    }

    /**
     * Get Qibla direction for a city
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_qibla_by_city( $request ) {
        $city   = strtolower( $request->get_param( 'city' ) );
        $lang   = $this->get_language( $request );
        $cities = Hilal_Prayer_Calculator::get_cities();

        if ( ! isset( $cities[ $city ] ) ) {
            return $this->error(
                'invalid_city',
                __( 'Invalid city specified.', 'hilal' ),
                400
            );
        }

        $city_data = $cities[ $city ];
        $result    = $this->calculate_qibla( $city_data['lat'], $city_data['lng'] );

        $result['city'] = array(
            'slug'    => $city,
            'name'    => 'ar' === $lang ? $city_data['name_ar'] : $city_data['name'],
            'name_en' => $city_data['name'],
            'name_ar' => $city_data['name_ar'],
        );

        return $this->success( $result );
    }

    /**
     * Calculate Qibla direction and distance
     *
     * @param float $lat Latitude.
     * @param float $lng Longitude.
     * @return array
     */
    private function calculate_qibla( $lat, $lng ) {
        // Calculate Qibla bearing
        $bearing = $this->calculate_bearing( $lat, $lng, self::KAABA_LAT, self::KAABA_LNG );

        // Calculate distance to Kaaba
        $distance = $this->calculate_distance( $lat, $lng, self::KAABA_LAT, self::KAABA_LNG );

        // Get cardinal direction
        $cardinal = $this->get_cardinal_direction( $bearing );

        return array(
            'location'  => array(
                'latitude'  => $lat,
                'longitude' => $lng,
            ),
            'kaaba'     => array(
                'latitude'  => self::KAABA_LAT,
                'longitude' => self::KAABA_LNG,
            ),
            'qibla'     => array(
                'bearing'            => round( $bearing, 2 ),
                'bearing_rounded'    => round( $bearing ),
                'cardinal_direction' => $cardinal['direction'],
                'cardinal_abbr'      => $cardinal['abbr'],
                'description_en'     => sprintf( '%.1f° from North (%s)', $bearing, $cardinal['direction'] ),
                'description_ar'     => sprintf( '%.1f° من الشمال (%s)', $bearing, $cardinal['direction_ar'] ),
            ),
            'distance'  => array(
                'km'    => round( $distance, 1 ),
                'miles' => round( $distance * 0.621371, 1 ),
            ),
        );
    }

    /**
     * Calculate bearing between two points
     *
     * @param float $lat1 Start latitude.
     * @param float $lng1 Start longitude.
     * @param float $lat2 End latitude.
     * @param float $lng2 End longitude.
     * @return float Bearing in degrees (0-360).
     */
    private function calculate_bearing( $lat1, $lng1, $lat2, $lng2 ) {
        $lat1 = deg2rad( $lat1 );
        $lat2 = deg2rad( $lat2 );
        $lng1 = deg2rad( $lng1 );
        $lng2 = deg2rad( $lng2 );

        $d_lng = $lng2 - $lng1;

        $x = cos( $lat2 ) * sin( $d_lng );
        $y = cos( $lat1 ) * sin( $lat2 ) - sin( $lat1 ) * cos( $lat2 ) * cos( $d_lng );

        $bearing = atan2( $x, $y );
        $bearing = rad2deg( $bearing );

        // Normalize to 0-360
        return fmod( $bearing + 360, 360 );
    }

    /**
     * Calculate distance between two points using Haversine formula
     *
     * @param float $lat1 Start latitude.
     * @param float $lng1 Start longitude.
     * @param float $lat2 End latitude.
     * @param float $lng2 End longitude.
     * @return float Distance in kilometers.
     */
    private function calculate_distance( $lat1, $lng1, $lat2, $lng2 ) {
        $earth_radius = 6371; // km

        $lat1 = deg2rad( $lat1 );
        $lat2 = deg2rad( $lat2 );
        $lng1 = deg2rad( $lng1 );
        $lng2 = deg2rad( $lng2 );

        $d_lat = $lat2 - $lat1;
        $d_lng = $lng2 - $lng1;

        $a = sin( $d_lat / 2 ) * sin( $d_lat / 2 ) +
             cos( $lat1 ) * cos( $lat2 ) *
             sin( $d_lng / 2 ) * sin( $d_lng / 2 );

        $c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );

        return $earth_radius * $c;
    }

    /**
     * Get cardinal direction from bearing
     *
     * @param float $bearing Bearing in degrees.
     * @return array
     */
    private function get_cardinal_direction( $bearing ) {
        $directions = array(
            array(
                'min'          => 0,
                'max'          => 22.5,
                'direction'    => 'North',
                'direction_ar' => 'شمال',
                'abbr'         => 'N',
            ),
            array(
                'min'          => 22.5,
                'max'          => 67.5,
                'direction'    => 'Northeast',
                'direction_ar' => 'شمال شرق',
                'abbr'         => 'NE',
            ),
            array(
                'min'          => 67.5,
                'max'          => 112.5,
                'direction'    => 'East',
                'direction_ar' => 'شرق',
                'abbr'         => 'E',
            ),
            array(
                'min'          => 112.5,
                'max'          => 157.5,
                'direction'    => 'Southeast',
                'direction_ar' => 'جنوب شرق',
                'abbr'         => 'SE',
            ),
            array(
                'min'          => 157.5,
                'max'          => 202.5,
                'direction'    => 'South',
                'direction_ar' => 'جنوب',
                'abbr'         => 'S',
            ),
            array(
                'min'          => 202.5,
                'max'          => 247.5,
                'direction'    => 'Southwest',
                'direction_ar' => 'جنوب غرب',
                'abbr'         => 'SW',
            ),
            array(
                'min'          => 247.5,
                'max'          => 292.5,
                'direction'    => 'West',
                'direction_ar' => 'غرب',
                'abbr'         => 'W',
            ),
            array(
                'min'          => 292.5,
                'max'          => 337.5,
                'direction'    => 'Northwest',
                'direction_ar' => 'شمال غرب',
                'abbr'         => 'NW',
            ),
            array(
                'min'          => 337.5,
                'max'          => 360,
                'direction'    => 'North',
                'direction_ar' => 'شمال',
                'abbr'         => 'N',
            ),
        );

        foreach ( $directions as $dir ) {
            if ( $bearing >= $dir['min'] && $bearing < $dir['max'] ) {
                return $dir;
            }
        }

        return $directions[0]; // Default to North
    }
}
