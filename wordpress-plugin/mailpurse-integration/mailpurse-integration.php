<?php
/*
Plugin Name: MailPurse Integration
Description: Send WordPress and WooCommerce events to MailPurse Automations.
Version: 0.1.0
Author: MailPurse
*/

if (!defined('ABSPATH')) {
    exit;
}

define('MAILPURSE_INTEGRATION_VERSION', '0.1.0');
define('MAILPURSE_INTEGRATION_PLUGIN_FILE', __FILE__);
define('MAILPURSE_INTEGRATION_PLUGIN_DIR', plugin_dir_path(__FILE__));

define('MAILPURSE_INTEGRATION_OPTION_KEY', 'mailpurse_integration_settings');

define('MAILPURSE_INTEGRATION_QUEUE_TABLE', 'mailpurse_event_queue');

define('MAILPURSE_INTEGRATION_CRON_HOOK', 'mailpurse_integration_process_queue');

define('MAILPURSE_INTEGRATION_DEFAULT_EVENTS', wp_json_encode([
    'wp_user_registered' => true,
    'wp_user_updated' => false,
    'woo_customer_created' => true,
    'woo_order_created' => true,
    'woo_order_paid' => true,
    'woo_order_completed' => true,
    'woo_order_refunded' => true,
    'woo_order_cancelled' => true,
    'woo_abandoned_checkout' => false,
]));

require_once MAILPURSE_INTEGRATION_PLUGIN_DIR . 'src/MailPurseClient.php';
require_once MAILPURSE_INTEGRATION_PLUGIN_DIR . 'src/MailPurseQueue.php';
require_once MAILPURSE_INTEGRATION_PLUGIN_DIR . 'src/MailPurseSettingsPage.php';
require_once MAILPURSE_INTEGRATION_PLUGIN_DIR . 'src/MailPurseHooks.php';

register_activation_hook(MAILPURSE_INTEGRATION_PLUGIN_FILE, function () {
    \MailPurseIntegration\MailPurseQueue::activate();
});

register_deactivation_hook(MAILPURSE_INTEGRATION_PLUGIN_FILE, function () {
    \MailPurseIntegration\MailPurseQueue::deactivate();
});

add_action('plugins_loaded', function () {
    \MailPurseIntegration\MailPurseSettingsPage::register();
    \MailPurseIntegration\MailPurseQueue::register();
    \MailPurseIntegration\MailPurseHooks::register();
});
