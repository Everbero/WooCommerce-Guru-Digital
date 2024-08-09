<?php
abstract class WC_Guru_Payment_Base {
    protected $api;
    protected $logger;
    protected $enable_logging;
    protected $use_gateway_id;

    public function __construct() {
        $this->api = new WC_Guru_Digital_API();
        $this->logger = new WC_Logger();
        $this->enable_logging = get_option('wc_guru_enable_logging', false);
        $this->use_gateway_id = get_option('wc_guru_use_gateway_id', false);
    }

    public function get_now_time() {
        return new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    }

    abstract public function process_order($order, $new_status);

    protected function prepare_order_data($order, $payment_method, $new_status) {
        $api_token = get_option('wc_guru_api_token');
        $now = $this->get_now_time();
        $data = [
            'api_token' => $api_token,
            'id' => $this->get_order_id($order, $payment_method),
            'payment_method' => $payment_method,
            'currency' => $order->get_currency(),
            'ordered_at' => $order->get_date_created()->format('Y-m-d H:i:s'),
            'unavailable_until' => $now->format('Y-m-d H:i:s'),
            'warranty_until' => $now->add(new DateInterval('P7D'))->format('Y-m-d H:i:s'),
            'value' => $order->get_total(),
            'transaction_fee' => $order->get_total_tax(),
            'shipping_fee' => $order->get_shipping_total(),
            'net_value' => number_format((float)$order->get_total() - $order->get_total_tax() - $order->get_total_shipping() - $order->get_shipping_tax(), wc_get_price_decimals(), '.', ''),
            'source' => get_site_url(),
            'checkout_source' => get_site_url(),
            'utm_source' => $order->get_meta('_wc_order_attribution_utm_source'),
            'utm_campaign' => $order->get_meta('_wc_order_attribution_utm_campaign'),
            'utm_term' => '',
            'utm_medium' => $order->get_meta('_wc_order_attribution_utm_medium'),
            'utm_content' => $order->get_meta('_wc_order_attribution_utm_content'),
            'contact' => [
                'name' => $order->get_formatted_billing_full_name(),
                'email' => $order->get_billing_email(),
                'doc' => $order->get_meta('_billing_cpf'),
                'phone_number' => $order->get_billing_phone(),
                'address' => $order->get_billing_address_1(),
                'address_number' => '',
                'address_comp' => $order->get_billing_address_2(),
                'address_district' => $order->get_billing_state(),
                'address_city' => $order->get_billing_city(),
                'address_country' => $order->get_billing_country(),
                'address_zip_code' => $order->get_billing_postcode(),
            ],
            'subscription' => [
                'id' => '',
                // Outros campos da assinatura serão preenchidos conforme necessário
            ],
        ];
        // atualiza os campos de status do pedido
        return array_merge($data, $this->update_order_status($order, $new_status));
    }

    protected function get_order_id($order, $payment_method_is, $item_counter = 0) {
        $order_id = strval($order->get_id());

        if ($this->use_gateway_id) {
            if ($payment_method_is === 'pagarme-banking-ticket' || $payment_method_is === 'pagarme-credit-card') {
                $order_id = $order->get_meta('_wc_pagarme_transaction_id');
            } elseif ($payment_method_is === 'wc_pagarme_pix_payment_geteway') {
                $order_id = $order->get_meta('_wc_pagarme_pix_payment_transaction_id');
            }
        }

        if ($item_counter > 0) {
            $order_id .= '-' . $item_counter;
        }

        return $order_id;
    }

    protected function get_order_items($order, $payment_method_is, $new_status) {
        $items = [];
        $item_counter = 0;

        foreach ($order->get_items() as $item) {
            $data = $this->prepare_order_data($order, $payment_method_is, $new_status);

            $data['id'] = $this->get_order_id($order, $payment_method_is, $item_counter);
            $data['product']['id'] = strval($item->get_product_id());
            $data['product']['name'] = $item->get_name();
            $data['product']['qty'] = $item->get_quantity();
            $data['product']['cost'] = $item->get_total();

            // Checa se o pagamento foi por cartão de crédito
            if ($payment_method_is == 'credit_card') {
                $totalComJuros = $order->get_meta('Total paid');
                $totalSemJuros = $order->get_total();
                $numItens = $order->get_item_count();
                $data['value'] = $item->get_total() + $this->calculate_interest_per_item($totalComJuros, $totalSemJuros, $numItens);
            } else {
                $data['value'] = $item->get_total(); // Valor do item sem alterações para outros métodos de pagamento
            }

            $response = $this->api->send_order_to_guru($data);
            update_post_meta($order->get_id(), '_guru_status', $response);

            if ($response instanceof WP_Error) {
                $this->log('Error sending item ' . $data['product']['name'] . ' to Guru Digital: ' . $response->get_error_message(), 'error');
                $order->add_order_note("item: " . $data['product']['name'] . " Guru: Erro no processamento");
            } else {
                $this->log('Response from Guru Digital for item ' . $data['product']['name'] . ': ' . $response);
                $order->add_order_note("item: " . $data['product']['name'] . " Resposta da Guru: " . $response);
            }

            $item_counter++;
            $this->log('DADOS ENVIADOS: ' . json_encode($data));
        }

    }

    public function update_order_status($order, $new_status) {  

        $this->log('Mudando status para: ' . print_r($new_status) . ' ' . print_r($order->status), 'error');
        
        $status_mapping = [
            'pending' => 'waiting_payment',
            'processing' => 'approved',
            'on-hold' => 'in_analysis',
            'completed' => 'completed',
            'cancelled' => 'canceled',
            'refunded' => 'refunded',
            'failed' => 'abandoned',
        ];

        if (!isset($status_mapping[$order->status])) {
            $this->log('Unmapped status: ' . $order->status, 'error');
            return; // Status não mapeado, então não faz nada
        }

        $now = $this->get_now_time()->format('Y-m-d H:i:s');
        $aprovadoEm = '';
        $canceladoEm = '';
        $status = $status_mapping[$order->status];

        // procura e atualiza status e datas de aprovação e cancelamento
        if($order->status == 'completed' || $order->status == 'processing') {
            
            $aprovadoEm = $now;
        }

        if($order->status == 'cancelled' || $order->status == 'refunded' || $order->status == 'failed') {
            $canceladoEm = $now;
        }

        $data = [
            'status' => $status,
            'approved_at' => $aprovadoEm,
            'canceled_at' => $canceladoEm,
        ];


        return $data;
    }

    private function log($message, $level = 'info') {
        if ($this->enable_logging) {
            $this->logger->log($level, $message, array('source' => 'wc-guru-digital'));
        }
    }

    private function calculate_interest_per_item($total_with_interest, $total_without_interest, $num_items) {
        $added_interest = $total_with_interest - $total_without_interest;
        $added_value_per_item = $added_interest / $num_items;
        return $added_value_per_item;
    }
}
