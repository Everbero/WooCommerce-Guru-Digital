# WooCommerce Guru Digital

![Plugin Version](https://img.shields.io/badge/version-1.0.6-blue)
![WordPress Tested Up To](https://img.shields.io/badge/tested%20up%20to-5.7-blue)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-blue)
![PHP Version](https://img.shields.io/badge/php-%3E%3D%207.2-blue)

**Plugin Name:** Woocommerce Guru Digital  
**Plugin URI:** https://3xweb.site  
**Description:** Envia pedidos do WooCommerce para a Guru Digital  
**Version:** 1.0.6  
**Requires at least:** 4.4  
**Tested up to:** 5.7  
**WC requires at least:** 3.0  
**WC tested up to:** 5.4  
**Text Domain:** wc-guru  
**Domain Path:** /languages  

---

## Descrição

O plugin WooCommerce Guru Digital permite integrar sua loja WooCommerce com o sistema de gestão da Guru Digital, enviando automaticamente informações dos pedidos. Além disso, o plugin inclui a funcionalidade de pré-cadastrar produtos na Guru Digital através de um metabox no admin do WooCommerce.

## Funcionalidades

- Envio automático de pedidos do WooCommerce para a Guru Digital.
- Suporte para diferentes métodos de pagamento, incluindo boleto bancário e cartão de crédito.
- Configuração de ID do pedido com base em metadados específicos do método de pagamento.
- Opção para utilizar o ID do pedido do gateway em vez do ID do pedido do WooCommerce.
- Logging opcional para monitorar o envio dos pedidos.
- Metabox nos produtos para enviar pedidos fictícios à Guru Digital, permitindo o pré-cadastro de produtos.

## Instalação

1. Faça o download do plugin.
2. No painel de administração do WordPress, vá para **Plugins** > **Adicionar novo**.
3. Clique em **Enviar plugin** no topo da página.
4. Selecione o arquivo ZIP do plugin e clique em **Instalar agora**.
5. Após a instalação, clique em **Ativar** para ativar o plugin.

## Configuração

Após ativar o plugin, você pode configurar suas opções:

1. No painel de administração do WordPress, vá para **Configurações** > **Guru Digital**.
2. Preencha as configurações necessárias:
   - **API Token:** Token de autenticação da Guru Digital.
   - **API URL:** URL da API da Guru Digital.
   - **Habilitar Logging:** Ative esta opção se desejar que o plugin registre logs das operações.
   - **Informar nr do pedido do gateway:** Se ativada, o ID do pedido informado à Guru será o ID do pedido do gateway em vez do ID do pedido do WooCommerce.

## Uso

### Envio Automático de Pedidos

O plugin enviará automaticamente os dados dos pedidos para a Guru Digital sempre que o status do pedido mudar. A lógica do envio considera os seguintes cenários:

- **Boleto Bancário:** O ID do pedido será o conteúdo de `_wc_pagarme_transaction_id` se o método de pagamento for `pagarme-banking-ticket`.
- **Cartão de Crédito:** O ID do pedido será o conteúdo de `_wc_pagarme_transaction_id` se o método de pagamento for `pagarme-credit-card`.
- **PIX:** O ID do pedido será o conteúdo de `_wc_pagarme_pix_payment_transaction_id` se o método de pagamento for `wc_pagarme_pix_payment_geteway`.
- **Outros Métodos de Pagamento:** O ID do pedido será o ID do pedido do WooCommerce, a menos que a opção "Informar nr do pedido do gateway" esteja ativada.

### Metabox para Pré-Cadastro de Produtos

Nos produtos do WooCommerce, haverá um metabox chamado "Enviar Pedido Fictício à Guru". Este metabox permite enviar um pedido fictício à Guru Digital para pré-cadastrar o produto com valores zerados. 

1. No painel de administração do WordPress, vá para **Produtos**.
2. Edite um produto existente ou crie um novo produto.
3. No lado direito da página de edição do produto, localize o metabox "Enviar Pedido Fictício à Guru".
4. Leia a descrição que explica a funcionalidade do metabox.
5. Clique no botão **Enviar Pedido Fictício**.
6. Uma solicitação será enviada à Guru Digital, e você verá uma mensagem de sucesso ou erro conforme o resultado da operação.

## Desenvolvimento

### Estrutura de Arquivos

- `woocommerce-guru-digital.php`: Arquivo principal do plugin.
- `includes/class-wc-guru-digital-api.php`: Classe responsável pela comunicação com a API da Guru Digital.
- `includes/class-wc-guru-digital-settings.php`: Classe responsável pelas configurações do plugin.
- `includes/class-wc-guru-payment-base.php`: Classe base para diferentes métodos de pagamento.
- `includes/payments/class-wc-guru-payment-billet.php`: Classe para o método de pagamento boleto bancário.
- `includes/payments/class-wc-guru-payment-credit-card.php`: Classe para o método de pagamento cartão de crédito.
- `includes/payments/class-wc-guru-payment-other.php`: Classe para outros métodos de pagamento.
- `includes/class-wc-guru-product-metabox.php`: Classe responsável pelo metabox de envio fictício de produtos.
- `assets/js/wc-guru-product-metabox.js`: Script JavaScript para o metabox de envio fictício de produtos.

### Hooks e Filtros

- `woocommerce_order_status_changed`: Hook usado para detectar mudanças no status dos pedidos e enviar os dados à Guru Digital.
- `add_meta_boxes`: Hook usado para adicionar o metabox aos produtos no admin do WooCommerce.
- `wp_ajax_wc_guru_send_test_order`: Hook usado para processar a solicitação AJAX de envio de pedido fictício.

### Logging

Se a opção "Habilitar Logging" estiver ativada, o plugin registrará logs das operações de envio de pedidos usando a classe `WC_Logger` do WooCommerce. Os logs podem ser visualizados no painel de administração do WooCommerce, em **WooCommerce** > **Status** > **Logs**.

---

## Suporte

Para obter suporte, visite o [site oficial](https://3xweb.site) ou entre em contato com o autor do plugin.

---

## Changelog

**1.0.6**
- Versão inicial do plugin.

---

**Licença:** GPL-2.0+

---

© 2024 3xweb.site. Todos os direitos reservados.
