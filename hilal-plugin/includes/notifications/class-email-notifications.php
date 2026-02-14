<?php
/**
 * Email Notifications Handler
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Email Notifications Class
 */
class Hilal_Email_Notifications {

    /**
     * Constructor
     */
    public function __construct() {
        // Hook into announcement publish
        add_action( 'hilal_send_email_notification', array( $this, 'send_announcement_email' ) );

        // Hook into report status changes
        add_action( 'hilal_sighting_report_approved', array( $this, 'send_report_approved_email' ) );
        add_action( 'hilal_sighting_report_rejected', array( $this, 'send_report_rejected_email' ) );

        // Register REST endpoint for email subscription
        add_action( 'rest_api_init', array( $this, 'register_subscribe_endpoint' ) );

        // Customize email sender
        add_filter( 'wp_mail_from', array( $this, 'custom_mail_from' ) );
        add_filter( 'wp_mail_from_name', array( $this, 'custom_mail_from_name' ) );
    }

    /**
     * Register subscription REST endpoint
     */
    public function register_subscribe_endpoint() {
        register_rest_route(
            'hilal/v1',
            '/subscribe',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_subscribe' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'email'    => array(
                        'required'          => true,
                        'type'              => 'string',
                        'format'            => 'email',
                        'sanitize_callback' => 'sanitize_email',
                    ),
                    'name'     => array(
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'channels' => array(
                        'type'              => 'string',
                        'default'           => 'email',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );

        // Unsubscribe endpoint
        register_rest_route(
            'hilal/v1',
            '/unsubscribe',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'handle_unsubscribe' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'email' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'format'            => 'email',
                        'sanitize_callback' => 'sanitize_email',
                    ),
                    'token' => array(
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );
    }

    /**
     * Handle subscription request
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_subscribe( $request ) {
        global $wpdb;

        $email    = $request->get_param( 'email' );
        $name     = $request->get_param( 'name' );
        $channels = $request->get_param( 'channels' );

        $table_name = $wpdb->prefix . 'hilal_subscribers';

        // Check if already subscribed
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table_name WHERE email = %s AND unsubscribed_at IS NULL",
                $email
            )
        );

        if ( $existing ) {
            return new WP_REST_Response(
                array(
                    'success' => true,
                    'message' => __( 'You are already subscribed.', 'hilal' ),
                ),
                200
            );
        }

        // Generate verification token
        $token = wp_generate_password( 32, false );

        // Insert subscriber
        $result = $wpdb->insert(
            $table_name,
            array(
                'email'              => $email,
                'name'               => $name,
                'channels'           => $channels,
                'verified'           => 0,
                'verification_token' => $token,
                'created_at'         => current_time( 'mysql' ),
            )
        );

        if ( false === $result ) {
            return new WP_Error(
                'subscribe_failed',
                __( 'Failed to subscribe. Please try again.', 'hilal' ),
                array( 'status' => 500 )
            );
        }

        // Send verification email
        $this->send_verification_email( $email, $name, $token );

        return new WP_REST_Response(
            array(
                'success' => true,
                'message' => __( 'Please check your email to verify your subscription.', 'hilal' ),
            ),
            201
        );
    }

    /**
     * Handle unsubscribe request
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function handle_unsubscribe( $request ) {
        global $wpdb;

        $email = $request->get_param( 'email' );
        $token = $request->get_param( 'token' );

        $table_name = $wpdb->prefix . 'hilal_subscribers';

        // Verify token if provided
        $where = array( 'email' => $email );
        if ( $token ) {
            $where['verification_token'] = $token;
        }

        $wpdb->update(
            $table_name,
            array( 'unsubscribed_at' => current_time( 'mysql' ) ),
            $where
        );

        return new WP_REST_Response(
            array(
                'success' => true,
                'message' => __( 'You have been unsubscribed successfully.', 'hilal' ),
            ),
            200
        );
    }

    /**
     * Send verification email
     *
     * @param string $email Email address.
     * @param string $name  Subscriber name.
     * @param string $token Verification token.
     */
    private function send_verification_email( $email, $name, $token ) {
        $verify_url = add_query_arg(
            array(
                'action' => 'hilal_verify_email',
                'email'  => urlencode( $email ),
                'token'  => $token,
            ),
            home_url( '/' )
        );

        $subject = __( 'Verify your Hilal subscription', 'hilal' );

        $message = $this->get_email_template(
            'verify',
            array(
                'name'       => $name ?: __( 'Subscriber', 'hilal' ),
                'verify_url' => $verify_url,
            )
        );

        $this->send_email( $email, $subject, $message );
    }

    /**
     * Send announcement email to all subscribers
     *
     * @param int $post_id Announcement post ID.
     */
    public function send_announcement_email( $post_id ) {
        $subscribers = $this->get_verified_subscribers();

        if ( empty( $subscribers ) ) {
            return;
        }

        $title_en = get_field( 'title_en', $post_id );
        $body_en  = get_field( 'body_en', $post_id );
        $type     = get_field( 'type', $post_id );

        $subject = sprintf(
            '[%s] %s',
            get_bloginfo( 'name' ),
            $title_en
        );

        foreach ( $subscribers as $subscriber ) {
            $unsubscribe_url = add_query_arg(
                array(
                    'action' => 'hilal_unsubscribe',
                    'email'  => urlencode( $subscriber->email ),
                    'token'  => $subscriber->verification_token,
                ),
                home_url( '/' )
            );

            $message = $this->get_email_template(
                'announcement',
                array(
                    'name'            => $subscriber->name ?: __( 'Subscriber', 'hilal' ),
                    'title'           => $title_en,
                    'body'            => $body_en,
                    'type'            => $type,
                    'url'             => get_permalink( $post_id ),
                    'unsubscribe_url' => $unsubscribe_url,
                )
            );

            $this->send_email( $subscriber->email, $subject, $message );
        }
    }

    /**
     * Send report approved email
     *
     * @param int $post_id Report post ID.
     */
    public function send_report_approved_email( $post_id ) {
        $user_id = get_field( 'observer_user_id', $post_id );

        if ( ! $user_id ) {
            return;
        }

        $user = get_userdata( $user_id );

        if ( ! $user ) {
            return;
        }

        $subject = __( 'Your moon sighting report has been approved', 'hilal' );

        $message = $this->get_email_template(
            'report_approved',
            array(
                'name' => $user->display_name,
            )
        );

        $this->send_email( $user->user_email, $subject, $message );
    }

    /**
     * Send report rejected email
     *
     * @param int $post_id Report post ID.
     */
    public function send_report_rejected_email( $post_id ) {
        $user_id = get_field( 'observer_user_id', $post_id );

        if ( ! $user_id ) {
            return;
        }

        $user = get_userdata( $user_id );

        if ( ! $user ) {
            return;
        }

        $admin_notes = get_field( 'admin_notes', $post_id );

        $subject = __( 'Your moon sighting report was not accepted', 'hilal' );

        $message = $this->get_email_template(
            'report_rejected',
            array(
                'name'   => $user->display_name,
                'reason' => $admin_notes ?: __( 'No specific reason provided.', 'hilal' ),
            )
        );

        $this->send_email( $user->user_email, $subject, $message );
    }

    /**
     * Send HTML email
     *
     * @param string $to      Recipient email.
     * @param string $subject Email subject.
     * @param string $message HTML message.
     * @return bool
     */
    private function send_email( $to, $subject, $message ) {
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
        );

        return wp_mail( $to, $subject, $message, $headers );
    }

    /**
     * Get email template
     *
     * @param string $template Template name.
     * @param array  $vars     Template variables.
     * @return string
     */
    private function get_email_template( $template, $vars ) {
        $site_name = get_bloginfo( 'name' );
        $site_url  = home_url( '/' );

        // Base styles
        $styles = '
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #2271b1; }
            .header h1 { margin: 0; color: #2271b1; }
            .content { padding: 30px 0; }
            .button { display: inline-block; padding: 12px 24px; background: #2271b1; color: #fff; text-decoration: none; border-radius: 5px; }
            .footer { padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; text-align: center; }
        ';

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style><?php echo $styles; ?></style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1><?php echo esc_html( $site_name ); ?></h1>
                </div>
                <div class="content">
                    <?php
                    switch ( $template ) {
                        case 'verify':
                            ?>
                            <p><?php printf( __( 'Assalamu Alaikum %s,', 'hilal' ), esc_html( $vars['name'] ) ); ?></p>
                            <p><?php _e( 'Thank you for subscribing to Hilal notifications. Please click the button below to verify your email address.', 'hilal' ); ?></p>
                            <p style="text-align: center;">
                                <a href="<?php echo esc_url( $vars['verify_url'] ); ?>" class="button">
                                    <?php _e( 'Verify Email', 'hilal' ); ?>
                                </a>
                            </p>
                            <?php
                            break;

                        case 'announcement':
                            ?>
                            <p><?php printf( __( 'Assalamu Alaikum %s,', 'hilal' ), esc_html( $vars['name'] ) ); ?></p>
                            <h2><?php echo esc_html( $vars['title'] ); ?></h2>
                            <div><?php echo wp_kses_post( $vars['body'] ); ?></div>
                            <p style="text-align: center;">
                                <a href="<?php echo esc_url( $vars['url'] ); ?>" class="button">
                                    <?php _e( 'Read More', 'hilal' ); ?>
                                </a>
                            </p>
                            <?php
                            break;

                        case 'report_approved':
                            ?>
                            <p><?php printf( __( 'Assalamu Alaikum %s,', 'hilal' ), esc_html( $vars['name'] ) ); ?></p>
                            <p><?php _e( 'Alhamdulillah! Your moon sighting report has been reviewed and approved by our committee.', 'hilal' ); ?></p>
                            <p><?php _e( 'JazakAllahu Khairan for your contribution to the Muslim community.', 'hilal' ); ?></p>
                            <?php
                            break;

                        case 'report_rejected':
                            ?>
                            <p><?php printf( __( 'Assalamu Alaikum %s,', 'hilal' ), esc_html( $vars['name'] ) ); ?></p>
                            <p><?php _e( 'We have reviewed your moon sighting report and unfortunately we could not accept it at this time.', 'hilal' ); ?></p>
                            <p><strong><?php _e( 'Reason:', 'hilal' ); ?></strong> <?php echo esc_html( $vars['reason'] ); ?></p>
                            <p><?php _e( 'Please feel free to submit future sighting reports. JazakAllahu Khairan.', 'hilal' ); ?></p>
                            <?php
                            break;
                    }
                    ?>
                </div>
                <div class="footer">
                    <p><?php echo esc_html( $site_name ); ?> - <a href="<?php echo esc_url( $site_url ); ?>"><?php echo esc_url( $site_url ); ?></a></p>
                    <?php if ( isset( $vars['unsubscribe_url'] ) ) : ?>
                        <p><a href="<?php echo esc_url( $vars['unsubscribe_url'] ); ?>"><?php _e( 'Unsubscribe', 'hilal' ); ?></a></p>
                    <?php endif; ?>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Get verified subscribers
     *
     * @return array
     */
    private function get_verified_subscribers() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'hilal_subscribers';

        return $wpdb->get_results(
            "SELECT * FROM $table_name WHERE verified = 1 AND unsubscribed_at IS NULL"
        );
    }

    /**
     * Custom mail from address
     *
     * @param string $from Original from address.
     * @return string
     */
    public function custom_mail_from( $from ) {
        $custom = get_option( 'hilal_email_from_address' );
        return $custom ? $custom : $from;
    }

    /**
     * Custom mail from name
     *
     * @param string $name Original from name.
     * @return string
     */
    public function custom_mail_from_name( $name ) {
        $custom = get_option( 'hilal_email_from_name' );
        return $custom ? $custom : $name;
    }
}
