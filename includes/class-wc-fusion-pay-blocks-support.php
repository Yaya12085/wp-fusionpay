<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Fusion_Pay_Blocks_Support extends AbstractPaymentMethodType
{
    private $gateway;
    protected $name = "fusion_pay";

    public function initialize()
    {
        $this->settings = get_option("woocommerce_fusion_pay_settings", []);
        $gateways = WC()->payment_gateways->payment_gateways();
        $this->gateway = $gateways[$this->name];
    }

    public function is_active()
    {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles()
    {
        $script_path = "/assets/js/fusion-pay-blocks.js";
        $script_asset_path =
            plugin_dir_path(__FILE__) .
            "../assets/js/fusion-pay-blocks.asset.php";
        $script_asset = file_exists($script_asset_path)
            ? require $script_asset_path
            : [
                "dependencies" => [],
                "version" => "1.0.0",
            ];
        $script_url = plugins_url($script_path, dirname(__FILE__));

        wp_register_script(
            "wc-fusion-pay-blocks",
            $script_url,
            $script_asset["dependencies"],
            $script_asset["version"],
            true,
        );

        if (function_exists("wp_set_script_translations")) {
            wp_set_script_translations(
                "wc-fusion-pay-blocks",
                "woocommerce",
                plugin_dir_path(__FILE__) . "../languages/",
            );
        }

        return ["wc-fusion-pay-blocks"];
    }

    public function get_payment_method_data()
    {
        return [
            "title" => $this->get_setting("title"),
            "description" => $this->get_setting("description"),
            "supports" => array_filter($this->gateway->supports, [
                $this->gateway,
                "supports",
            ]),
        ];
    }
}
