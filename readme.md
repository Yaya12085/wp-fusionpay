# Fusion Pay Payment Gateway

![banner](https://raw.githubusercontent.com/Yaya12085/wp-fusionpay/refs/heads/main/assets/banner.png)

## Description

Fusion Pay WooCommerce Payment Gateway is a custom WordPress plugin that allows you to integrate the MoneyFusion payment system into your WooCommerce WordPress website.

## Features

- Easy installation and setup
- Secure payment processing through MoneyFusion
- Customizable payment gateway settings
- Real-time payment status updates via webhooks
- Detailed transaction logging and debug options
- Custom order statuses for better payment tracking
- Complete WooCommerce integration for seamless e-commerce payments

## Installation

1. Download the plugin from the [this link](https://github.com/Yaya12085/wp-fusionpay/archive/refs/heads/main.zip).
2. Install it or upload the `fusionpay` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.

## WooCommerce Integration

To use Fusion Pay with WooCommerce:

1. Ensure WooCommerce is installed and activated.
2. Go to WooCommerce > Settings > Payments.
3. Enable "Fusion Pay" as a payment method.
   ![woocommerce-fusionpay-activation](https://raw.githubusercontent.com/Yaya12085/wp-fusionpay/refs/heads/main/assets/woo-activation.png)

4. Click "Manage" next to Fusion Pay to configure the gateway settings:
   - **Enable/Disable**: Activate or deactivate the payment method
   - **Title**: Customize how the payment method appears to customers (default: "Fusion Pay")
   - **Description**: Provide information about this payment method to your customers
   - **API URL**: Enter your unique Fusion Pay API URL (create an app on [moneyfusion](https://moneyfusion.net/dashboard/fusionpay) to get the url)
   - **Return URL**: URL where customers will be redirected after payment
   - **Webhook URL**: URL you need to configure to receive payment notifications.
   - **Debug Log**: Enable for troubleshooting payment issues. Found on `https://your-site.com/wp-admin/admin.php?page=wc-status&tab=logs` and source `fusion-pay`

   ![form](https://raw.githubusercontent.com/Yaya12085/wp-fusionpay/refs/heads/main/assets/screenshot-3.png)

5. Save your settings and the payment gateway will be available at checkout.

Customers can now select Fusion Pay as a payment option during checkout.

![form](https://raw.githubusercontent.com/Yaya12085/wp-fusionpay/refs/heads/main/assets/screenshot-4.png)

## Payment Status Handling

The plugin automatically handles the following payment statuses from MoneyFusion:

- **paid** - Payment successful (order marked as completed)
- **pending** - Payment is being processed (order marked as processing)
- **failure** - Payment failed (order marked as failed)
- **no paid** - Payment not completed (order marked as on-hold)

## Webhook Integration

For automatic order status updates, leave the webhook endpoint in the woocommerce dashboard:

- Webhook URL: `https://your-site.com/wc-api/wc_fusion_pay_gateway`

This ensures your WooCommerce store automatically receives payment status updates.

## Troubleshooting

If you encounter issues with the gateway:

1. Enable Debug Log in the gateway settings
2. Check the WooCommerce system status logs for any errors
3. Verify your API URL is correctly entered
4. If the payment option is not visible, go to the payment page and enter `[woocommerce_checkout]` in the shortcut section for more information:
   [![Watch the video](https://img.youtube.com/vi/sfYauEEO7S0/0.jpg)](https://www.youtube.com/watch?v=sfYauEEO7S0)

## Support

Pour tout problème ou demande de fonctionnalité, veuillez consulter la [GitHub Issues Page](https://github.com/Yaya12085/wp-fusionpay/issues)

## Author

Yaya Mohamed

## Changelog

### 0.0.2

- Added comprehensive WooCommerce integration
- Implemented webhook support for automatic order status updates
- Added detailed debug logging for troubleshooting

### 0.0.1

- Initial release with basic WordPress integration

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request
