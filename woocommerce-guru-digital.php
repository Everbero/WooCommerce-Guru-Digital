<?php
defined('ABSPATH') || exit;

/**
 * Plugin Name: Woocommerce Guru Digital
 * Plugin URI: https://3xweb.site
 * Description: Envia pedidos do woocommerce para a guru digital
 * Author URI: https://3xweb.site/
 * Version: 2.0.0
 * Requires at least: 5.2
 * Tested up to: 6.3.3
 * WC requires at least: 6.0
 * WC tested up to: 9.1.2
 * Text Domain: wc-guru
 * Domain Path: /languages
 */

class WC_Guru_Digital {
    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    private function includes() {
        require_once plugin_dir_path(__FILE__) . 'includes/class-wc-guru-digital-settings.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-wc-guru-digital-order.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-wc-guru-digital-api.php';
        require_once plugin_dir_path(__FILE__) . 'includes/payments/class-wc-guru-payment-base.php';
        require_once plugin_dir_path(__FILE__) . 'includes/payments/class-wc-guru-payment-billet.php';
        require_once plugin_dir_path(__FILE__) . 'includes/payments/class-wc-guru-payment-credit-card.php';
        require_once plugin_dir_path(__FILE__) . 'includes/payments/class-wc-guru-payment-other.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-wc-guru-product-metabox.php';

    }

    private function init_hooks() {
        add_action('plugins_loaded', [$this, 'init_classes']);
    }

    public function init_classes() {
        new WC_Guru_Digital_Settings();
        new WC_Guru_Digital_Order();
        new WC_Guru_Digital_API();
    }
}

new WC_Guru_Digital();
