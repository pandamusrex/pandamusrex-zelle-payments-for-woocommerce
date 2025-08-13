<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function pandamusrex_zelle_plugins_loaded() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }

    class PandamusRex_Zelle_for_WooCommerce_Gateway extends WC_Payment_Gateway {

        protected $qr_code_img_id;

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
            $this->qr_code_img_id = $this->get_option( 'qr_code_img_id' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
            add_filter( 'woocommerce_gateway_icon', [ $this, 'woocommerce_gateway_icon' ], 10, 2 );
            add_filter( 'woocommerce_gateway_description', [ $this, 'woocommerce_gateway_description' ], 10, 2 );
            add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, [ $this, 'sanitize_settings' ] );
            add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
        }

        public function admin_enqueue_scripts() {
            wp_register_script(
                'pandamusrex_zelle_payment_gateway_admin',
                plugins_url( '../scripts/admin.js', __FILE__ ),
                [ 'jquery-core' ],
                [],
                true
            );

            if ( is_admin() ) {
                wp_enqueue_media();
                wp_enqueue_script( 'pandamusrex_zelle_payment_gateway_admin' );
            }
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
                    'default' => '1. Scan the QR code with your mobile device banking app.\r\n' .
                        '2. Hit Place Order on this page to place your order.\r\n' .
                        '3. Enter the total amount due into your banking app and send it to us.\r\n' .
                        '4. We will complete your order as soon as Zelle sends us the funds.'
                ),
                'qr_code_img_id' => array(
                    'title' => __( 'QR Code', 'woocommerce' ),
                    'description' => __( 'Select the QR code the user will see during checkout.', 'woocommerce' ),
                    'type' => 'qr_code_img_id',
                    'default' => '',
                    'desc_tip' => true,
                )
            );
        }

        public function generate_qr_code_img_id_html( $key, $data ) {
            $field = $this->plugin_id . $this->id . '_' . $key;

            $defaults = array(
                'class'             => 'button-secondary',
                'css'               => '',
                'custom_attributes' => array(),
                'desc_tip'          => false,
                'description'       => '',
                'title'             => '',
            );
            $data = wp_parse_args( $data, $defaults );

            $image_url = '';
            if ( $this->qr_code_img_id ) {
                $image_url = wp_get_attachment_image_url( $this->qr_code_img_id, 'medium' );
            }

            ob_start();
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr( $field ); ?>">
                        <?php echo wp_kses_post( $data['title'] ); ?>
                    </label>
                    <?php echo $this->get_tooltip_html( $data ); ?>
                </th>
                <td class="forminp">
                    <fieldset>
                        <legend class="screen-reader-text">
                            <span><?php echo wp_kses_post( $data['title'] ); ?></span>
                        </legend>
                        <?php echo $this->get_description_html( $data ); ?>
                        <img
                            id="pandamusrex_zelle_qr_code_image"
                            src="<?php echo esc_url( $image_url ); ?>"
                            width="200"
                        />
                        <button
                            id="pandamusrex_zelle_qr_code_upload_button"
                            class="<?php echo esc_attr( $data['class'] ); ?>"
                            type="button"
                            style="<?php echo esc_attr( $data['css'] ); ?>"
                            <?php echo $this->get_custom_attribute_html( $data ); ?>
                        >
                            <?php echo esc_html( __( 'Choose Image', 'woocommerce' ) ); ?>
                        </button>
                        <input
                            id="pandamusrex_zelle_qr_code_img_id"
                            type="hidden"
                            name="<?php echo esc_attr( $field ); ?>"
                            value="<?php echo esc_attr( absint( $this->qr_code_img_id ) ); ?>"
                        />
                        <a href="#" id="pandamusrex_zelle_qr_code_remove">
                            <?php echo esc_html( __( 'Remove Image', 'woocommerce' ) ); ?>
                        </a>
                    </fieldset>
                </td>
            </tr>
            <?php
            return ob_get_clean();
        }

        public function sanitize_settings( $settings ) {
            if ( ! isset( $settings ) ) {
                return $settings;
            }

            if ( ! isset( $settings[ 'qr_code_img_id' ] ) ) {
                return $settings;
            }

            $qr_code_img_id = absint( trim( $settings[ 'qr_code_img_id' ] ) );
            if ( $qr_code_img_id === 0 ) {
                $qr_code_img_id = '';
            }

            $settings[ 'qr_code_img_id' ] = $qr_code_img_id;

            return $settings;
        }

        public function validate_qr_code_img_id_field( $key, $value ) {
            return $value;
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

        function woocommerce_gateway_icon( $icon, $gateway_id ) {
            if ( $gateway_id === $this->id ) {
                return '<img src="' . plugins_url( '../img/zelle.png', __FILE__ ) . '" > ';
            } else {
                return $icon;
            }
        }

        function woocommerce_gateway_description( $description, $gateway_id ) {
            $html_to_be_added = '';

            if ( $gateway_id === $this->id) {
                $image_url = '';
                if ( $this->qr_code_img_id ) {
                    $image_url = wp_get_attachment_image_url( $this->qr_code_img_id, 'medium' );
                }

                if ( empty( $image_url ) ) {
                    $html_to_be_added = '<p>Merchant: Please complete setting up Zelle.</p>';
                } else {
                    $html_to_be_added = '<img src="' . esc_url( $image_url ) . '" width="300" />';
                }
            }

            return $description . $html_to_be_added;
        }
    }
}

add_action( 'plugins_loaded', 'pandamusrex_zelle_plugins_loaded' );