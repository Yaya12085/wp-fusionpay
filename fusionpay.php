<?php
/*
Plugin Name: FusionPay
Plugin URI: https://moneyfusion.net
Description: A custom WooCommerce payment gateway plugin for Fusion Pay.
Version: 0.0.3
Author: Yaya Mohamed
Author URI: https://yayamohamed.com
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

// Add the gateway to WooCommerce
add_filter('woocommerce_payment_gateways', 'add_fusion_pay_gateway');

add_filter('woocommerce_billing_fields', function($fields) {
    if (isset($fields['billing_phone'])) {
        $fields['billing_phone']['required'] = true;
    }
    return $fields;
});

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
        // Explicit property declarations
        private bool $debug = false;
        private string $api_url = '';
        private string $return_url = '';
        private string $webhook_url = '';

        public function __construct() {
            $this->id                 = 'fusion_pay';
            $this->icon               = apply_filters('woocommerce_fusion_pay_icon', plugins_url('assets/icon.png', __FILE__));
            $this->has_fields         = false;
            $this->method_title       = __('Fusion Pay par Moneyfusion', 'woocommerce');
            $this->method_description = __('Accepter les paiements par Fusion Pay (Moneyfusion).', 'woocommerce');
            $this->supports           = array('products');

            $this->init_form_fields();
            $this->init_settings();

            $this->title        = $this->get_option('title');
            $this->description  = $this->get_option('description');
            $this->api_url      = $this->get_option('api_url');
            $this->return_url   = $this->get_option('return_url');
            $this->webhook_url  = $this->get_option('webhook_url');
            $this->debug        = 'yes' === $this->get_option('debug');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_api_wc_fusion_pay_gateway', array($this, 'handle_webhook'));
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __('Enable/Disable', 'woocommerce'),
                    'type'    => 'checkbox',
                    'label'   => __('Activate Fusion Pay', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title'       => __('Title', 'woocommerce'),
                    'type'        => 'text',
                    'description' => __('Ce champ permet de modifier le titre du mode de paiement.', 'woocommerce'),
                    'default'     => __('Fusion Pay', 'woocommerce'),
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => __('Description', 'woocommerce'),
                    'type'        => 'textarea',
                    'description' => __('Ce champ permet de modifier la description du mode de paiement.', 'woocommerce'),
                    'default'     => __('Paiement sécurisé avec Fusion Pay.', 'woocommerce'),
                    'desc_tip'    => true,
                ),
                'api_url' => array(
                    'title'       => __('API URL', 'woocommerce'),
                    'type'        => 'text',
                    'description' => __('Entrer l\'URL unique de paiement Fusion Pay.', 'woocommerce'),
                    'default'     => '',
                    'placeholder' => 'https://www.pay.moneyfusion.net/mon_entreprise/658eb103657e8aa57aa0fd94/pay/',
                    'desc_tip'    => true,
                ),
                'return_url' => array(
                    'title'       => __('Return URL', 'woocommerce'),
                    'type'        => 'text',
                    'description' => __('URL où le client sera redirigé après le paiement (page de remerciement).', 'woocommerce'),
                    'default'     => home_url('/'),
                    'placeholder' => home_url('/thanks'),
                    'desc_tip'    => true,
                ),
                'webhook_url' => array(
                    'title'       => __('Webhook URL', 'woocommerce'),
                    'type'        => 'text',
                    'description' => __('URL que vous devez configurer pour recevoir les notifications de paiement.', 'woocommerce'),
                    'default'     => home_url('/wc-api/wc_fusion_pay_gateway'),
                    'placeholder' => home_url('/wc-api/wc_fusion_pay_gateway'),
                    'desc_tip'    => true,
                ),
                'debug' => array(
                    'title'       => __('Debug Log', 'woocommerce'),
                    'type'        => 'checkbox',
                    'label'       => __('Enable logging', 'woocommerce'),
                    'default'     => 'no',
                    'description' => __('Log Fusion Pay events, like webhooks.', 'woocommerce'),
                )
            );
        }

        public function process_payment($order_id) {
            $order = wc_get_order($order_id);

            $phone = $order->get_billing_phone();

            // ✅ Validate phone number
            if (empty($phone)) {
                wc_add_notice(__('Le numéro de téléphone est requis pour Fusion Pay.', 'woocommerce'), 'error');
                return;
            }

            if ($this->debug) {
                $this->log('Processing payment for order #' . $order_id);
            }
            

            $body = array(
                'totalPrice' => $order->get_total(),
                'article'    => $this->get_order_items($order),
                'numeroSend' => $order->get_billing_phone(),
                'nomclient'  => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'return_url' => $this->return_url,
                'webhook_url' => $this->webhook_url ? $this->webhook_url : home_url('/wc-api/wc_fusion_pay_gateway'),
                'personal_Info' => [
                    [
                        'userId' => $order->get_user_id() ? $order->get_user_id() : 'guest',
                        'orderId' => $order->get_id(),
                    ]
                ]
            );

            $headers = array(
                'Content-Type' => 'application/json',
            );

            $response = wp_remote_post($this->api_url, array(
                'method'    => 'POST',
                'headers'   => $headers,
                'body'      => json_encode($body),
                'timeout'   => 45,
            ));

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                if ($this->debug) {
                    $this->log('Erreur de paiement: ' . $error_message);
                }
                wc_add_notice(__('Erreur de paiement:', 'woocommerce') . $error_message, 'error');
                return;
            }

            $response_body = json_decode(wp_remote_retrieve_body($response), true);

            if (isset($response_body['statut']) && $response_body['statut']) {
                $order->update_status('pending-payment', __('En attente de paiement Fusion Pay', 'woocommerce'));

                if ($this->debug) {
                    $this->log('Paiement initié pour la commande #' . $order_id . '. Redirection vers: ' . $response_body['url']);
                }

                return array(
                    'result'   => 'success',
                    'redirect' => $response_body['url'],
                );
            } else {
                $error_message = $response_body['message'] ?? __('Erreur inconnue', 'woocommerce');
                if ($this->debug) {
                    $this->log('Erreur API de paiement: ' . $error_message);
                }
                wc_add_notice(__('Erreur de paiement:', 'woocommerce') . $error_message, 'error');
                return;
            }
        }

        public function handle_webhook() {
            $payload = file_get_contents('php://input');
            $data = json_decode($payload, true);

            if ($this->debug) {
                $this->log('Webhook received: ' . print_r($data, true));
            }

            if (isset($data['personal_Info'][0]['orderId']) && isset($data['statut'])) {
                $order_id = $data['personal_Info'][0]['orderId'];
                $status = $data['statut'];

                $order = wc_get_order($order_id);

                if (!$order) {
                    if ($this->debug) {
                        $this->log('Order #' . $order_id . ' not found');
                    }
                    wp_die('Commande non trouvée', 'Commande non trouvée', array('response' => 404));
                }

                switch ($status) {
                    case 'paid':
                        $order->update_status('completed', __('Paiement effectué via Fusion Pay.', 'woocommerce'));
                        $order->add_order_note(__('Paiement effectué via Fusion Pay.', 'woocommerce'));
                        break;
                    case 'pending':
                        $order->update_status('pending-payment', __('Paiement en cours via Fusion Pay.', 'woocommerce'));
                        break;
                    case 'failure':
                        $order->update_status('failed', __('Paiement échoué via Fusion Pay.', 'woocommerce'));
                        break;
                    case 'no paid':
                        $order->update_status('on-hold', __('Paiement non effectué via Fusion Pay.', 'woocommerce'));
                        break;
                    default:
                        if ($this->debug) {
                            $this->log('Statut de paiement inconnu: ' . $status);
                        }
                        break;
                }

                if ($this->debug) {
                    $this->log('Commande #' . $order_id . ' mise à jour avec le statut: ' . $status);
                }

                wp_die('Webhook traité avec succès', 'Succès', array('response' => 200));
            }

            if ($this->debug) {
                $this->log('Données webhook invalides: manquant orderId ou statut');
            }

            wp_die('Données webhook invalides', 'Données invalides', array('response' => 400));
        }

        private function get_order_items($order) {
            $items = array();
            foreach ($order->get_items() as $item) {
                $product = $item->get_product();
                if ($product) {
                    $items[$product->get_name()] = $product->get_price();
                }
            }
            return $items;
        }

        private function log($message) {
            if ($this->debug) {
                $logger = wc_get_logger();
                $logger->debug($message, array('source' => 'fusion-pay'));
            }
        }
    }
}