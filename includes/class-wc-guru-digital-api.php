<?php
class WC_Guru_Digital_API {
    private $api_url;
    private $logger;

    public function __construct() {
        $this->api_url = get_option('wc_guru_api_url', 'https://digitalmanager.guru/api/v1/marketplaces/generic/926885cb-1a19-4608-aa78-9601f98fe25b');
        $this->logger = new WC_Logger();
    }

    public function send_order_to_guru($data) {
        $this->log('Sending order data to Guru Digital: ' . json_encode($data));

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            $this->log('Error sending order data to Guru Digital: ' . $error, 'error');
        } else {
            $this->log('Response from Guru Digital: ' . $response . ' (HTTP Code: ' . $http_code . ')');
        }

        return $response;
    }

    public function calculate_interest_per_item($total_with_interest, $total_without_interest, $num_items) {
        $added_interest = $total_with_interest - $total_without_interest;
        $added_value_per_item = $added_interest / $num_items;
        return $added_value_per_item;
    }

    private function log($message, $level = 'info') {
        $this->logger->log($level, $message, array('source' => 'wc-guru-digital'));
    }
}
