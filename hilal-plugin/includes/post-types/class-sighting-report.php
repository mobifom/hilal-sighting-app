<?php
/**
 * Crescent Sighting Custom Post Type
 *
 * Simplified to PDF attachment and details only.
 * Works with or without ACF.
 *
 * @package Hilal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hilal Crescent Sighting Post Type Class
 */
class Hilal_Sighting_Report {

    /**
     * Post type slug
     *
     * @var string
     */
    const POST_TYPE = 'sighting_report';

    /**
     * Register the post type
     */
    public static function register() {
        $labels = array(
            'name'                  => _x( 'Crescent Sightings', 'Post type general name', 'hilal' ),
            'singular_name'         => _x( 'Crescent Sighting', 'Post type singular name', 'hilal' ),
            'menu_name'             => _x( 'Crescent Sightings', 'Admin Menu text', 'hilal' ),
            'name_admin_bar'        => _x( 'Crescent Sighting', 'Add New on Toolbar', 'hilal' ),
            'add_new'               => __( 'Add New', 'hilal' ),
            'add_new_item'          => __( 'Add New Crescent Sighting', 'hilal' ),
            'new_item'              => __( 'New Crescent Sighting', 'hilal' ),
            'edit_item'             => __( 'Edit Crescent Sighting', 'hilal' ),
            'view_item'             => __( 'View Crescent Sighting', 'hilal' ),
            'all_items'             => __( 'All Sightings', 'hilal' ),
            'search_items'          => __( 'Search Crescent Sightings', 'hilal' ),
            'not_found'             => __( 'No crescent sightings found.', 'hilal' ),
            'not_found_in_trash'    => __( 'No crescent sightings found in Trash.', 'hilal' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'crescent-sighting' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 6,
            'menu_icon'          => 'dashicons-visibility',
            'supports'           => array( 'title' ),
            'show_in_rest'       => true,
            'rest_base'          => 'crescent-sightings',
        );

        register_post_type( self::POST_TYPE, $args );

        // Register meta boxes (native WordPress - works without ACF)
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_meta_boxes' ), 10, 2 );

        // Auto-generate title on save
        add_filter( 'wp_insert_post_data', array( __CLASS__, 'auto_generate_title' ), 10, 2 );
    }

    /**
     * Add meta boxes
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'hilal_sighting_details',
            __( 'Crescent Sighting Details', 'hilal' ),
            array( __CLASS__, 'render_meta_box' ),
            self::POST_TYPE,
            'normal',
            'high'
        );
    }

    /**
     * Render meta box
     *
     * @param WP_Post $post Post object.
     */
    public static function render_meta_box( $post ) {
        wp_nonce_field( 'hilal_sighting_save', 'hilal_sighting_nonce' );

        $status      = get_post_meta( $post->ID, 'status', true ) ?: 'pending';
        $details     = get_post_meta( $post->ID, 'details', true );
        $attachment  = get_post_meta( $post->ID, 'attachment', true );
        $admin_notes = get_post_meta( $post->ID, 'admin_notes', true );

        // Get attachment info
        $attachment_url = '';
        $attachment_name = '';
        if ( $attachment ) {
            $attachment_url = wp_get_attachment_url( $attachment );
            $attachment_name = basename( get_attached_file( $attachment ) );
        }
        ?>
        <style>
            .hilal-meta-field { margin-bottom: 20px; }
            .hilal-meta-field label { display: block; font-weight: 600; margin-bottom: 5px; }
            .hilal-meta-field select, .hilal-meta-field textarea { width: 100%; }
            .hilal-meta-field textarea { min-height: 120px; }
            .hilal-meta-field .description { color: #666; font-size: 12px; margin-top: 5px; }
            .hilal-status-approved { background: #d4edda !important; border-color: #28a745 !important; }
            .hilal-attachment-preview { background: #f0f0f0; padding: 10px; border-radius: 5px; margin-top: 10px; }
            .hilal-attachment-preview a { text-decoration: none; }
        </style>

        <div class="hilal-meta-field">
            <label for="hilal_status"><?php esc_html_e( 'Review Status', 'hilal' ); ?> <span style="color: red;">*</span></label>
            <select name="hilal_status" id="hilal_status" class="<?php echo $status === 'approved' ? 'hilal-status-approved' : ''; ?>">
                <option value="pending" <?php selected( $status, 'pending' ); ?>><?php esc_html_e( 'Pending Review', 'hilal' ); ?></option>
                <option value="approved" <?php selected( $status, 'approved' ); ?>><?php esc_html_e( 'Approved', 'hilal' ); ?></option>
                <option value="rejected" <?php selected( $status, 'rejected' ); ?>><?php esc_html_e( 'Rejected', 'hilal' ); ?></option>
            </select>
            <p class="description"><?php esc_html_e( 'Set to "Approved" to display on the public page.', 'hilal' ); ?></p>
        </div>

        <div class="hilal-meta-field">
            <label for="hilal_attachment"><?php esc_html_e( 'PDF Attachment', 'hilal' ); ?></label>
            <input type="hidden" name="hilal_attachment" id="hilal_attachment" value="<?php echo esc_attr( $attachment ); ?>">
            <button type="button" class="button" id="hilal_upload_btn"><?php esc_html_e( 'Upload PDF', 'hilal' ); ?></button>
            <button type="button" class="button" id="hilal_remove_btn" style="<?php echo $attachment ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'hilal' ); ?></button>
            <?php if ( $attachment_url ) : ?>
                <div class="hilal-attachment-preview" id="hilal_attachment_preview">
                    📄 <a href="<?php echo esc_url( $attachment_url ); ?>" target="_blank"><?php echo esc_html( $attachment_name ); ?></a>
                </div>
            <?php else : ?>
                <div class="hilal-attachment-preview" id="hilal_attachment_preview" style="display:none;"></div>
            <?php endif; ?>
            <p class="description"><?php esc_html_e( 'Upload a PDF document for the crescent sighting.', 'hilal' ); ?></p>
        </div>

        <div class="hilal-meta-field">
            <label for="hilal_details"><?php esc_html_e( 'Details', 'hilal' ); ?></label>
            <textarea name="hilal_details" id="hilal_details" rows="6"><?php echo esc_textarea( $details ); ?></textarea>
            <p class="description"><?php esc_html_e( 'Details about the sighting including location, time, conditions, etc.', 'hilal' ); ?></p>
        </div>

        <div class="hilal-meta-field">
            <label for="hilal_admin_notes"><?php esc_html_e( 'Admin Notes', 'hilal' ); ?></label>
            <textarea name="hilal_admin_notes" id="hilal_admin_notes" rows="3"><?php echo esc_textarea( $admin_notes ); ?></textarea>
            <p class="description"><?php esc_html_e( 'Internal notes (not shown publicly).', 'hilal' ); ?></p>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var mediaUploader;

            $('#hilal_upload_btn').on('click', function(e) {
                e.preventDefault();

                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }

                mediaUploader = wp.media({
                    title: '<?php esc_html_e( 'Select PDF', 'hilal' ); ?>',
                    button: { text: '<?php esc_html_e( 'Use this PDF', 'hilal' ); ?>' },
                    library: { type: 'application/pdf' },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#hilal_attachment').val(attachment.id);
                    $('#hilal_attachment_preview').html('📄 <a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a>').show();
                    $('#hilal_remove_btn').show();
                });

                mediaUploader.open();
            });

            $('#hilal_remove_btn').on('click', function(e) {
                e.preventDefault();
                $('#hilal_attachment').val('');
                $('#hilal_attachment_preview').hide();
                $(this).hide();
            });

            // Highlight status change
            $('#hilal_status').on('change', function() {
                if ($(this).val() === 'approved') {
                    $(this).addClass('hilal-status-approved');
                } else {
                    $(this).removeClass('hilal-status-approved');
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Save meta box data
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     */
    public static function save_meta_boxes( $post_id, $post ) {
        // Check nonce
        if ( ! isset( $_POST['hilal_sighting_nonce'] ) || ! wp_verify_nonce( $_POST['hilal_sighting_nonce'], 'hilal_sighting_save' ) ) {
            return;
        }

        // Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save fields
        if ( isset( $_POST['hilal_status'] ) ) {
            $status = sanitize_text_field( $_POST['hilal_status'] );
            if ( in_array( $status, array( 'pending', 'approved', 'rejected' ), true ) ) {
                update_post_meta( $post_id, 'status', $status );
            }
        }

        if ( isset( $_POST['hilal_details'] ) ) {
            update_post_meta( $post_id, 'details', sanitize_textarea_field( $_POST['hilal_details'] ) );
        }

        if ( isset( $_POST['hilal_attachment'] ) ) {
            update_post_meta( $post_id, 'attachment', absint( $_POST['hilal_attachment'] ) );
        }

        if ( isset( $_POST['hilal_admin_notes'] ) ) {
            update_post_meta( $post_id, 'admin_notes', sanitize_textarea_field( $_POST['hilal_admin_notes'] ) );
        }
    }

    /**
     * Auto-generate title on save
     *
     * @param array $data    Post data.
     * @param array $postarr Post array.
     * @return array
     */
    public static function auto_generate_title( $data, $postarr ) {
        if ( self::POST_TYPE !== $data['post_type'] ) {
            return $data;
        }

        // Generate title from date
        if ( empty( $data['post_title'] ) || 'Auto Draft' === $data['post_title'] ) {
            $data['post_title'] = sprintf(
                __( 'Crescent Sighting - %s', 'hilal' ),
                gmdate( 'd M Y' )
            );
        }

        return $data;
    }

    /**
     * Get reports by status
     *
     * @param string $status Report status.
     * @param int    $limit  Number of reports.
     * @return array
     */
    public static function get_by_status( $status = 'pending', $limit = 10 ) {
        $args = array(
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => $limit,
            'post_status'    => 'publish',
            'meta_key'       => 'status',
            'meta_value'     => $status,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        $query   = new WP_Query( $args );
        $reports = array();

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $reports[] = self::format_report_data( $post );
            }
        }

        return $reports;
    }

    /**
     * Get reports by user
     *
     * @param int $user_id User ID.
     * @param int $limit   Number of reports.
     * @return array
     */
    public static function get_by_user( $user_id, $limit = 10 ) {
        $args = array(
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => $limit,
            'post_status'    => 'publish',
            'meta_key'       => 'observer_user_id',
            'meta_value'     => $user_id,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        $query   = new WP_Query( $args );
        $reports = array();

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $reports[] = self::format_report_data( $post );
            }
        }

        return $reports;
    }

    /**
     * Count pending reports
     *
     * @return int
     */
    public static function count_pending() {
        $args = array(
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_key'       => 'status',
            'meta_value'     => 'pending',
            'fields'         => 'ids',
        );

        $query = new WP_Query( $args );
        return $query->found_posts;
    }

    /**
     * Format report data for API/display
     *
     * @param WP_Post $post Post object.
     * @return array
     */
    public static function format_report_data( $post ) {
        $attachment_id = get_post_meta( $post->ID, 'attachment', true );
        $formatted_attachment = null;

        if ( $attachment_id ) {
            $file_path = get_attached_file( $attachment_id );
            $formatted_attachment = array(
                'id'       => $attachment_id,
                'url'      => wp_get_attachment_url( $attachment_id ),
                'filename' => basename( $file_path ),
                'filesize' => file_exists( $file_path ) ? filesize( $file_path ) : 0,
            );
        }

        return array(
            'id'           => $post->ID,
            'title'        => get_the_title( $post->ID ),
            'attachment'   => $formatted_attachment,
            'details'      => get_post_meta( $post->ID, 'details', true ),
            'status'       => get_post_meta( $post->ID, 'status', true ),
            'admin_notes'  => get_post_meta( $post->ID, 'admin_notes', true ),
            'submitted_at' => get_the_date( 'c', $post ),
        );
    }

    /**
     * Create a new crescent sighting
     *
     * @param array $data Sighting data (details and attachment_id).
     * @return int|WP_Error Post ID or error.
     */
    public static function create_report( $data ) {
        // Create post
        $post_id = wp_insert_post( array(
            'post_type'   => self::POST_TYPE,
            'post_title'  => sprintf( __( 'Crescent Sighting - %s', 'hilal' ), gmdate( 'd M Y H:i' ) ),
            'post_status' => 'publish',
        ) );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        // Update details field
        if ( ! empty( $data['details'] ) ) {
            update_post_meta( $post_id, 'details', sanitize_textarea_field( $data['details'] ) );
        }

        // Handle PDF attachment - single attachment ID
        if ( ! empty( $data['attachment_id'] ) ) {
            update_post_meta( $post_id, 'attachment', absint( $data['attachment_id'] ) );
        }

        // Set default status
        update_post_meta( $post_id, 'status', 'pending' );

        // Trigger notification to admin
        do_action( 'hilal_new_sighting_report', $post_id );

        return $post_id;
    }

    /**
     * Update report status
     *
     * @param int    $post_id     Post ID.
     * @param string $status      New status.
     * @param string $admin_notes Admin notes.
     * @param int    $reviewer_id Reviewer user ID.
     * @return bool
     */
    public static function update_status( $post_id, $status, $admin_notes = '', $reviewer_id = null ) {
        $valid_statuses = array( 'pending', 'approved', 'rejected' );
        if ( ! in_array( $status, $valid_statuses, true ) ) {
            return false;
        }

        update_post_meta( $post_id, 'status', $status );

        if ( $admin_notes ) {
            update_post_meta( $post_id, 'admin_notes', $admin_notes );
        }

        if ( $reviewer_id ) {
            update_post_meta( $post_id, 'reviewed_by', $reviewer_id );
        }

        // Trigger actions based on status
        if ( 'approved' === $status ) {
            do_action( 'hilal_sighting_report_approved', $post_id );
        } elseif ( 'rejected' === $status ) {
            do_action( 'hilal_sighting_report_rejected', $post_id );
        }

        return true;
    }
}
