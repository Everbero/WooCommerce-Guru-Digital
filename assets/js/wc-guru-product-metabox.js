jQuery(document).ready(function($) {
    $('#wc-guru-send-test-order').on('click', function(e) {
        e.preventDefault();
        var $button = $(this);
        $button.prop('disabled', true).text('Enviando...');
        $.ajax({
            url: wc_guru_product_metabox.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wc_guru_send_test_order',
                nonce: wc_guru_product_metabox.nonce,
                product_id: $('#post_ID').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                } else {
                    alert('Erro: ' + response.data);
                }
                $button.prop('disabled', false).text('Enviar Pedido Fictício');
            },
            error: function() {
                alert('Erro ao processar o pedido.');
                $button.prop('disabled', false).text('Enviar Pedido Fictício');
            }
        });
    });
});
