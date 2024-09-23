<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

// Add the gateway to WooCommerce
add_filter('woocommerce_payment_gateways', 'add_fusion_pay_gateway');

function add_fusion_pay_gateway($methods) {
    $methods[] = 'WC_Fusion_Pay_Gateway';
    return $methods;
}

// Initialize the gateway class
add_action('plugins_loaded', 'init_fusion_pay_gateway');

function init_fusion_pay_gateway() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_Fusion_Pay_Gateway extends WC_Payment_Gateway {
        public function __construct() {
            $this->id                 = 'fusion_pay';
            $this->icon               = apply_filters('woocommerce_fusion_pay_icon', '');
            $this->has_fields         = false;
            $this->method_title       = __('Fusion Pay par Moneyfusion', 'woocommerce');
            $this->method_description = __('Accepter les paiements par Fusion Pay.', 'woocommerce');

            $this->init_form_fields();
            $this->init_settings();

            $this->title        = $this->get_option('title');
            $this->description  = $this->get_option('description');
            $this->api_url      = $this->get_option('api_url');
            $this->return_url   = $this->get_option('return_url');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __('Enable/Disable(activer/desactiver)', 'woocommerce'),
                    'type'    => 'checkbox',
                    'label'   => __('Activer le paiement par Fusion Pay', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title'       => __('Title(titre du mode de paiement)', 'woocommerce'),
                    'type'        => 'text',
                    'description' => __('Ce champ permet de modifier le titre du mode de paiement.', 'woocommerce'),
                    'default'     => __('Fusion Pay', 'woocommerce'),
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => __('Description(description du mode de paiement)', 'woocommerce'),
                    'type'        => 'textarea',
                    'description' => __('Ce champ permet de modifier la description du mode de paiement.', 'woocommerce'),
                    'default'     => __('Paiement sécurisé avec Fusion Pay.', 'woocommerce'),
                    'desc_tip'    => true,
                ),
                'api_url' => array(
                    'title'       => __('API URL(url de l\'api de Fusion Pay)', 'woocommerce'),
                    'type'        => 'text',
                    'description' => __('Entrer l\'url de l\'api de Fusion Pay.', 'woocommerce'),
                    'default'     => '',
                    'placeholder' => 'https://www.pay.moneyfusion.net/mon_entreprise/658eb103657e8aa57aa0fd94/pay/',
                    'desc_tip'    => true,
                ),
                'return_url' => array(
                    'title'       => __('Return URL(url de retour après paiement)', 'woocommerce'),
                    'type'        => 'text',
                    'description' => __('Entrer l\'url de retour après paiement.', 'woocommerce'),
                    'default'     => '',
                    'placeholder' => 'https://moneyfusion.net/',
                    'desc_tip'    => true,
                ),
            );
        }

        public function process_payment($order_id) {
            $order = wc_get_order($order_id);

            $body = array(
                'totalPrice' => $order->get_total(),
                'article'    => $this->get_order_items($order),
                'numeroSend' => $order->get_billing_phone(),
                'nomclient'  => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'return_url' => $this->return_url
            );

            $response = wp_remote_post($this->api_url, array(
                'method'    => 'POST',
                'headers'   => array('Content-Type' => 'application/json'),
                'body'      => json_encode($body),
                'timeout'   => 45,
            ));

            if (is_wp_error($response)) {
                wc_add_notice(__('Payment error:', 'woocommerce') . $response->get_error_message(), 'error');
                return;
            }

            $response_body = json_decode(wp_remote_retrieve_body($response), true);

            if (isset($response_body['statut']) && $response_body['statut']) {
                // Redirect to the payment URL
                return array(
                    'result'   => 'success',
                    'redirect' => $response_body['url'],
                );
            } else {
                wc_add_notice(__('Payment error:', 'woocommerce') . ($response_body['message'] ?? 'Unknown error'), 'error');
                return;
            }
        }

        private function get_order_items($order) {
            $items = array();
            foreach ($order->get_items() as $item) {
                $product = $item->get_product();
                $items[$product->get_name()] = $product->get_price();
            }
            return $items;
        }

        
    }
}