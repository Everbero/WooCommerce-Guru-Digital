<?php
class WC_Guru_Payment_Other extends WC_Guru_Payment_Base { 
    public function process_order($order, $new_status) {
        $this->get_order_items($order, 'bank_transfer', [], $new_status);
    }
}
