<?php
/**
 * Created by ingwar1991 from 0-1-10.com
 */

require_once 'faspo_settings.php';
require_once 'faspo_feed.php';

class FasPo_Cron {
    private static $instance;

    private function __construct() {}

    public static function getInstance() {
        if ( !self::$instance ) {
            self::$instance = new FasPo_Cron();
        }

        return self::$instance;
    }

    public function getSchedule() {
        if ( !FasPo_Settings::getInstance()->get( 'frequency' ) ) {
            return false;
        }

        return FasPo_Settings::getInstance()->get( 'frequency' ) . '_hour';
    }

    public function init() {
        $this->createSchedule();
        add_action( 'faspo_process_feed_hook', 'faspo_process_feed' );
    }

    private function createSchedule() {
        add_filter( 'cron_schedules', 'faspo_cron_custom_intervals' );
    }

    public function activate() {
        $this->deactivate();

        if ( ( $schedule = $this->getSchedule() ) ) {
            wp_schedule_event(
                time(),
                $schedule,
                'faspo_process_feed_hook'
            );
        }
    }

    public function deactivate() {
        if ( ( $timestamp = wp_next_scheduled( 'faspo_process_feed_hook' ) ) ) {
            wp_unschedule_event( $timestamp, 'faspo_process_feed_hook' );
        }
    }
}

function faspo_cron_custom_intervals( $schedules ) {
    if ( ( $schedule = FasPo_Cron::getInstance()->getSchedule() ) ) {
        $freq = FasPo_Settings::getInstance()->get( 'frequency' );

        $schedules[ $schedule ] = array(
            'interval' => $freq * 60 * 60,
            'display'  => __( 'Every ' . $freq . ' hour(s)' )
        );
    }

    return $schedules;
}
