<?php
class WC_Guru_Payment_Other extends WC_Guru_Payment_Base {
       
    public function process_order($order, $new_status) {
        $this->get_order_items($order, $order->get_payment_method_title( "view" ));
        $this->update_order_status($order, $new_status);
    }
}
