<?php
/*
Plugin Name: Smart Renew Tracker
Description: Track and manage your domain and hosting renewals inside WordPress. Get renewal reminders and stay organized.
Version: 1.1.1
Author: Iman Hossein Gholizadeh
Author URI: https://www.linkedin.com/in/iman-hossein-gholizadeh/
License: GPL v2 or later
Text Domain: smart-renew-tracker
*/

namespace MadeByIman\SmartRenewTracker;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // No direct access allowed.
}

// Plugin paths
define( 'SMARTRT_PATH', plugin_dir_path( __FILE__ ) );
define( 'SMARTRT_URL', plugin_dir_url( __FILE__ ) );

// Includes - (We will fix these files next)
require_once SMARTRT_PATH . 'includes/admin.php';
require_once SMARTRT_PATH . 'includes/alerts.php';
require_once SMARTRT_PATH . 'includes/email-notifications.php';

class Plugin {

    public function __construct() {
        add_action( 'init', [ $this, 'register_subscription_cpt' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_subscription_metabox' ] );
        add_action( 'save_post_smartrt_renewal', [ $this, 'save_subscription_meta' ], 10, 2 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );
        add_filter( 'manage_smartrt_renewal_posts_columns', [ $this, 'add_custom_columns' ] );
        add_action( 'manage_smartrt_renewal_posts_custom_column', [ $this, 'render_custom_columns' ], 10, 2 );
    }

    public function register_subscription_cpt() {

        $labels = [
                'name'          => __( 'Renewals', 'smart-renew-tracker' ),
                'singular_name' => __( 'Renewal', 'smart-renew-tracker' ),
                'add_new'       => __( 'Add New Renewal', 'smart-renew-tracker' ),
                'add_new_item'  => __( 'Add New Renewal', 'smart-renew-tracker' ),
                'edit_item'     => __( 'Edit Renewal', 'smart-renew-tracker' ),
                'new_item'      => __( 'New Renewal', 'smart-renew-tracker' ),
                'menu_name'     => __( 'Renewals', 'smart-renew-tracker' ),
        ];

        $args = [
                'labels'          => $labels,
                'public'          => false,
                'show_ui'         => true,
                'show_in_menu'    => false, // Menu handled in admin.php
                'supports'        => [ 'title' ],
                'capability_type' => 'post',
                'menu_icon'       => 'dashicons-update',
        ];

        register_post_type( 'smartrt_renewal', $args );
    }

    public function add_subscription_metabox() {
        add_meta_box(
                'smartrt_details',
                __( 'Renewal Details', 'smart-renew-tracker' ),
                [ $this, 'render_metabox' ],
                'smartrt_renewal',
                'normal',
                'high'
        );
    }

    public function render_metabox( $post ) {
        // Use empty strings as defaults to avoid undefined variable warnings
        $type         = '';
        $renewal_date = '';
        $amount       = '';

        if ( $post->post_status !== 'auto-draft' ) {
            $type         = get_post_meta( $post->ID, '_smartrt_type', true );
            $renewal_date = get_post_meta( $post->ID, '_smartrt_renewal_date', true );
            $amount       = get_post_meta( $post->ID, '_smartrt_amount', true );
        }

        wp_nonce_field( 'smartrt_save_meta', 'smartrt_meta_nonce' );
        ?>
        <p>
            <label for="smartrt_type"><strong><?php esc_html_e( 'Type:', 'smart-renew-tracker' ); ?></strong></label><br>
            <select name="smartrt_type" id="smartrt_type">
                <option value=""><?php esc_html_e( 'Select Type', 'smart-renew-tracker' ); ?></option>
                <option value="domain"  <?php selected( $type, 'domain' ); ?>><?php esc_html_e( 'Domain', 'smart-renew-tracker' ); ?></option>
                <option value="hosting" <?php selected( $type, 'hosting' ); ?>><?php esc_html_e( 'Hosting', 'smart-renew-tracker' ); ?></option>
                <option value="ssl"     <?php selected( $type, 'ssl' ); ?>><?php esc_html_e( 'SSL', 'smart-renew-tracker' ); ?></option>
                <option value="other"   <?php selected( $type, 'other' ); ?>><?php esc_html_e( 'Other', 'smart-renew-tracker' ); ?></option>
            </select>
        </p>

        <p>
            <label for="smartrt_renewal_date"><strong><?php esc_html_e( 'Renewal Date:', 'smart-renew-tracker' ); ?></strong></label><br>
            <input type="date"
                   name="smartrt_renewal_date"
                   id="smartrt_renewal_date"
                   value="<?php echo esc_attr( $renewal_date ); ?>">
        </p>

        <p>
            <label for="smartrt_amount"><strong><?php esc_html_e( 'Amount ($):', 'smart-renew-tracker' ); ?></strong></label><br>
            <input type="number"
                   step="0.01"
                   name="smartrt_amount"
                   id="smartrt_amount"
                   value="<?php echo esc_attr( $amount ); ?>">
        </p>
        <?php
    }

    public function save_subscription_meta( $post_id, $post ) {
        // 1. Verify Nonce (Sanitized first)
        if ( ! isset( $_POST['smartrt_meta_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['smartrt_meta_nonce'] ) ), 'smartrt_save_meta' ) ) {
            return;
        }

        // 2. Check Autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // 3. Check Post Type
        if ( 'smartrt_renewal' !== $post->post_type ) {
            return;
        }

        // 4. Check Permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // 5. Sanitize and Save
        $type         = isset( $_POST['smartrt_type'] ) ? sanitize_text_field( wp_unslash( $_POST['smartrt_type'] ) ) : '';
        $renewal_date = isset( $_POST['smartrt_renewal_date'] ) ? sanitize_text_field( wp_unslash( $_POST['smartrt_renewal_date'] ) ) : '';
        $amount       = isset( $_POST['smartrt_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['smartrt_amount'] ) ) : '';

        update_post_meta( $post_id, '_smartrt_type', $type );
        update_post_meta( $post_id, '_smartrt_renewal_date', $renewal_date );
        update_post_meta( $post_id, '_smartrt_amount', $amount );
    }

    public function enqueue_admin_styles() {
        // Only enqueue on our post type page to improve performance
        $screen = get_current_screen();
        if ( $screen && 'smartrt_renewal' === $screen->post_type ) {
            wp_enqueue_style(
                    'smartrt-admin-style',
                    SMARTRT_URL . 'assets/css/admin-style.css',
                    [],
                    '1.0.1'
            );
        }
    }

    public function add_custom_columns( $columns ) {
        $new_columns = [];

        foreach ( $columns as $key => $label ) {
            $new_columns[ $key ] = $label;

            if ( 'title' === $key ) {
                $new_columns['type']         = __( 'Type', 'smart-renew-tracker' );
                $new_columns['days_left']    = __( 'Days Left', 'smart-renew-tracker' );
                $new_columns['renewal_date'] = __( 'Renewal Date', 'smart-renew-tracker' );
                $new_columns['amount']       = __( 'Amount ($)', 'smart-renew-tracker' );
            }
        }

        return $new_columns;
    }

    public function render_custom_columns( $column, $post_id ) {

        switch ( $column ) {

            case 'type':
                $type = get_post_meta( $post_id, '_smartrt_type', true );
                if ( $type ) {
                    echo esc_html( ucfirst( $type ) );
                } else {
                    echo '-';
                }
                break;

            case 'days_left':
                $renewal_date = get_post_meta( $post_id, '_smartrt_renewal_date', true );

                if ( ! $renewal_date ) {
                    echo '-';
                    break;
                }

                $today = current_time( 'timestamp' ); // Use WordPress time
                $renew_timestamp = strtotime( $renewal_date );
                $diff  = ( $renew_timestamp - $today );
                $days  = ceil( $diff / DAY_IN_SECONDS ); // Ceil is better for approaching deadlines

                // Color logic (using inline styles for simplicity, but classes are better in CSS)
                if ( $days < 0 ) {
                    printf( '<span style="color:#cc0000;font-weight:bold;">%s</span>', esc_html__( 'Expired', 'smart-renew-tracker' ) );
                } elseif ( $days <= 10 ) {
                    printf( '<span style="color:#cc0000;font-weight:bold;">%s</span>', intval( $days ) );
                } elseif ( $days <= 30 ) {
                    printf( '<span style="color:#dba617;font-weight:bold;">%s</span>', intval( $days ) );
                } else {
                    printf( '<span style="color:#33a853;font-weight:bold;">%s</span>', intval( $days ) );
                }

                break;

            case 'renewal_date':
                $date = get_post_meta( $post_id, '_smartrt_renewal_date', true );
                echo $date ? esc_html( $date ) : '-';
                break;

            case 'amount':
                $amount = get_post_meta( $post_id, '_smartrt_amount', true );
                echo $amount ? '$' . esc_html( $amount ) : '-';
                break;
        }
    }
}

register_activation_hook( __FILE__, function() {
    if ( ! wp_next_scheduled( 'smartrt_daily_email_check' ) ) {
        wp_schedule_event( time(), 'daily', 'smartrt_daily_email_check' );
    }
} );

register_deactivation_hook( __FILE__, function() {
    wp_clear_scheduled_hook( 'smartrt_daily_email_check' );
} );


new Plugin();