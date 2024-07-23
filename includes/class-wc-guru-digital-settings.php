<?php
class WC_Guru_Digital_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_plugin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_plugin_menu() {
        add_options_page(
            'Configurações Guru Digital',
            'Guru Digital',
            'manage_options',
            'wc-guru-settings',
            [$this, 'create_admin_page']
        );
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1>Configurações Guru Digital</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wc_guru_settings');
                do_settings_sections('wc-guru-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting('wc_guru_settings', 'wc_guru_api_token');
        register_setting('wc_guru_settings', 'wc_guru_api_url');
        register_setting('wc_guru_settings', 'wc_guru_enable_logging');
        register_setting('wc_guru_settings', 'wc_guru_use_gateway_id');

        add_settings_section('wc_guru_settings_section', 'Configurações API', null, 'wc-guru-settings');

        add_settings_field('wc_guru_api_token', 'API Token', [$this, 'api_token_callback'], 'wc-guru-settings', 'wc_guru_settings_section');
        add_settings_field('wc_guru_api_url', 'API URL', [$this, 'api_url_callback'], 'wc-guru-settings', 'wc_guru_settings_section');
        add_settings_field('wc_guru_enable_logging', 'Habilitar Logging', [$this, 'enable_logging_callback'], 'wc-guru-settings', 'wc_guru_settings_section');
        add_settings_field('wc_guru_use_gateway_id', 'Informar nr do pedido do gateway', [$this, 'use_gateway_id_callback'], 'wc-guru-settings', 'wc_guru_settings_section');
    }

    public function api_token_callback() {
        $token = get_option('wc_guru_api_token');
        echo '<input type="text" id="wc_guru_api_token" name="wc_guru_api_token" value="' . esc_attr($token) . '" />';
    }

    public function api_url_callback() {
        $url = get_option('wc_guru_api_url');
        echo '<input type="text" id="wc_guru_api_url" name="wc_guru_api_url" value="' . esc_attr($url) . '" />';
    }

    public function enable_logging_callback() {
        $enabled = get_option('wc_guru_enable_logging');
        echo '<input type="checkbox" id="wc_guru_enable_logging" name="wc_guru_enable_logging" value="1" ' . checked(1, $enabled, false) . ' />';
    }

    public function use_gateway_id_callback() {
        $use_gateway_id = get_option('wc_guru_use_gateway_id');
        echo '<input type="checkbox" id="wc_guru_use_gateway_id" name="wc_guru_use_gateway_id" value="1" ' . checked(1, $use_gateway_id, false) . ' />';
        echo '<p class="description">Se marcado, o ID do pedido informado ao Guru será o ID do pedido do gateway e não o ID do pedido do WooCommerce.</p>';
    }
}
