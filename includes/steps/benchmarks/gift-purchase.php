<?php

namespace GroundhoggWSG\Steps\Benchmarks;

use Groundhogg\Steps\Benchmarks\Benchmark;
use Groundhogg\Plugin;
use Groundhogg\Step;
use Groundhogg\Html;
use WCS_Gifting;
use WCSG_Product;

use function GroundhoggWSG\ghwsg_get_contact;
use function GroundhoggWSG\ghwsg_get_giftable_subscription_products_for_select;

if ( ! defined( 'ABSPATH' ) ) exit;

class Gift_Purchase extends Benchmark
{
    
    public function __construct()
    {
        parent::__construct();
           
    }
          
    protected function get_complete_hooks()
    {
        return [ 
            
            'woocommerce_checkout_subscription_created' => 3
        
        ];
    }

    protected function get_the_contact()
    {
        return ghwsg_get_contact($this->get_data( 'subscription' ));
    }
    
    /**
    * @param $subscription WC Subscription
    * @param $order WC Order 
    * @param $recurring_cart WC Cart
    */
    
    public function setup($subscription, $order, $recurring_cart)
    {
        $this->add_data( 'order_id', $order->get_order_number() );

        $subscription_products = array();
            
        foreach($order->get_items() as $item) {

            //Only add Giftable Subscription Products
            if( WCSG_Product::is_giftable($item->get_product_id()) ) {
                
                $subscription_products[] = $item->get_product_id();
            }
        }

        $this->add_data('subscription_products_order', $subscription_products);

        $this->add_data('subscription', $subscription);
        
    }

    protected function can_complete_step()
    {
        $order_id = $this->get_data( 'order_id', false );

        //Ignore none gifted checkouts
        if(!$order_id || !WCS_Gifting::order_contains_gifted_subscription( $order_id )) return false;

        $condition = $this->get_setting( 'condition', 'any' );

        $ids = $this->get_setting( 'subscription_products', [] );

        switch ($condition) {
            default:
            case 'any':
                $can_complete = true;
                break;

            case 'subscription_products':
                $has_ids = array_intersect( $this->get_data( 'subscription_products_order' ), $ids );
                $can_complete = !empty($has_ids);
                break;
        }

        return $can_complete;
    }
    
    
    public function get_name()
    {
        return _x( 'Subscription Gift Purchased', 'step_name', GROUNDHOGG_WSG_TEXT_DOMAIN );
    }

    public function get_type()
    {
        return 'subscription_gift_purchased';
    }

    public function get_description()
    {
        return _x( "Runs when Subscription Gifts are purchased.", 'step_description', GROUNDHOGG_WSG_TEXT_DOMAIN );
    }
    
    /**
     * @param $step Step
     */
    public function settings( $step )
    {
        $condition = $this->get_setting( 'condition', 'any' );
        
        $this->start_controls_section();
        
        $this->add_control( 'condition', [
            'label'         => __( 'Run when:', GROUNDHOGG_WSG_TEXT_DOMAIN ),
            'type'          => HTML::DROPDOWN,
            'default'       => 'any',
            'description'   => __( 'You can run this benchmark for any subscription gift creation, or specific gift subscription creations.', GROUNDHOGG_WSG_TEXT_DOMAIN ),
            'field'         => [
                'options'     => array(
                    'any'           => __( 'Any gifted subscription is created' ),
                    'subscription_products'     => __( 'Any of the following subscription(s) are gifted', GROUNDHOGG_WSG_TEXT_DOMAIN ),
                ),
            ],
        ] );
        
        if($condition != 'any') {
        
           $this->add_control( 'subscription_products', [
            'label'         => __( 'Run for these gifted subscription products:', GROUNDHOGG_WSG_TEXT_DOMAIN ),
            'type'          => HTML::SELECT2,
            'default'       => [ ],
            'description'   => __( 'These subscription products when gifted will trigger this benchmark.', GROUNDHOGG_WSG_TEXT_DOMAIN ),
            'multiple' => true,
            'field'         => [
                'multiple' => true,
                'data'  => ghwsg_get_giftable_subscription_products_for_select(),
                'placeholder'       => 'Please enter your Product(s)'
            ],
        ] );
            
        }
        
        $this->end_controls_section();

    }
    
     /**
     * Save the step settings
     *
     * @param $step Step
     */
    public function save( $step )
    {
        $this->save_setting( 'condition', sanitize_text_field( $this->get_posted_data( 'condition', 'any' ) ) );
        
        $this->save_setting( 'subscription_products', wp_parse_id_list( $this->get_posted_data( 'subscription_products', [] ) ) );
    }

    public function get_icon()
    {
        return GROUNDHOGG_WSG_ASSETS_URL . '/images/gift.png';
    }
    
}