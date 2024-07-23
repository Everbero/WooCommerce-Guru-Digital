<?php
class WC_Guru_Digital_Order {
    public function __construct() {
        add_action('woocommerce_order_status_changed', [$this, 'handle_order_status_changed'], 10, 3);
        add_action('add_meta_boxes', [$this, 'add_order_meta_box']);
    }

    public function handle_order_status_changed($order_id, $old_status, $new_status) {
        $order = new WC_Order($order_id);
        $payment_method = $order->get_payment_method();

        $handler_class = $this->get_payment_handler_class($payment_method);
        if ($handler_class) {
            $handler = new $handler_class();
            $handler->process_order($order, $new_status);
        }
    }

    private function get_payment_handler_class($payment_method) {
        $handlers = [
            'pagarme-banking-ticket' => 'WC_Guru_Payment_Billet',
            'asaas-ticket' => 'WC_Guru_Payment_Billet',
            'wc_pagarme_pix_payment_geteway' => 'WC_Guru_Payment_Other',
            'pagarme-credit-card' => 'WC_Guru_Payment_Credit_Card',
        ];

        return $handlers[$payment_method] ?? 'WC_Guru_Payment_Other';
    }

    public function add_order_meta_box() {
        add_meta_box(
            'wc_guru_meta_box',
            'Guru Digital Status',
            [$this, 'display_order_meta_box'],
            'shop_order',
            'side',
            'default'
        );
    }

    public function display_order_meta_box($post) {
        $order_id = $post->ID;
        echo '<p>Status da Guru Digital: <strong>' . get_post_meta($order_id, '_guru_status', true) . '</strong></p>';
    }
}
