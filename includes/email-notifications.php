<?php
namespace MadeByIman\SmartRenewTracker;

class Email_Notifications {

    public function __construct() {
        add_action( 'smartrt_daily_email_check', [ $this, 'process_notifications' ] );

        // هوک برای پاسخ به درخواست دکمه تست (AJAX)
        add_action( 'wp_ajax_smartrt_send_test_email', [ $this, 'handle_test_email_ajax' ] );
    }

    public function handle_test_email_ajax() {
        if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'smartrt_test_nonce', 'nonce', false ) ) {
            wp_send_json_error( __( 'Permission denied.', 'smart-renew-tracker' ) );
        }

        $target_email = get_option( 'smartrt_target_email', get_option( 'admin_email' ) );

        $test_item = [[
                'title' => 'Test Service (Example)',
                'days'  => 5,
                'date'  => date('Y-m-d', strtotime('+5 days')),
                'type'  => 'domain'
        ]];

        $result = $this->send_html_email( $target_email, $test_item, true );

        if ( $result ) {
            wp_send_json_success( __( 'Test email sent successfully!', 'smart-renew-tracker' ) );
        } else {
            wp_send_json_error( __( 'Failed to send email. Check your server SMTP.', 'smart-renew-tracker' ) );
        }
    }

    // متد ارسال ایمیل (کمی تغییر دادیم تا نتیجه رو برگردونه)
    private function send_html_email( $to, $items, $is_test = false ) {
        $prefix = $is_test ? '[TEST] ' : '⚠️ ';
        $subject = $prefix . __( 'Renewals Alert', 'smart-renew-tracker' );

        ob_start();
        ?>
        <div style="font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee;">
            <h2 style="color: #d63638;"><?php echo $is_test ? 'Test Notification' : 'Upcoming Renewals'; ?></h2>
            <?php foreach($items as $item): ?>
                <p><strong><?php echo esc_html($item['title']); ?></strong>: <?php echo esc_html($item['days']); ?> days left.</p>
            <?php endforeach; ?>
        </div>
        <?php
        $message = ob_get_clean();
        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];

        return wp_mail( $to, $subject, $message, $headers );
    }

    public function process_notifications() {
        $alert_days   = get_option( 'smartrt_alert_days', 7 );
        $target_email = get_option( 'smartrt_target_email', get_option( 'admin_email' ) );
        $today        = current_time( 'timestamp' );

        // ۲. کوئری برای پیدا کردن تمام موارد ثبت شده
        $query = new \WP_Query( [
                'post_type'      => 'smartrt_renewal',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
        ] );

        $expiring_items = [];

        foreach ( $query->posts as $renewal ) {
            $renewal_date = get_post_meta( $renewal->ID, '_smartrt_renewal_date', true );
            if ( ! $renewal_date ) continue;

            $diff = ceil( ( strtotime( $renewal_date ) - $today ) / DAY_IN_SECONDS );

            // اگر مورد در بازه هشدار (مثلاً ۷ روز مانده) باشد
            if ( $diff <= $alert_days && $diff >= 0 ) {
                $expiring_items[] = [
                        'title' => get_the_title( $renewal->ID ),
                        'days'  => $diff,
                        'date'  => $renewal_date,
                        'type'  => get_post_meta( $renewal->ID, '_smartrt_type', true )
                ];
            }
        }

        if ( ! empty( $expiring_items ) ) {
            $this->send_html_email( $target_email, $expiring_items );
        }
    }
}

new Email_Notifications();