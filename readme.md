# Fusion Pay Payment Gateway

## Description

Fusion Pay Payment Gateway is a custom WordPress plugin that allows you to integrate the Fusion Pay payment system into your WordPress website. This plugin provides a user-friendly interface for managing payment settings and allows users to make payments seamlessly.

## Features

- Easy installation and setup
- Customizable API settings
- Ability to define articles and total price
- Multilingual support (English and French)
- Shortcode for embedding payment form
- Dynamic handling of articles in the payment form

## Installation

1. Download the plugin from the [Fusion Pay Plugin Repository](https://wordpress.org/plugins/fusionpay).
2. Upload the `fusionpay` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Navigate to the "Fusion Pay" settings page in the admin dashboard to configure your API and payment settings.

## Configuration

After activating the plugin, follow these steps to configure it:

1. Go to the **Fusion Pay** menu in the WordPress admin panel.
2. Fill in the **API URL** and **Return URL**.
3. Add articles with their names and prices.
4. Set the **Total Price** and choose whether to display articles and total price on the payment form.
5. Select the form language (English or French).
6. Save your settings.

#### preview

![panel](https://raw.githubusercontent.com/Yaya12085/wp-fusionpay/refs/heads/main/assets/screenshot-1.png)

## Shortcode Usage

To display the payment form on a page or post, use the following shortcode:

```plaintext
[fusion_pay_form]
```

This will render the payment form with fields for the customer's name and phone number, along with the articles and total price if configured to display.

#### preview

![form](https://raw.githubusercontent.com/Yaya12085/wp-fusionpay/refs/heads/main/assets/screenshot-2.png)

## License

This plugin is licensed under the GPL2. You can modify and distribute it as per the terms of the license.

## Support

For any issues or feature requests, please visit the [GitHub Issues Page](https://github.com/Yaya12085/wp-fusionpay/issues)

## Author

Yaya Mohamed

Feel free to customize any sections according to your preferences or specific requirements!

## Donations

If you find this plugin useful and would like to support its development, consider making a donation:

[Make a Donation](https://www.pay.moneyfusion.net/Faire_un_don_1726979068528/)
