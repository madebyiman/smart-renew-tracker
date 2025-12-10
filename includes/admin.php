<?php
namespace MadeByIman\SmartRenewTracker;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // No direct access
}

// Load Excel library
// مطمئن شو که مسیر فایل در ساختارت درست است
require_once SMARTRT_PATH . 'includes/lib/SimpleXLSXGen.php';

class Admin_Menu {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menus' ] );
    }

    /**
     * Register admin menus (Main + Submenus)
     */
    public function register_menus() {
        // Main Menu
        add_menu_page(
                __( 'Smart Renew Tracker', 'smart-renew-tracker' ),
                __( 'Renew Tracker', 'smart-renew-tracker' ),
                'manage_options',
                'smart-renew-tracker',
                [ $this, 'render_subscriptions_page' ],
                'dashicons-backup',
                30
        );

        // Submenu: All Renewals
        add_submenu_page(
                'smart-renew-tracker',
                __( 'All Renewals', 'smart-renew-tracker' ),
                __( 'Renewals', 'smart-renew-tracker' ),
                'manage_options',
                'edit.php?post_type=smartrt_renewal'
        );

        // Submenu: Settings
        add_submenu_page(
                'smart-renew-tracker',
                __( 'Settings', 'smart-renew-tracker' ),
                __( 'Settings', 'smart-renew-tracker' ),
                'manage_options',
                'smart-renew-tracker-settings',
                [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Render the Subscriptions page (main overview)
     */
    public function render_subscriptions_page() {
        ?>
        <div class="wrap smartrt-wrap">
            <h1>
                <span class="dashicons dashicons-backup"></span> <?php esc_html_e( 'Smart Renew Tracker', 'smart-renew-tracker' ); ?>
            </h1>

            <p><?php esc_html_e( 'Manage and track your domain and hosting renewals.', 'smart-renew-tracker' ); ?></p>

            <hr>

            <p>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=smartrt_renewal' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'View All Renewals', 'smart-renew-tracker' ); ?>
                </a>

                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=smartrt_renewal&export_smartrt_csv=1' ) ); ?>"
                   class="button button-secondary">
                    <?php esc_html_e( 'Export CSV', 'smart-renew-tracker' ); ?>
                </a>

                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=smartrt_renewal&export_smartrt_excel=1' ) ); ?>"
                   class="button button-secondary">
                    <?php esc_html_e( 'Export Excel', 'smart-renew-tracker' ); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Render Settings Page
     */
    public function render_settings_page() {
        // Load saved settings
        $alert_days = get_option( 'smartrt_alert_days', 7 );
        ?>
        <div class="wrap smartrt-wrap">
            <h1><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Settings', 'smart-renew-tracker' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'smartrt_settings_group' ); ?>
                <?php do_settings_sections( 'smartrt_settings_group' ); ?>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Alert Before (days)', 'smart-renew-tracker' ); ?></th>
                        <td>
                            <input type="number" name="smartrt_alert_days"
                                   value="<?php echo esc_attr( $alert_days ); ?>"
                                   min="1" max="60"/>
                            <p class="description"><?php esc_html_e( 'Number of days before expiry to trigger an alert.', 'smart-renew-tracker' ); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button( __( 'Save Settings', 'smart-renew-tracker' ) ); ?>
            </form>
        </div>
        <?php
    }
}

// Initialize Admin Menu
new Admin_Menu();

// Register settings safely
add_action( 'admin_init', function () {
    register_setting( 'smartrt_settings_group', 'smartrt_alert_days', [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 7,
    ] );
} );

/**
 * Handle CSV Export
 */
add_action( 'admin_init', function () {

    if ( isset( $_GET['export_smartrt_csv'] ) && $_GET['export_smartrt_csv'] == 1 ) {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to export this data.', 'smart-renew-tracker' ) );
        }

        // Disable all output buffering
        if ( ob_get_length() ) {
            ob_end_clean();
        }

        $filename = 'renewals-' . date( 'Y-m-d' ) . '.csv';

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );

        // CSV Header
        fputcsv( $output, [
                'Title',
                'Type',
                'Renewal Date',
                'Amount',
                'Days Left'
        ] );

        // Query all renewals
        $query = new \WP_Query( [
                'post_type'      => 'smartrt_renewal',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
        ] );

        $today = current_time( 'timestamp' );

        foreach ( $query->posts as $post ) {

            $id = $post->ID;

            $type         = get_post_meta( $id, '_smartrt_type', true );
            $renewal_date = get_post_meta( $id, '_smartrt_renewal_date', true );
            $amount       = get_post_meta( $id, '_smartrt_amount', true );

            $diff = '';
            if ( $renewal_date ) {
                $renew_timestamp = strtotime( $renewal_date );
                $diff            = ceil( ( $renew_timestamp - $today ) / DAY_IN_SECONDS );
            }

            fputcsv( $output, [
                    $post->post_title,
                    $type,
                    $renewal_date,
                    $amount,
                    $diff
            ] );
        }

        fclose( $output );
        exit;
    }
} );

/**
 * Handle Excel Export
 */
add_action( 'admin_init', function () {

    if ( isset( $_GET['export_smartrt_excel'] ) && $_GET['export_smartrt_excel'] == 1 ) {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to export this data.', 'smart-renew-tracker' ) );
        }

        // Prepare rows
        $rows   = [];
        $rows[] = [ 'Title', 'Type', 'Renewal Date', 'Amount', 'Days Left' ];

        $query = new \WP_Query( [
                'post_type'      => 'smartrt_renewal',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
        ] );

        $today = current_time( 'timestamp' );

        foreach ( $query->posts as $post ) {

            $id = $post->ID;

            $type         = get_post_meta( $id, '_smartrt_type', true );
            $renewal_date = get_post_meta( $id, '_smartrt_renewal_date', true );
            $amount       = get_post_meta( $id, '_smartrt_amount', true );

            $diff = '';
            if ( $renewal_date ) {
                $renew_timestamp = strtotime( $renewal_date );
                $diff            = ceil( ( $renew_timestamp - $today ) / DAY_IN_SECONDS );
            }

            $rows[] = [
                    $post->post_title,
                    $type,
                    $renewal_date,
                    $amount,
                    $diff
            ];
        }

        // ----------------------------------
        // Generate Excel
        // ----------------------------------
        // Assuming SimpleXLSXGen is in the global namespace or Shuchkin namespace
        if ( class_exists( '\MadeByIman\SmartRenewTracker\Lib\SimpleXLSXGen' ) ) {
            $xlsx = \MadeByIman\SmartRenewTracker\Lib\SimpleXLSXGen::fromArray( $rows );
            $xlsx->downloadAs( 'renewals-' . date( 'Y-m-d' ) . '.xlsx' );
        } else {
            wp_die( esc_html__( 'Excel library not found.', 'smart-renew-tracker' ) );
        }
        exit;
    }
} );