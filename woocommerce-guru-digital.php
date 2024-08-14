<?php
defined('ABSPATH') || exit;

/**
 * Plugin Name: Woocommerce Guru Digital
 * Plugin URI: https://github.com/Everbero/WooCommerce-Guru-Digital
 * Description: <b>Envia pedidos do woocommerce para a guru digital</b>
 * Author: Douglas E.
 * Author URI: https://github.com/Everbero
 * Version: 2.0.2
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
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_action_links']);
        add_filter('plugin_row_meta', [$this, 'add_row_meta'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function init_classes() {
        new WC_Guru_Digital_Settings();
        new WC_Guru_Digital_Order();
        new WC_Guru_Digital_API();
    }

    public function add_action_links($links) {
        $settings_link = '<a href="options-general.php?page=wc-guru-settings">' . __('Settings', 'wc-guru') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function add_row_meta($links, $file) {
        if (plugin_basename(__FILE__) === $file) {
            $new_links = array(
                '<a href="mailto:info@3xweb.site">' . __('Support', 'wc-guru') . '</a>',
                '<a href="https://3xweb.site/documentation">' . __('Documentation', 'wc-guru') . '</a>',
            );
            $links = array_merge($links, $new_links);
        }
        return $links;
    }

    public function enqueue_styles($hook_suffix) {
        if ($hook_suffix === 'plugins.php') {
            wp_enqueue_style('wc-guru-custom-styles', plugin_dir_url(__FILE__) . 'assets/css/custom-styles.css');
        }
    }

    public function enqueue_scripts($hook) {
        if ($hook === 'post.php' || $hook === 'post-new.php') {
            wp_enqueue_script('wc-guru-product-metabox', plugins_url('assets/js/wc-guru-product-metabox.js', __FILE__), ['jquery'], null, true);
            wp_localize_script('wc-guru-product-metabox', 'wc_guru_product_metabox', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc_guru_send_test_order_nonce')
            ]);
        }
    }
    
}

new WC_Guru_Digital();
