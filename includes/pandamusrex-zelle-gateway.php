<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function pandamusrex_zelle_plugins_loaded() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }

    class PandamusRex_Zelle_for_WooCommerce_Gateway extends WC_Payment_Gateway {
        public function __construct() {
            $this->id = 'pandamusrex_zelle';
            $this->icon = plugins_url( '../img/zelle.png', __FILE__ );
            $this->method_title = __( 'Zelle' );
            $this->method_description = __( 'Accept offline payments with Zelle' );

            $this->supports = array(
                'products'
            );

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_filter( 'woocommerce_gateway_icon', array( $this, 'custom_gateway_icon' ), 10, 2 );
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

            // Reduce stock levels
            $order->reduce_order_stock();

            // Remove cart
            $woocommerce->cart->empty_cart();

            // Return thank you redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url( $order )
            );
        }

        function custom_gateway_icon( $icon, $gateway_id ) {
            if ( $gateway_id === $this->id ) {
                return '<img src="' . plugins_url( '../img/zelle.png', __FILE__ ) . '" > ';
            } else {
                return $icon;
            }
        }
    }
}

add_action( 'plugins_loaded', 'pandamusrex_zelle_plugins_loaded' );