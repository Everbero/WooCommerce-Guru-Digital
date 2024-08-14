<?php
class WC_Guru_Payment_Billet extends WC_Guru_Payment_Base {
    public function process_order($order, $new_status) {
        // $billet_url = $this->get_billet_url($order);
        // precisa alterar aqui para que billet url seja instanciado em payment base
        $this->get_order_items($order, 'billet', $new_status);
    }

    private function get_billet_url($order) {
        $payment_method = $order->get_payment_method();
        $my_account_url = wc_get_page_permalink('myaccount');

        if ($payment_method === 'pagarme-banking-ticket') {
            return $order->get_meta('Banking Ticket URL') ?: $my_account_url;
        } elseif ($payment_method === 'asaas-ticket') {
            $json_string = $order->get_meta('__ASAAS_ORDER');
            $asaas_order = !empty($json_string) ? json_decode($json_string, true) : null;
            return $asaas_order['bankSlipUrl'] ?? $my_account_url;
        }
        return '';
    }
}
