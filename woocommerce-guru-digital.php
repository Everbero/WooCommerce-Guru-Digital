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
        
        // Hooks para adicionar a coluna personalizada
        add_filter('manage_edit-shop_order_columns', [$this, 'add_guru_response_column'], 20);
        add_action('manage_shop_order_posts_custom_column', [$this, 'populate_guru_response_column']);
        add_filter('manage_edit-shop_order_sortable_columns', [$this, 'make_guru_response_column_sortable']);
        add_action('pre_get_posts', [$this, 'sort_orders_by_guru_response']);
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
    
    // Adiciona a coluna "Guru Response" na lista de pedidos
    public function add_guru_response_column($columns) {
        $new_columns = array();

        foreach ($columns as $key => $column) {
            $new_columns[$key] = $column;
            if ('order_total' === $key) {
                $new_columns['guru_response'] = __('Guru Response', 'wc-guru');
            }
        }

        return $new_columns;
    }

    // Preenche a coluna "Guru Response" com o valor do meta
public function populate_guru_response_column($column) {
    global $post;

    if ('guru_response' === $column) {
        $guru_response = get_post_meta($post->ID, '_guru_status', true);
        
        if ($guru_response) {
            $decoded_response = json_decode($guru_response, true); // Decodifica o JSON

            if (json_last_error() === JSON_ERROR_NONE) {
                // Percorre o array para exibir os valores
                foreach ($decoded_response as $key => $value) {
                    // Verifica se o valor é um array ou string
                    if (is_array($value)) {
                        echo implode(', ', $value); // Exibe os valores do array como string
                    } else {
                        echo esc_html($value); // Exibe o valor como string
                    }
                }
            } else {
                echo __('Invalid JSON', 'wc-guru'); // Caso o JSON seja inválido
            }
        }
    }
}


    // Torna a coluna "Guru Response" ordenável
    public function make_guru_response_column_sortable($columns) {
        $columns['guru_response'] = '_guru_status';
        return $columns;
    }

    // Ordena a lista de pedidos pela coluna "Guru Response"
    public function sort_orders_by_guru_response($query) {
        if (!is_admin()) {
            return;
        }

        $orderby = $query->get('orderby');

        if ('_wc_guru_test_order_response' === $orderby) {
            $query->set('meta_key', '_guru_status');
            $query->set('orderby', 'meta_value');
        }
    }
}

new WC_Guru_Digital();
