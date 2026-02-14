<?php
/**
 * Calendar REST API
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Calendar API Class
 */
class Hilal_Calendar_API extends Hilal_API_Base {

    /**
     * Register routes
     */
    public function register_routes() {
        // GET /hilal/v1/today
        register_rest_route(
            $this->namespace,
            '/today',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_today' ),
                'permission_callback' => '__return_true',
            )
        );

        // GET /hilal/v1/hijri-calendar/{year}
        register_rest_route(
            $this->namespace,
            '/hijri-calendar/(?P<year>\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_year_calendar' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'year' => array(
                        'required'          => true,
                        'type'              => 'integer',
                        'minimum'           => 1400,
                        'maximum'           => 1500,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );

        // GET /hilal/v1/hijri-calendar (current year)
        register_rest_route(
            $this->namespace,
            '/hijri-calendar',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_current_year_calendar' ),
                'permission_callback' => '__return_true',
            )
        );

        // GET /hilal/v1/islamic-events
        register_rest_route(
            $this->namespace,
            '/islamic-events',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_islamic_events' ),
                'permission_callback' => '__return_true',
            )
        );

        // GET /hilal/v1/upcoming-events
        register_rest_route(
            $this->namespace,
            '/upcoming-events',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_upcoming_events' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'limit' => array(
                        'default'           => 5,
                        'type'              => 'integer',
                        'minimum'           => 1,
                        'maximum'           => 20,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );
    }

    /**
     * Get today's Hijri date and related information
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_today( $request ) {
        $lang = $this->get_language( $request );

        // Get current Hijri month info (confirmed or calculated)
        $current_month = Hilal_Hijri_Date::get_current_month_info();

        // Format date
        $formatted_date = Hilal_Hijri_Date::format_date(
            $current_month['day'],
            $current_month['month'],
            $current_month['year'],
            $lang
        );

        // Get upcoming events
        $upcoming_events = Hilal_Islamic_Event::get_upcoming_events( 3 );

        // Get next significant month countdown
        $next_month = $this->get_next_significant_month();

        $data = array(
            'hijri_date'     => array(
                'day'           => $current_month['day'],
                'month'         => $current_month['month'],
                'year'          => $current_month['year'],
                'month_name'    => 'ar' === $lang ? $current_month['month_name_ar'] : $current_month['month_name_en'],
                'month_name_en' => $current_month['month_name_en'],
                'month_name_ar' => $current_month['month_name_ar'],
                'formatted'     => $formatted_date,
                'status'        => $current_month['status'],
                'source'        => $current_month['source'],
            ),
            'gregorian_date' => array(
                'date'      => gmdate( 'Y-m-d' ),
                'formatted' => gmdate( 'j F Y' ),
            ),
            'upcoming_events' => array_map( function( $event ) use ( $lang ) {
                return array(
                    'id'        => $event['id'],
                    'name'      => 'ar' === $lang ? $event['event_name_ar'] : $event['event_name_en'],
                    'hijri_day' => $event['hijri_day'],
                    'hijri_month' => $event['hijri_month'],
                    'hijri_month_name' => 'ar' === $lang ? $event['hijri_month_name_ar'] : $event['hijri_month_name_en'],
                );
            }, $upcoming_events ),
            'next_significant_month' => $next_month,
        );

        return $this->success( $data );
    }

    /**
     * Get calendar for a specific Hijri year
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_year_calendar( $request ) {
        $year = $request->get_param( 'year' );
        $lang = $this->get_language( $request );

        // Get months from database
        $months = Hilal_Hijri_Month::get_year_months( $year );

        // If no months exist, create estimated ones
        if ( empty( $months ) ) {
            $created = Hilal_Hijri_Month::create_year_months( $year );
            $months  = Hilal_Hijri_Month::get_year_months( $year );
        }

        // Get all Islamic events
        $all_events = Hilal_Islamic_Event::get_all_events();

        // Add events to each month
        $months_with_events = array_map( function( $month ) use ( $all_events, $lang ) {
            $month_events = array_filter( $all_events, function( $event ) use ( $month ) {
                return $event['hijri_month'] === $month['month_number'];
            } );

            return array(
                'month_number'    => $month['month_number'],
                'hijri_year'      => $month['hijri_year'],
                'month_name'      => 'ar' === $lang ? $month['month_name_ar'] : $month['month_name_en'],
                'month_name_en'   => $month['month_name_en'],
                'month_name_ar'   => $month['month_name_ar'],
                'gregorian_start' => $month['gregorian_start'],
                'gregorian_end'   => $month['gregorian_end'],
                'days_count'      => $month['days_count'],
                'status'          => $month['status'],
                'events'          => array_values( array_map( function( $event ) use ( $lang ) {
                    return array(
                        'id'        => $event['id'],
                        'name'      => 'ar' === $lang ? $event['event_name_ar'] : $event['event_name_en'],
                        'hijri_day' => $event['hijri_day'],
                        'category'  => $event['event_category'],
                    );
                }, $month_events ) ),
            );
        }, $months );

        // Fill in missing months with calculated dates
        $complete_calendar = array();
        for ( $m = 1; $m <= 12; $m++ ) {
            $found = null;
            foreach ( $months_with_events as $month ) {
                if ( $month['month_number'] === $m ) {
                    $found = $month;
                    break;
                }
            }

            if ( $found ) {
                $complete_calendar[] = $found;
            } else {
                // Create calculated entry
                $gregorian = Hilal_Hijri_Date::hijri_to_gregorian( $year, $m, 1 );
                $complete_calendar[] = array(
                    'month_number'    => $m,
                    'hijri_year'      => $year,
                    'month_name'      => 'ar' === $lang ?
                        Hilal_Hijri_Date::get_month_name( $m, 'ar' ) :
                        Hilal_Hijri_Date::get_month_name( $m, 'en' ),
                    'month_name_en'   => Hilal_Hijri_Date::get_month_name( $m, 'en' ),
                    'month_name_ar'   => Hilal_Hijri_Date::get_month_name( $m, 'ar' ),
                    'gregorian_start' => sprintf( '%04d-%02d-%02d', $gregorian['year'], $gregorian['month'], $gregorian['day'] ),
                    'gregorian_end'   => null,
                    'days_count'      => null,
                    'status'          => 'estimated',
                    'events'          => array(),
                );
            }
        }

        $data = array(
            'year'   => $year,
            'months' => $complete_calendar,
        );

        return $this->success( $data );
    }

    /**
     * Get calendar for current Hijri year
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_current_year_calendar( $request ) {
        $current = Hilal_Hijri_Date::get_today_hijri();
        $request->set_param( 'year', $current['year'] );

        return $this->get_year_calendar( $request );
    }

    /**
     * Get all Islamic events
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_islamic_events( $request ) {
        $lang   = $this->get_language( $request );
        $events = Hilal_Islamic_Event::get_all_events();

        $formatted_events = array_map( function( $event ) use ( $lang ) {
            return array(
                'id'              => $event['id'],
                'name'            => 'ar' === $lang ? $event['event_name_ar'] : $event['event_name_en'],
                'name_en'         => $event['event_name_en'],
                'name_ar'         => $event['event_name_ar'],
                'hijri_day'       => $event['hijri_day'],
                'hijri_month'     => $event['hijri_month'],
                'hijri_month_name' => 'ar' === $lang ? $event['hijri_month_name_ar'] : $event['hijri_month_name_en'],
                'description'     => 'ar' === $lang ? $event['description_ar'] : $event['description_en'],
                'category'        => $event['event_category'],
                'duration_days'   => $event['duration_days'],
                'is_recurring'    => $event['is_recurring'],
            );
        }, $events );

        return $this->success( array( 'events' => $formatted_events ) );
    }

    /**
     * Get upcoming events
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function get_upcoming_events( $request ) {
        $limit  = $request->get_param( 'limit' );
        $lang   = $this->get_language( $request );
        $events = Hilal_Islamic_Event::get_upcoming_events( $limit );

        $formatted_events = array_map( function( $event ) use ( $lang ) {
            return array(
                'id'              => $event['id'],
                'name'            => 'ar' === $lang ? $event['event_name_ar'] : $event['event_name_en'],
                'hijri_day'       => $event['hijri_day'],
                'hijri_month'     => $event['hijri_month'],
                'hijri_month_name' => 'ar' === $lang ? $event['hijri_month_name_ar'] : $event['hijri_month_name_en'],
                'category'        => $event['event_category'],
            );
        }, $events );

        return $this->success( array( 'events' => $formatted_events ) );
    }

    /**
     * Get next significant month (Ramadan, Dhul Hijjah, etc.)
     *
     * @return array|null
     */
    private function get_next_significant_month() {
        $current    = Hilal_Hijri_Date::get_current_month_info();
        $significant = array( 9, 12 ); // Ramadan, Dhul Hijjah

        foreach ( $significant as $month ) {
            if ( $month > $current['month'] ) {
                return array(
                    'month'         => $month,
                    'month_name_en' => Hilal_Hijri_Date::get_month_name( $month, 'en' ),
                    'month_name_ar' => Hilal_Hijri_Date::get_month_name( $month, 'ar' ),
                    'year'          => $current['year'],
                );
            }
        }

        // Next year
        return array(
            'month'         => 9,
            'month_name_en' => Hilal_Hijri_Date::get_month_name( 9, 'en' ),
            'month_name_ar' => Hilal_Hijri_Date::get_month_name( 9, 'ar' ),
            'year'          => $current['year'] + 1,
        );
    }
}
