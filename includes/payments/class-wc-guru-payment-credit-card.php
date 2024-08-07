<?php
class WC_Guru_Payment_Credit_Card extends WC_Guru_Payment_Base {
    public function process_order($order, $new_status) {
        $this->get_order_items($order, 'credit_card', [], $new_status);
    }
}
