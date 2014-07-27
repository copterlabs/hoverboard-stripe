<?php

/*
 * Fails if this class wasn't loaded by the plugin boot script 
 */
if (!defined('HB_STRIPE_VERSION')) {
    header( 'HTTP/1.0 403 Forbidden' );
    die;
}

/**
 * A sample plugin class for building Hoverboard things that happen on the 
 * front-end side of WordPress
 */
class HB_Stripe_Frontend
{
    /**
     * Constructor
     */
    public function __construct(  ) {
        add_action('after_setup_theme', array($this, 'init'), 20);
    }

    /**
     * Performs plugin setup tasks, such as registering shortcodes
     * @return void
     */
    public function init (  ) {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function enqueue_assets(  ) {
        /*
         * SCRIPTS
         *********************************************************************/
        wp_register_script(
            'stripe_js', 
            'https://js.stripe.com/v2/', 
            array(), 
            NULL, 
            TRUE
        );

        wp_register_script(
            'hoverboard_stripe_js',
            HB_STRIPE_URI . 'js/hoverboard_stripe.js',
            array('stripe_js'),
            filemtime(HB_STRIPE_PATH . 'js/hoverboard_stripe.js'),
            TRUE
        );

        wp_enqueue_script('hoverboard_stripe_js');

        /*
         * STYLES
         *********************************************************************/
        wp_register_style(
            'hoverboard_stripe_css',
            HB_STRIPE_URI . 'css/hoverboard_stripe.min.css',
            NULL,
            filemtime(HB_STRIPE_PATH . 'css/hoverboard_stripe.min.css')
        );

        wp_enqueue_style('hoverboard_stripe_css');
    }

    public function process_form_submission(  ) {
        $noncecheck = $this->validate_nonce(
            'hoverboard_stripe_nonce',
            'hoverboard_stripe_submit_payment'
        );

        print_r($_POST);

        // Only continues if the nonce is valid and order data was submitted
        if ($noncecheck===FALSE || !array_key_exists('order_data', $_POST)) {
            return array(
                'error' => TRUE,
                'message' => 'Invalid order data supplied.',
            );
        }

        // Loads the Stripe PHP lib
        self::get_stripe();

        // Retrieves order data for use with Stripe
        $order_data = $_POST['order_data'];
        $card = $order_data['stripe_token'];
        $email = $order_data['email-address'];
        $name_on_card = $order_data['purchaser-name'];

        // TODO Get the product cost dynamically
        $price = (int) (300.00*100);

        // TODO Make the product description dynamic
        $description = 'Product description';

        // Creates metadata to help search & support orders within Stripe
        $metadata = array(
            'Purchaser Name' => $name_on_card,
            'Email Address' => $email,
            // TODO Make the product name dynamic
            'Purchased Product' => 'Product Name',
        );

        return $this->create_charge($price, $card, $description, $metadata);
    }

    /**
     * Creates a Stripe charge and returns the resulting object
     * @param  string $amt  The amount (in cents) to charge the card
     * @param  string $card The Stripe token for the card to be charged
     * @param  string $desc An optional description of the purchase
     * @param  array  $meta An optional array of meta info about the purchase
     * @return mixed        Stripe response object on success or error message
     */
    protected function create_charge( $amt, $card, $desc='', $meta=array() ) {
        try {
            return Stripe_Charge::create(array(
                'amount' => $amt,
                'currency' => 'usd',
                'card' => $card,
                'metadata' => $meta,
                'description' => $desc
            ));
        } catch (Exception $e) {
            return $e->jsonBody['error']['message'];
        }
    }

    protected function validate_nonce( $nonce_id, $action ) {
        $noncecheck = FALSE;

        // Checks if a nonce exists to validate
        if (array_key_exists($nonce_id, $_POST)) {
            $nonce = $_POST[$nonce_id];
            $noncecheck = wp_verify_nonce($nonce, $action);
        }

        return $noncecheck;
    }

    /**
     * Displays the purchasing form
     * @return string The form markup
     */
    public function get_payment_form() {
        $variables = array(
            'publishable_key' => 'pk_test_4TKCZWxCSQMRyqXbJxDXnOGm',
            'price' => sprintf('$%01.2f', 300),
            'wp_nonce' => self::get_nonce_field(
                'hoverboard_stripe_submit_payment',
                'hoverboard_stripe_nonce'
            ),
        );
        return self::get_mustache()->render('payment-form', $variables);
    }

    protected function get_nonce_field( $action, $field_name, $echo=FALSE ) {
        ob_start();
        wp_nonce_field($action, $field_name);
        $nonce = ob_get_clean();

        if ($echo===TRUE) {
            echo $nonce;
            return;
        } else {
            return $nonce;
        }
    }

    /**
     * Loads Mustache for templating
     * @return void
     * @since  0.1.0
     */
    static function get_mustache( $views_path=NULL ) {
        require_once HB_STRIPE_PATH . 'lib/mustache/src/Mustache/Autoloader.php';
        Mustache_Autoloader::register();

        if (empty($views_path)) {
            $views_path = HB_STRIPE_PATH . 'views';
        }

        return new Mustache_Engine(array(
            'loader' => new Mustache_Loader_FilesystemLoader($views_path),
        ));
    }

    static function get_stripe(  ) {
        require_once HB_STRIPE_PATH . 'lib/stripe-php/lib/Stripe.php';
        // TODO make this dynamic
        Stripe::setApiKey("sk_test_4TKCVfKesFNFUjHKJ6lpIRa2");
    }
}
