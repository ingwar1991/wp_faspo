<?php
/**
 * @package FasPo 
 * @version 1.0
 */
/**
 * Plugin Name: FasPo
 * Plugin URI: https://wordpress.org/plugins/faspo
 * Description: FasPo enables you to get content from RSS feeds & save them as WP Posts. 
 * Author: ingwar1991
 * Author URI: http://ingwar1991.info
 * Version: 1.0
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

define( 'faspoPluginVersion', '1.1' );

require_once 'faspo_utils.php';
require_once 'faspo_settings.php';
require_once 'faspo_admin.php';
require_once 'faspo_feed.php';
require_once 'faspo_cron.php';
require_once 'faspo_image.php';
require_once 'faspo_logs.php';

faspo_admin_init();

function faspo_activate() {
    FasPo_Image::getInstance()->createDir();
    FasPo_Cron::getInstance()->activate();
}
register_activation_hook( FasPo_Utils::getInstance()->getPluginBaseFile(), 'faspo_activate' );

function faspo_deactivate() {
    FasPo_Cron::getInstance()->deactivate();
}
register_deactivation_hook( FasPo_Utils::getInstance()->getPluginBaseFile(), 'faspo_deactivate' );
