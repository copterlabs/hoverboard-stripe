<?php
/*
 * Fails if this class wasn't loaded by the plugin boot script 
 */
if (!defined('HB_PLUGIN_VERSION')) {
    header( 'HTTP/1.0 403 Forbidden' );
    die;
}

/**
 * A sample plugin class for building Hoverboard things that happen on the 
 * admin side of WordPress
 */
class HB_Stripe_Admin
{
    /**
     * Registers the init method
     * @return void
     */
    public function __construct(  ) {
        add_action('hoverboard/init', array($this, 'init'), 20);
    }

    /**
     * Performs plugin setup tasks, such as registering custom fields
     * @return void
     */
    public function init (  ) {
        add_action('acf/register_fields', array($this, 'register_options_panel'));
        add_action('acf/register_fields', array($this, 'register_fields'));
    }

    /**
     * Registers a custom options panel as a sub-page of Settings
     * @return void
     * @see    http://www.advancedcustomfields.com/resources/functions/acf_add_options_sub_page/
     */
    public function register_options_panel(  ) {
        if (function_exists('acf_add_options_sub_page')) {
            acf_add_options_sub_page(array(
                'title' => 'Stripe Settings',
                'parent' => 'options-general.php',
                'capability' => 'manage_options'
            ));
        }
    }

    /**
     * Registers custom fields for the plugin
     *
     * You'll probably have the best luck creating your fields through the ACF 
     * plugin, then exporting them to PHP and copy-pasting the output here.
     * 
     * @return void
     */
    public function register_fields(  ) {
        if (function_exists("register_field_group")) {
            register_field_group(array (
                'id' => 'acf_hoverboard-plugin-custom-fields',
                'title' => 'Stripe Settings',
                'fields' => array (
                    array (
                        'key' => 'field_hoverboard-stripe-pk',
                        'label' => 'Stripe Publishable Key',
                        'name' => 'stripe_publishable_key',
                        'type' => 'text',
                        'instructions' => 'Get your publishable key on <a href="https://dashboard.stripe.com/account/apikeys">your Stripe account dashboard</a>.',
                    ),
                ),
                'location' => array (
                    array (
                        array (
                            'param' => 'options_page',
                            'operator' => '==',
                            'value' => 'acf-options-custom-plugin-settings',
                            'order_no' => 0,
                            'group_no' => 0,
                        ),
                    ),
                ),
                'options' => array (
                    'position' => 'normal',
                    'layout' => 'no_box',
                    'hide_on_screen' => array (
                    ),
                ),
                'menu_order' => 0,
            ));
        }

    }
}
