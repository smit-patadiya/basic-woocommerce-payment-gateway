<?php
/**
 * Plugin Name: YOUR-PLUGIN-NAME
 * Plugin URI: https://smitpatadiya.me
 * Description: N/A
 * Author URI: https://www.linkedin.com/in/smit-patadiya/
 * Version: 1.0.0
 * Text Domain: my-text-domain
 * Domain Path: /i18n/languages/
 *
 * License: GNU GENERAL PUBLIC LICENSE v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   my-text-domain
 * @author    SmitPatadiya
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 * This offline gateway forks the WooCommerce core "Cheque" payment gateway to create another offline payment method.
 */
 
defined( 'ABSPATH' ) or exit;

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

/**
 * Add the gateway to WC Available Gateways
 * 
 * @since 1.0.0
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + my gateway
 */
if( ! function_exists('add_my_payment_gateway') ){

    function add_my_payment_gateway( $gateways ) {

        $gateways[] = 'WC_MY_BASIC_Gateway';
    
        return $gateways;
    
    }
    add_filter( 'woocommerce_payment_gateways', 'add_my_payment_gateway' );

}


add_action( 'plugins_loaded', 'wc_my_baic_gateway_init', 10 );

if( ! function_exists('wc_my_baic_gateway_init') ){

    function wc_my_baic_gateway_init () {

        if ( class_exists( 'WC_MY_BASIC_Gateway' ) ){
            class WC_Gateway_USDCINR extends WC_Payment_Gateway {

                /**
                 * Constructor for the gateway.
                 */
                public function __construct() {
                    
                    $this->id = 'my_basic_gateway';
                    $this->method_title = __( 'MY BASIC GATEWAY', 'woocommerce' );
                    $this->method_description = __( 'Pay with MY BASIC GATEWAY', 'woocommerce' );
                    $this->has_fields = false;
                    $this->order_button_text = __( 'Proceed to MY BASIC GATEWAY', 'woocommerce' );

                    // Actions
                    /**
                     * Thankyou Page
                     */
                    add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

                    /**
                     * setup custom email
                     */
                    add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

                }

                /**
                 * Message on thankyou page 
                 */
                public function thankyou_page () {
                    /**
                     * Function Reference:- wpautop()
                     * https://codex.wordpress.org/Function_Reference/wpautop
                     * 
                     */
                    $thankyou_text = __('Paid Using MY BASIC GATEWAY');
                    echo wpautop( wptexturize( $thankyou_text ) );

                }

                /**
                 * Process the payment and return the result
                 *
                 * @param int $order_id
                 * @return array
                 */
                public function process_payment( $order_id ) {
            
                    $order = wc_get_order( $order_id );
                    
                    // Mark as on-hold (we're awaiting the payment)
                    $order->update_status( 'on-hold', __( 'Awaiting offline payment', 'my-text-domain' ) );
                    
                    /**
                     * Do you API or CUSTOM ACTIONS HERE
                     */
                    // *************  API or CUSTOM ACTIONS HERE  *************
                    // Eg. Reduce user wallet balance / rewards

                    //Change order status
                    $order->update_status( 'processing', __( 'Payment processing using MY BASIC GATEWAY', 'woocommerce' ) );

                    // Reduce stock levels
                    $order->reduce_order_stock();
                    
                    // Remove cart
                    WC()->cart->empty_cart();
                    
                    // Return thankyou redirect
                    return array(
                        'result' 	=> 'success',
                        'redirect'	=> $this->get_return_url( $order )
                    );
                }

                
                /**
                 * Add content to the WC emails.
                 *
                 * @param WC_Order $order
                 * @param bool $sent_to_admin
                 * @param bool $plain_text
                 */
                public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
                
                    if ( $this->id === $order->payment_method && $order->has_status( 'processing' ) ) {
                        echo wpautop( wptexturize( __( 'PAID USING MY BASIC GATEWAY', 'woocommerce' ) ) ) . PHP_EOL;
                    }
                }

            }
        }

    }

}



