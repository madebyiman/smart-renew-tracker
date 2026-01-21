<?php
namespace MadeByIman\SmartRenewTracker;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// بارگذاری کتابخانه اکسل
require_once SMARTRT_PATH . 'includes/lib/SimpleXLSXGen.php';

class Admin_Menu {

    public function __construct() {
        // هوک‌های منو و تنظیمات
        add_action( 'admin_menu', [ $this, 'register_menus' ] );
        add_action( 'admin_init', [ $this, 'register_plugin_settings' ] );

        // هوک‌های مربوط به خروجی گرفتن (Export)
        add_action( 'admin_init', [ $this, 'handle_export_actions' ] );
    }

    /**
     * ثبت منوی اصلی و زیرمنوها
     */
    public function register_menus() {
        add_menu_page(
                __( 'Smart Renew Tracker', 'smart-renew-tracker' ),
                __( 'Renew Tracker', 'smart-renew-tracker' ),
                'manage_options',
                'smart-renew-tracker',
                [ $this, 'render_subscriptions_page' ],
                'dashicons-backup',
                30
        );

        add_submenu_page(
                'smart-renew-tracker',
                __( 'All Renewals', 'smart-renew-tracker' ),
                __( 'Renewals', 'smart-renew-tracker' ),
                'manage_options',
                'edit.php?post_type=smartrt_renewal'
        );

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
     * ثبت تنظیمات در دیتابیس وردپرس (White-listing)
     */
    public function register_plugin_settings() {
        // تنظیم تعداد روزهای هشدار
        register_setting( 'smartrt_settings_group', 'smartrt_alert_days', [
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'default'           => 7,
        ] );

        // تنظیم ایمیل مقصد برای نوتیفیکیشن
        register_setting( 'smartrt_settings_group', 'smartrt_target_email', [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_email',
                'default'           => get_option( 'admin_email' ),
        ] );
    }

    /**
     * مدیریت درخواست‌های اکسپورت (CSV & Excel)
     */
    public function handle_export_actions() {
        $is_csv   = isset( $_GET['export_smartrt_csv'] ) && $_GET['export_smartrt_csv'] == 1;
        $is_excel = isset( $_GET['export_smartrt_excel'] ) && $_GET['export_smartrt_excel'] == 1;

        if ( ! $is_csv && ! $is_excel ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to export this data.', 'smart-renew-tracker' ) );
        }

        $this->process_export( $is_excel ? 'excel' : 'csv' );
    }

    /**
     * منطق استخراج داده‌ها و تولید فایل
     */
    private function process_export( $type ) {
        // پاکسازی بافر برای جلوگیری از خرابی فایل خروجی
        if ( ob_get_length() ) {
            ob_end_clean();
        }

        $header = [ 'Title', 'Type', 'Renewal Date', 'Amount', 'Days Left' ];
        $rows   = [ $header ];

        $query = new \WP_Query( [
                'post_type'      => 'smartrt_renewal',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
        ] );

        $today = current_time( 'timestamp' );

        foreach ( $query->posts as $post ) {
            $renewal_date = get_post_meta( $post->ID, '_smartrt_renewal_date', true );
            $diff = '';
            if ( $renewal_date ) {
                $diff = ceil( ( strtotime( $renewal_date ) - $today ) / DAY_IN_SECONDS );
            }

            $rows[] = [
                    $post->post_title,
                    get_post_meta( $post->ID, '_smartrt_type', true ),
                    $renewal_date,
                    get_post_meta( $post->ID, '_smartrt_amount', true ),
                    $diff
            ];
        }

        $filename = 'renewals-' . date( 'Y-m-d' );

        if ( $type === 'excel' ) {
            if ( class_exists( '\MadeByIman\SmartRenewTracker\Lib\SimpleXLSXGen' ) ) {
                $xlsx = \MadeByIman\SmartRenewTracker\Lib\SimpleXLSXGen::fromArray( $rows );
                $xlsx->downloadAs( $filename . '.xlsx' );
            } else {
                wp_die( esc_html__( 'Excel library not found.', 'smart-renew-tracker' ) );
            }
        } else {
            // منطق CSV
            header( 'Content-Type: text/csv; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename=' . $filename . '.csv' );
            $output = fopen( 'php://output', 'w' );
            foreach ( $rows as $row ) {
                fputcsv( $output, $row );
            }
            fclose( $output );
        }
        exit;
    }

    public function render_subscriptions_page() {
        ?>
        <div class="wrap smartrt-wrap">
            <h1><span class="dashicons dashicons-backup"></span> <?php esc_html_e( 'Smart Renew Tracker', 'smart-renew-tracker' ); ?></h1>
            <p><?php esc_html_e( 'Manage and track your domain and hosting renewals.', 'smart-renew-tracker' ); ?></p>
            <hr>
            <p>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=smartrt_renewal' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'View All Renewals', 'smart-renew-tracker' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=smartrt_renewal&export_smartrt_csv=1' ) ); ?>" class="button button-secondary">
                    <?php esc_html_e( 'Export CSV', 'smart-renew-tracker' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=smartrt_renewal&export_smartrt_excel=1' ) ); ?>" class="button button-secondary">
                    <?php esc_html_e( 'Export Excel', 'smart-renew-tracker' ); ?>
                </a>
            </p>
        </div>
        <?php
    }

    public function render_settings_page() {
        ?>
        <div class="wrap smartrt-wrap">
            <h1><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Settings', 'smart-renew-tracker' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'smartrt_settings_group' );
                $alert_days   = get_option( 'smartrt_alert_days', 7 );
                $target_email = get_option( 'smartrt_target_email', get_option( 'admin_email' ) );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Alert Before (days)', 'smart-renew-tracker' ); ?></th>
                        <td>
                            <input type="number" name="smartrt_alert_days" value="<?php echo esc_attr( $alert_days ); ?>" min="1" max="60"/>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Notification Email', 'smart-renew-tracker' ); ?></th>
                        <td>
                            <input type="email" name="smartrt_target_email" id="smartrt_target_email" value="<?php echo esc_attr( $target_email ); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e( 'Reminders will be sent to this address.', 'smart-renew-tracker' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Test System', 'smart-renew-tracker' ); ?></th>
                        <td>
                            <button type="button" id="smartrt-test-email-btn" class="button button-secondary">
                                <?php esc_html_e( 'Send Test Email', 'smart-renew-tracker' ); ?>
                            </button>
                            <span id="smartrt-test-msg" style="margin-left: 10px; font-weight: bold;"></span>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#smartrt-test-email-btn').on('click', function() {
                    var btn = $(this);
                    var msg = $('#smartrt-test-msg');

                    btn.prop('disabled', true).text('<?php _e("Sending...", "smart-renew-tracker"); ?>');
                    msg.text('');

                    $.post(ajaxurl, {
                        action: 'smartrt_send_test_email',
                        nonce: '<?php echo wp_create_nonce("smartrt_test_nonce"); ?>'
                    }, function(response) {
                        if (response.success) {
                            msg.css('color', 'green').text(response.data);
                        } else {
                            msg.css('color', 'red').text(response.data);
                        }
                        btn.prop('disabled', false).text('<?php _e("Send Test Email", "smart-renew-tracker"); ?>');
                    });
                });
            });
        </script>
        <?php
    }
}

new Admin_Menu();