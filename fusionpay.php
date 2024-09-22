<?php
/*
Plugin Name: Fusionpay
Plugin URI: https://moneyfusion.net
Description: A custom payment gateway plugin for Fusion Pay.
Version: 0.0.1
Author: Yaya Mohamed
Author URI: https://yayamohamed.com
License: GPL2
*/

add_action('admin_enqueue_scripts', function() {
    wp_enqueue_style('style', plugin_dir_url(__FILE__) . 'admin.css');
});
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('style', plugin_dir_url(__FILE__) . 'client.css');
});

// Register plugin settings
function fusion_pay_register_settings() {
    register_setting('fusion_pay_settings_group', 'fusion_pay_api_url');
    register_setting('fusion_pay_settings_group', 'fusion_pay_return_url');
    register_setting('fusion_pay_settings_group', 'fusion_pay_total_price');
    register_setting('fusion_pay_settings_group', 'fusion_pay_article', 'fusion_pay_sanitize_article');
    register_setting('fusion_pay_settings_group', 'fusion_pay_show_articles');
    register_setting('fusion_pay_settings_group', 'fusion_pay_show_total_price');
    register_setting('fusion_pay_settings_group', 'fusion_pay_language');
}
add_action('admin_init', 'fusion_pay_register_settings');

// Sanitize article data
function fusion_pay_sanitize_article($input) {
    if (!is_array($input)) {
        return [];
    }
    
    $sanitized_input = [];
    foreach ($input as $item) {
        if (isset($item['name']) && isset($item['price'])) {
            $sanitized_input[] = [
                'name' => sanitize_text_field($item['name']),
                'price' => floatval($item['price'])
            ];
        }
    }
    return json_encode($sanitized_input);
}

// Create the settings page
function fusion_pay_create_menu() {
    add_menu_page(
    'Fusion Pay Settings', 
    'Fusion Pay', 
    'administrator', 
    __FILE__, 
    'fusion_pay_settings_page', 
    plugin_dir_url( __FILE__ ) . 'assets/icon.png');
}
add_action('admin_menu', 'fusion_pay_create_menu');

// Settings page HTML
function fusion_pay_settings_page() {
    ?>
<div class="">
    <h1 class="setting-title">Fusion Pay Settings(Paramètres de Fusion Pay)</h1>
    <form method="post" action="options.php" class="form-container">
        <?php settings_fields('fusion_pay_settings_group'); ?>
        <?php do_settings_sections('fusion_pay_settings_group'); ?>

        <h2 class="form-title">API Settings (Paramètres de l'application)</h2>
        <div class="form-group">
            <label class="form-label" for="fusion_pay_api_url">API URL(URL de l'application):</label>
            <input class="form-input" type="text" name="fusion_pay_api_url"
                value="<?php echo esc_attr(get_option('fusion_pay_api_url')); ?>" />
        </div>
        <div class="form-group">
            <label class="form-label" for="fusion_pay_return_url">Return URL (Lien de retour après la
                transaction):</label>
            <input class="form-input" type="text" name="fusion_pay_return_url"
                value="<?php echo esc_attr(get_option('fusion_pay_return_url')); ?>" />
        </div>






        <h2>List of Articles (Articles)</h2>
        <?php fusion_pay_article_callback(); ?>
        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="fusion_pay_show_articles" value="1"
                    <?php checked(get_option('fusion_pay_show_articles'), 1); ?> />
                Show Articles on Payment Form(Afficher les articles sur le formulaire de paiement)
            </label>
        </div>

        <div class="form-group">
            <h2>Total Price (Prix total)</h2>
            <input class="form-input" type="number" name="fusion_pay_total_price"
                value="<?php echo esc_attr(get_option('fusion_pay_total_price')); ?>" />
        </div>
        <div class="form-group">
            <label class="form-label">
                <input type="checkbox" name="fusion_pay_show_total_price" value="1"
                    <?php checked(get_option('fusion_pay_show_total_price'), 1); ?> />
                Show Total Price on Payment Form(Afficher le prix total sur le formulaire de paiement)
            </label>
        </div>

        <div class="form-group">
            <label class="form-label" for="fusion_pay_language">Form language(Langue du formulaire):</label>
            <select name="fusion_pay_language" class="form-input">
                <option value="en" <?php selected(get_option('fusion_pay_language'), 'en'); ?>>English</option>
                <option value="fr" <?php selected(get_option('fusion_pay_language'), 'fr'); ?>>French</option>
            </select>
        </div>

        <?php wp_nonce_field('fusion_pay_article_nonce', 'fusion_pay_article_nonce'); ?>
        <?php submit_button(); ?>
    </form>
</div>
<?php
}

// Dynamic input fields for articles
function fusion_pay_article_callback() {
    $saved_articles = get_option('fusion_pay_article', '[]');
    $articles = json_decode($saved_articles, true) ?: [];
    ?>
<div id="article-fields">
    <?php foreach ($articles as $key => $item): ?>
    <div class="article-fields-item">
        <input class="form-input-article" type="text" name="fusion_pay_article[<?php echo $key; ?>][name]"
            value="<?php echo esc_attr($item['name']); ?>" placeholder="Article" />
        <input class="form-input-article" type="number" name="fusion_pay_article[<?php echo $key; ?>][price]"
            value="<?php echo esc_attr($item['price']); ?>" placeholder="Price(prix)" step="0.01" />
        <button type="button" class="remove-article" onclick="removeArticle(this)">✖</button>
    </div>
    <?php endforeach; ?>
</div>
<button type="button" class="add-article" onclick="addArticle()">+ Article</button>

<script>
function addArticle() {
    const articleFields = document.getElementById('article-fields');
    const newIndex = articleFields.children.length;
    const newField = document.createElement('div');
    newField.className = 'article-fields-item';
    newField.innerHTML = `
            <input class="form-input-article" type="text" name="fusion_pay_article[${newIndex}][name]" placeholder="Article" />
            <input class="form-input-article" type="number" name="fusion_pay_article[${newIndex}][price]" placeholder="Price(prix)" step="0.01" />
            <button class="remove-article" type="button" onclick="removeArticle(this)">✖</button>
        `;
    articleFields.appendChild(newField);
}

function removeArticle(button) {
    button.parentElement.remove();
}
</script>
<?php
}

// Save article data
add_action('admin_init', 'fusion_pay_save_article_data');

function fusion_pay_save_article_data() {
    if (isset($_POST['fusion_pay_article']) && check_admin_referer('fusion_pay_article_nonce', 'fusion_pay_article_nonce')) {
        $article_data = $_POST['fusion_pay_article'];
        update_option('fusion_pay_article', fusion_pay_sanitize_article($article_data));
    }
}

// Handle the payment processing
function fusion_pay_process_payment() {
    $nomClient = sanitize_text_field($_POST['nom_client']);
    $numeroSend = sanitize_text_field($_POST['numero_send']);
    
    // Get the admin-set article and total price
    $saved_articles = get_option('fusion_pay_article', '[]');
    $articles = json_decode($saved_articles, true) ?: [];

    // Transform the articles array to the desired format
    $transformedArticles = [];
    foreach ($articles as $article) {
        $transformedArticles[$article['name']] = $article['price'];
    }

    $totalPrice = get_option('fusion_pay_total_price');

    // Get dynamic settings from the admin panel
    $apiUrl = get_option('fusion_pay_api_url');
    $returnUrl = get_option('fusion_pay_return_url');

    // Prepare the data for the API request
    $body = array(
        'totalPrice' => $totalPrice,
        'article' => $transformedArticles,
        'numeroSend' => $numeroSend,
        'nomclient' => $nomClient,
        'return_url' => $returnUrl
    );

    // Send the request to the Fusion Pay API using wp_remote_post
    $response = wp_remote_post($apiUrl, array(
        'method'    => 'POST',
        'headers'   => array('Content-Type' => 'application/json'),
        'body'      => json_encode($body),
        'timeout'   => 45,
    ));

    // Handle the response
    if (is_wp_error($response)) {
        echo 'Payment failed. Please try again.';
    } else {
        $responseBody = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($responseBody['statut']) {
            $paymentUrl = esc_url($responseBody['url']);
            wp_redirect($paymentUrl);
        } else {
            echo 'Error: ' . esc_html($responseBody['message']);
        }
    }
}

// Shortcode to display the payment form
function fusion_pay_form_shortcode() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        fusion_pay_process_payment();
    }

    $language = get_option('fusion_pay_language', 'en');
    $show_articles = get_option('fusion_pay_show_articles', 0);
    $show_total_price = get_option('fusion_pay_show_total_price', 0);

    ob_start();
    ?>
<form method="POST" class="fusion-pay-form">
    <div class="fusion-pay-form-group">
        <label for="nom_client" class="fusion-pay-label">
            <?php echo $language === 'fr' ? 'Votre nom:' : 'Your name:'; ?>
        </label>
        <input type="text" id="nom_client" name="nom_client" required class="fusion-pay-input" />
    </div>

    <div class="fusion-pay-form-group">
        <label for="numero_send" class="fusion-pay-label">
            <?php echo $language === 'fr' ? 'Numéro de téléphone:' : 'Phone Number:'; ?>
        </label>
        <input type="text" id="numero_send" name="numero_send" required class="fusion-pay-input" />
    </div>

    <?php if ($show_articles): ?>
    <div class="fusion-pay-articles">
        <h3 class="fusion-pay-section-title"><?php echo $language === 'fr' ? 'Articles:' : 'Articles:'; ?></h3>
        <?php
                $saved_articles = get_option('fusion_pay_article', '[]');
                $articles = json_decode($saved_articles, true) ?: [];
                foreach ($articles as $article):
                ?>
        <div class="fusion-pay-article-item">
            <span class="fusion-pay-article-name"><?php echo esc_html($article['name']); ?></span>
            <span class="fusion-pay-article-price"><?php echo esc_html($article['price']); ?> F CFA</span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($show_total_price): ?>
    <div class="fusion-pay-total-price">
        <h3 class="fusion-pay-section-title"><?php echo $language === 'fr' ? 'Prix total:' : 'Total Price:'; ?></h3>
        <p class="fusion-pay-price"><?php echo esc_html(get_option('fusion_pay_total_price')); ?> F CFA</p>
    </div>
    <?php endif; ?>

    <button type="submit" class="fusion-pay-submit-button">
        <?php echo $language === 'fr' ? 'Payer maintenant' : 'Pay Now'; ?>
    </button>
</form>
<?php
    return ob_get_clean();
}
add_shortcode('fusion_pay_form', 'fusion_pay_form_shortcode');