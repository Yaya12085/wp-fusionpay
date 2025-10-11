<?php
/**
 * Version simplifiée avec JS inline
 * Fichier : includes/class-wc-fusion-pay-blocks-support.php
 */

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Fusion_Pay_Blocks_Support extends AbstractPaymentMethodType
{
    private $gateway;
    protected $name = "fusion_pay";

    public function initialize()
    {
        $this->settings = get_option("woocommerce_fusion_pay_settings", []);
        $gateways = WC()->payment_gateways->payment_gateways();
        $this->gateway = isset($gateways[$this->name])
            ? $gateways[$this->name]
            : null;
    }

    public function is_active()
    {
        return $this->gateway && $this->gateway->is_available();
    }

    public function get_payment_method_script_handles()
    {
        $script_handle = "wc-fusion-pay-blocks-inline";

        // Enregistrer un script inline
        wp_register_script(
            $script_handle,
            "",
            [
                "wc-blocks-registry",
                "wc-settings",
                "wp-element",
                "wp-html-entities",
            ],
            null,
            true,
        );

        // Ajouter le code JavaScript inline
        $script = "
        (function() {
            const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
            const { getSetting } = window.wc.wcSettings;
            const { decodeEntities } = window.wp.htmlEntities;
            const { createElement } = window.wp.element;

            const settings = getSetting('fusion_pay_data', {});
            const label = decodeEntities(settings.title) || 'Fusion Pay';
            const description = decodeEntities(settings.description) || '';

            const Content = () => {
                return createElement('div', {
                    dangerouslySetInnerHTML: { __html: description }
                });
            };

            registerPaymentMethod({
                name: 'fusion_pay',
                label: label,
                content: createElement(Content),
                edit: createElement(Content),
                canMakePayment: () => true,
                ariaLabel: label,
                supports: {
                    features: settings.supports || []
                }
            });
        })();
        ";

        wp_add_inline_script($script_handle, $script);

        return [$script_handle];
    }

    public function get_payment_method_data()
    {
        return [
            "title" => $this->get_setting("title"),
            "description" => $this->get_setting("description"),
            "supports" => $this->gateway ? $this->gateway->supports : [],
        ];
    }
}
