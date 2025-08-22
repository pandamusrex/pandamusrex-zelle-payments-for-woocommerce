<?php

/**
 * Plugin Name: PandamusRex Zelle Payments for WooCommerce
 * Version: 1.6.0
 * Plugin URI: https://github.com/pandamusrex/pandamusrex-zelle-payments-for-woocommerce
 * Description: Accept WooCommerce offline payments through Zelle.
 * Author: PandamusRex
 * Author URI: https://www.github.com/pandamusrex/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.4
 * Requires PHP: 7.0
 * Tested up to: 6.8
 *
 * Text Domain: pandamusrex-zelle-for-woocommerce
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author PandamusRex
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once( plugin_dir_path(__FILE__) . 'includes/pandamusrex-zelle-gateway.php' );

class PandamusRex_Zelle_for_WooCommerce {
    private static $instance;

    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {}

    public function __wakeup() {}

    public function __construct() {
        add_filter( 'woocommerce_payment_gateways', [ $this, 'woocommerce_payment_gateways' ] );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );
    }

    public function woocommerce_payment_gateways( $methods ) {
        $methods[] = 'PandamusRex_Zelle_for_WooCommerce_Gateway';
        return $methods;
    }

    public function plugin_action_links( $actions ) {
        $links = array(
            '<a href="' .
                admin_url( 'admin.php?page=wc-settings&tab=checkout&section=pandamusrex_zelle' ) .
                '">' .
                esc_html__( 'Settings', 'pandamusrex-zelle-for-woocommerce' ) .
                '</a>',
        );
        $actions = array_merge( $actions, $links );
        return $actions;
    }
}

PandamusRex_Zelle_for_WooCommerce::get_instance();
