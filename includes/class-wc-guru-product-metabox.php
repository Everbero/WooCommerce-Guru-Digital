<?php
class WC_Guru_Product_Metabox {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_metabox']);
        add_action('wp_ajax_wc_guru_send_test_order', [$this, 'send_test_order']);
        // add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function add_metabox() {
        add_meta_box(
            'wc_guru_product_metabox',
            'Enviar Pedido Fictício à Guru',
            [$this, 'render_metabox'],
            'product',
            'side',
            'default'
        );
    }

    public function render_metabox($post) {
        $response_message = get_post_meta($post->ID, '_wc_guru_test_order_response', true);

        echo '<p>Utilize este recurso para pré-cadastrar um produto no Guru. Isso enviará um pedido fictício com valores zerados.</p>';
        echo '<button id="wc-guru-send-test-order" type="button" class="button button-primary">Enviar Pedido Fictício</button>';
        wp_nonce_field('wc_guru_send_test_order', 'wc_guru_test_order_nonce');

        if ($response_message) {
            echo '<p><strong>Último Resultado:</strong></p>';
            echo '<p>' . esc_html($response_message) . '</p>';
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

    public function send_test_order() {
        check_ajax_referer('wc_guru_send_test_order_nonce', 'nonce');

        $product_id = intval($_POST['product_id']);
        if (!$product_id) {
            wp_send_json_error('ID do produto inválido.');
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error('Produto não encontrado.');
        }

        // Prepara os dados fictícios do pedido
        $data = [
            'api_token' => get_option('wc_guru_api_token'),
            'id' => strval($product_id),
            'payment_method' => 'bank_transfer',
            'status' => 'approved',
            'currency' => get_woocommerce_currency(),
            'ordered_at' => current_time('mysql'),
            'approved_at' => current_time('mysql'),
            'canceled_at' => '',
            'unavailable_until' => current_time('mysql'),
            'warranty_until' => current_time('mysql'),
            'value' => 0,
            'transaction_fee' => 0,
            'shipping_fee' => 0,
            'net_value' => 0,
            'source' => get_site_url(),
            'checkout_source' => get_site_url(),
            'utm_source' => '',
            'utm_campaign' => '',
            'utm_term' => '',
            'utm_medium' => '',
            'utm_content' => '',
            'contact' => [
                'name' => 'Teste',
                'email' => 'teste@example.com',
                'doc' => '',
                'phone_number' => '',
                'address' => '',
                'address_number' => '',
                'address_comp' => '',
                'address_district' => '',
                'address_city' => '',
                'address_country' => '',
                'address_zip_code' => '',
            ],
            'product' => [
                'id' => strval($product_id),
                'name' => $product->get_name(),
                'qty' => 1,
                'cost' => 0,
            ],
            'subscription' => [
                'id' => '',
            ],
        ];

        // Envia os dados para a Guru
        $api = new WC_Guru_Digital_API();
        $response = $api->send_order_to_guru($data);

        if ($response instanceof WP_Error) {
            $error_message = 'Erro ao enviar pedido: ' . $response->get_error_message();
            update_post_meta($product_id, '_wc_guru_test_order_response', $error_message);
        } else {
            $success_message = "Item: " . $data['product']['name'] . "\n" . $this->format_response_for_order_notes($response);
            update_post_meta($product_id, '_wc_guru_test_order_response', $success_message);
            wp_send_json_success($success_message);

        }
    }

    private function format_response_for_order_notes($response) {
        // Decodifica o JSON da resposta
        $response_data = json_decode($response, true);

        // Determina a cor da bolinha (amarela para erros, verde para sucesso)
        $color = 'yellow'; // Cor padrão
        if (isset($response_data['status']) && $response_data['status'] === 'success') {
            $color = 'green';
        } else if (!empty($response_data)) {
            $color = 'yellow';
        }

        // Ícones de bolinhas
        $bullet_icon = $color === 'green' ? '🟢' : '🟡';

        // Formata a resposta de forma legível
        $formatted_response = '';
        foreach ($response_data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $formatted_response .= ucfirst($key) . ': ' . $value . "\n";
        }

        return $bullet_icon . ' Resposta da Guru: ' . $formatted_response;
    }
}

new WC_Guru_Product_Metabox();
