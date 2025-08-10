<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PandamusRex_Zelle_for_WooCommerce_Gateway extends WC_Payment_Gateway {
    public function __construct() {
        $this->id = 'pandamusrex_zelle';
        $this->method_title = __( 'Zelle' );
        $this->method_description = __( 'Accept offline payments with Zelle' );

        $this->init_form_fields();
        $this->init_settings();

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    public function process_admin_options() {
        // GNDN
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Enable/Disable', 'woocommerce' ),
                'type' => 'checkbox',
                'label' => __( 'Enable Zelle Payments', 'woocommerce' ),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __( 'Title', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                'default' => __( 'Zelle', 'woocommerce' ),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __( 'Customer Message', 'woocommerce' ),
                'type' => 'textarea',
                'default' => ''
            )
        );
    }

    public function process_payment( $order_id ) {
        global $woocommerce;
        $order = new WC_Order( $order_id );

        // Mark as on-hold (we're awaiting the Zelle)
        $order->update_status('on-hold', __( 'Awaiting Zelle payment', 'woocommerce' ));

        // Remove cart
        $woocommerce->cart->empty_cart();

        // Return thank you redirect
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url( $order )
        );
    }
}