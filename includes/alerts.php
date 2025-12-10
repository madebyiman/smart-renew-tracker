<?php
namespace MadeByIman\SmartRenewTracker;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // prevent direct access
}

class Alerts {

    public function __construct() {
        add_action( 'admin_notices', [ $this, 'show_admin_alerts' ] );
    }

    /**
     * Show alerts in WordPress dashboard for soon-to-expire renewals
     */
    public function show_admin_alerts() {

        // 1. Check current screen
        $screen = get_current_screen();
        if ( ! $screen ) {
            return;
        }

        // Only show on Dashboard or our Plugin's list page
        // Note: The ID for custom post type list is usually 'edit-{post_type}'
        if ( 'dashboard' !== $screen->base && 'edit-smartrt_renewal' !== $screen->id ) {
            return;
        }

        // 2. Get Options & Date
        $alert_days = get_option( 'smartrt_alert_days', 7 ); // Updated prefix
        $today      = current_time( 'timestamp' ); // Use timestamp for better comparison

        // 3. Query Renewals
        $query = new \WP_Query( [
            'post_type'      => 'smartrt_renewal', // Updated prefix
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ] );

        if ( empty( $query->posts ) ) {
            return;
        }

        $expiring = [];

        foreach ( $query->posts as $renewal ) {

            $id           = $renewal->ID;
            $renewal_date = get_post_meta( $id, '_smartrt_renewal_date', true ); // Updated prefix

            if ( ! $renewal_date ) {
                continue;
            }

            // Calculate difference
            $renew_timestamp = strtotime( $renewal_date );
            $diff            = ceil( ( $renew_timestamp - $today ) / DAY_IN_SECONDS );

            // Check if within alert range (and not expired too long ago, e.g. -30 days)
            if ( $diff <= $alert_days && $diff >= 0 ) {
                $expiring[] = [
                    'title' => get_the_title( $id ),
                    'days'  => (int) $diff,
                    'date'  => $renewal_date,
                    'link'  => get_edit_post_link( $id ),
                ];
            }
        }

        if ( empty( $expiring ) ) {
            return;
        }

        // 4. Display Admin Notice (Securely)
        echo '<div class="notice notice-warning is-dismissible">';

        printf(
            '<p><strong>%s</strong> %s</p>',
            esc_html__( '⚠️ Smart Renew Tracker:', 'smart-renew-tracker' ),
            sprintf(
                esc_html__( 'You have %d renewals coming up soon:', 'smart-renew-tracker' ),
                count( $expiring )
            )
        );

        echo '<ul style="margin-left:20px;">';

        foreach ( $expiring as $item ) {
            // ✅ FIXED: All variables are now escaped properly
            printf(
                '<li><a href="%s"><strong>%s</strong></a> – %s <strong>%d</strong> %s (%s)</li>',
                esc_url( $item['link'] ),
                esc_html( $item['title'] ),
                esc_html__( 'renews in', 'smart-renew-tracker' ),
                absint( $item['days'] ), // Safe integer output
                esc_html__( 'day(s)', 'smart-renew-tracker' ),
                esc_html( $item['date'] )
            );
        }

        echo '</ul>';
        echo '</div>';
    }
}

new Alerts();