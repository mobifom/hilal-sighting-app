<?php
/**
 * Plugin Name: Hilal - Islamic Moon Sighting Platform
 * Plugin URI: https://hilal.nz
 * Description: Complete platform for Islamic moon sighting, Hijri calendar, announcements, prayer times, and Qibla direction for New Zealand.
 * Version: 1.0.0
 * Author: Mohamed
 * Author URI: https://hilal.nz
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hilal
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package Hilal
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'HILAL_VERSION', '1.0.0' );
define( 'HILAL_PLUGIN_FILE', __FILE__ );
define( 'HILAL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HILAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'HILAL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main Hilal Plugin Class
 */
final class Hilal_Plugin {

    /**
     * Plugin instance
     *
     * @var Hilal_Plugin
     */
    private static $instance = null;

    /**
     * Get plugin instance
     *
     * @return Hilal_Plugin
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Helpers
        require_once HILAL_PLUGIN_DIR . 'includes/helpers/class-hijri-date.php';
        require_once HILAL_PLUGIN_DIR . 'includes/helpers/class-prayer-calculator.php';

        // Post Types
        require_once HILAL_PLUGIN_DIR . 'includes/post-types/class-hijri-month.php';
        require_once HILAL_PLUGIN_DIR . 'includes/post-types/class-sighting-report.php';
        require_once HILAL_PLUGIN_DIR . 'includes/post-types/class-announcement.php';
        require_once HILAL_PLUGIN_DIR . 'includes/post-types/class-islamic-event.php';

        // API
        require_once HILAL_PLUGIN_DIR . 'includes/api/class-api-base.php';
        require_once HILAL_PLUGIN_DIR . 'includes/api/class-calendar-api.php';
        require_once HILAL_PLUGIN_DIR . 'includes/api/class-announcements-api.php';
        require_once HILAL_PLUGIN_DIR . 'includes/api/class-sighting-api.php';
        require_once HILAL_PLUGIN_DIR . 'includes/api/class-prayer-times-api.php';
        require_once HILAL_PLUGIN_DIR . 'includes/api/class-qibla-api.php';

        // Admin
        require_once HILAL_PLUGIN_DIR . 'includes/admin/class-admin-dashboard.php';
        require_once HILAL_PLUGIN_DIR . 'includes/admin/class-admin-columns.php';

        // Notifications
        require_once HILAL_PLUGIN_DIR . 'includes/notifications/class-push-notifications.php';
        require_once HILAL_PLUGIN_DIR . 'includes/notifications/class-email-notifications.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation/Deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Initialize plugin
        add_action( 'init', array( $this, 'init' ) );

        // Load text domain
        add_action( 'init', array( $this, 'load_textdomain' ) );

        // Initialize post types
        add_action( 'init', array( $this, 'register_post_types' ) );

        // Initialize REST API
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

        // ACF JSON save/load paths
        add_filter( 'acf/settings/save_json', array( $this, 'acf_json_save_path' ) );
        add_filter( 'acf/settings/load_json', array( $this, 'acf_json_load_path' ) );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Register post types
        $this->register_post_types();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Create custom database tables if needed
        $this->create_tables();

        // Set default options
        $this->set_default_options();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize admin features
        if ( is_admin() ) {
            new Hilal_Admin_Dashboard();
            new Hilal_Admin_Columns();
        }

        // Initialize notifications
        new Hilal_Push_Notifications();
        new Hilal_Email_Notifications();
    }

    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'hilal',
            false,
            dirname( HILAL_PLUGIN_BASENAME ) . '/languages/'
        );
    }

    /**
     * Register custom post types
     */
    public function register_post_types() {
        Hilal_Hijri_Month::register();
        Hilal_Sighting_Report::register();
        Hilal_Announcement::register();
        Hilal_Islamic_Event::register();
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        $calendar_api = new Hilal_Calendar_API();
        $calendar_api->register_routes();

        $announcements_api = new Hilal_Announcements_API();
        $announcements_api->register_routes();

        $sighting_api = new Hilal_Sighting_API();
        $sighting_api->register_routes();

        $prayer_api = new Hilal_Prayer_Times_API();
        $prayer_api->register_routes();

        $qibla_api = new Hilal_Qibla_API();
        $qibla_api->register_routes();
    }

    /**
     * ACF JSON save path
     *
     * @param string $path The default path.
     * @return string
     */
    public function acf_json_save_path( $path ) {
        return HILAL_PLUGIN_DIR . 'acf-json';
    }

    /**
     * ACF JSON load paths
     *
     * @param array $paths The default paths.
     * @return array
     */
    public function acf_json_load_path( $paths ) {
        $paths[] = HILAL_PLUGIN_DIR . 'acf-json';
        return $paths;
    }

    /**
     * Create custom database tables
     */
    private function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // FCM Device Tokens table
        $table_name = $wpdb->prefix . 'hilal_device_tokens';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned DEFAULT NULL,
            device_token varchar(255) NOT NULL,
            platform varchar(20) NOT NULL DEFAULT 'unknown',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY device_token (device_token),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Email Subscribers table
        $table_name2 = $wpdb->prefix . 'hilal_subscribers';
        $sql2 = "CREATE TABLE IF NOT EXISTS $table_name2 (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            name varchar(255) DEFAULT NULL,
            channels varchar(255) DEFAULT 'email',
            verified tinyint(1) DEFAULT 0,
            verification_token varchar(64) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            unsubscribed_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
        dbDelta( $sql2 );
    }

    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $defaults = array(
            'hilal_region'                => 'nz',
            'hilal_prayer_method'         => 'mwl',
            'hilal_default_language'      => 'en',
            'hilal_fcm_server_key'        => '',
            'hilal_email_from_name'       => get_bloginfo( 'name' ),
            'hilal_email_from_address'    => get_option( 'admin_email' ),
        );

        foreach ( $defaults as $key => $value ) {
            if ( false === get_option( $key ) ) {
                add_option( $key, $value );
            }
        }
    }
}

/**
 * Initialize the plugin
 *
 * @return Hilal_Plugin
 */
function hilal() {
    return Hilal_Plugin::instance();
}

// Start the plugin
hilal();
