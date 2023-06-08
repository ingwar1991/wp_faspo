<?php
/**
 * Created by ingwar1991 from 0-1-10.com
 */

require_once 'faspo_utils.php';
require_once 'faspo_settings.php';
require_once 'faspo_cron.php';

function faspo_options_page_html() {
    include 'faspo_form.php';
}

function faspo_options_page() {
    add_submenu_page(
        'options-general.php',
        'FasPo Options',
        'FasPo',
        'manage_options',
        'FasPo',
        'faspo_options_page_html'
    );
}

function faspo_settings_set( $settings ) {
    FasPo_Settings::getInstance()->set( $settings, 1 );
}

function faspo_options_save() {
    if ( $_SERVER['REQUEST_METHOD'] == 'POST' && !empty( $_POST['faspo_settings'] ) ) {
        $settings = [
            'frequency' => !empty( $_POST['faspo_settings']['frequency'] )
                ? sanitize_text_field( $_POST['faspo_settings']['frequency'] )
                : 1,
            'exclude_category' => !empty( $_POST['faspo_settings']['exclude_category'] )
                ? sanitize_text_field( $_POST['faspo_settings']['exclude_category'] )
                : '',
            'feeds' => !empty( $_POST['faspo_settings']['feeds'] )
                ? sanitize_text_field( $_POST['faspo_settings']['feeds'] )
                : '[]'
        ];

        $settings['feeds'] = json_decode( stripslashes( $settings['feeds'] ), 1 );
        $settings['feeds'] = is_array( $settings['feeds'] )
            ? $settings['feeds']
            : array();

        foreach( $settings['feeds'] as $i => $feed ) {
            foreach( $feed as $key => $data ) {
                if ( !is_array( $data ) ) {
                    $feed[ $key ] = sanitize_text_field( $data );
                    continue;
                }

                foreach( $data as $k => $d ) {
                    $feed[ $key ][ $k ] = sanitize_text_field( $d );
                }
            }

            $settings['feeds'][ $i ] = $feed;
        }

        do_action( 'faspo_settings_save', $settings );

        header( 'Location: ' . FasPo_Utils::getInstance()->getSettingsLink() );
        die;
    }
}

function faspo_add_action_links( $actions ) {
    return array_merge(
        [ '<a href="' . FasPo_Utils::getInstance()->getSettingsLink() . '">Settings</a>' ],
        $actions
    );
}

function faspo_activation_redirect( $plugin ) {
    if ( $plugin == FasPo_Utils::getInstance()->getPluginBasename() ) {
        exit( wp_redirect( FasPo_Utils::getInstance()->getSettingsLink() ) );
    }
}

function faspo_exclude_posts_from_home( $query ) {
    $excludeCatId = FasPo_Settings::getInstance()->get( 'exclude_category' );
    if ( $excludeCatId && $query->is_home ) {
        $query->set( 'cat', '-' . $excludeCatId );
    }

    return $query;
}

function faspo_enqueue_admin_page_attachments() {
    if ( get_current_screen()->id != 'settings_page_FasPo' ) {
        return;
    }

    wp_register_style( 'faspo-admin-chosen', '/' . FasPo_Utils::getInstance()->getPluginPath( 'attachments/jquery.chosen.min.css' ), false, faspoPluginVersion );
    wp_register_style( 'faspo-admin', '/' . FasPo_Utils::getInstance()->getPluginPath( 'attachments/faspo_style.css' ), [
        'faspo-admin-chosen'
    ], faspoPluginVersion );
    wp_enqueue_style( 'faspo-admin' );

    wp_register_script( 'faspo-admin-chosen', '/' . FasPo_Utils::getInstance()->getPluginPath( 'attachments/jquery.chosen.min.js' ), [
        'jquery-core'
    ], faspoPluginVersion );
    wp_register_script( 'faspo-admin', '/' . FasPo_Utils::getInstance()->getPluginPath( 'attachments/faspo_script.js' ), [
        'faspo-admin-chosen'
    ], faspoPluginVersion );
    wp_enqueue_script( 'faspo-admin' );
}

function faspo_enqueue_post_attachments() {
    global $post;
    if ( empty( $post ) || empty( $post->post_content ) ) {
        return;
    }

    // getting postContentKeyword from post_content without `<!-- `
    $postContentStart = substr( $post->post_content, 5, 45 );
    for( $feedKey = 0; $feedKey <= FasPo_Settings::getInstance()->feedsCount(); $feedKey++ ) {
        if ( $postContentStart != FasPo_Utils::getInstance()->getPostContentKeyword( $feedKey ) ) {
            continue;
        }

        wp_register_script( 'faspo-post-verify', '/' . FasPo_Utils::getInstance()->getPluginPath( 'attachments/faspo_verify.js' ), false, faspoPluginVersion );
        wp_enqueue_script( 'faspo-post-verify' );
    }
}

function faspo_upgrade( $upgrader, $options ) {
    $faspoPluginPathName = FasPo_Utils::getInstance()->getPluginBasename();
    if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
        foreach( $options['plugins'] as $pluginPathName ) {
            if ( $pluginPathName == $faspoPluginPathName ) {
                FasPo_Settings::getInstance()->backup();
            }
        }
    }
}

function faspo_admin_init() {
    add_action( 'faspo_settings_save', 'faspo_settings_set' );

    add_action('admin_menu', 'faspo_options_page');
    add_action('admin_init', 'faspo_options_save');

    add_filter( 'plugin_action_links_' . FasPo_Utils::getInstance()->getPluginBasename(), 'faspo_add_action_links' );
    add_action( 'activated_plugin', 'faspo_activation_redirect' );

    add_filter( 'pre_get_posts', 'faspo_exclude_posts_from_home' );

    add_action( 'admin_enqueue_scripts', 'faspo_enqueue_admin_page_attachments' );
    add_action( 'wp_enqueue_scripts', 'faspo_enqueue_post_attachments' );

    FasPo_Cron::getInstance()->init();
}
