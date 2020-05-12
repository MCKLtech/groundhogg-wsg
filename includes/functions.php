<?php

namespace GroundhoggWSG;
use Groundhogg\Contact;
use Groundhogg\Plugin as GHPlugin;
use Groundhogg\Preferences;
use function Groundhogg\get_contactdata;
use WCS_Gifting;
use WCSG_Product;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Return / Create contact based on WC Subscription
 * @param $subscription WC_Subscription
 */

function ghwsg_get_contact($subscription) {

    $get_recipient_user = WCS_Gifting::get_recipient_user($subscription);

    if(!$get_recipient_user) return false;

    $contact = get_contactdata( $get_recipient_user, true );

    if ( $contact && $contact->exists() ){
        return $contact;
    }

    //User should always exist
    return false;

}

function ghwsg_get_giftable_subscription_products_for_select() {

    $options = [];

    $args = array('type' => array('subscription', 'variable-subscription'));

    $subscription_products = wc_get_products( $args );

    foreach ( $subscription_products as $product ) {

        if(WCSG_Product::is_giftable($product->get_id())) $options[ $product->get_id() ] = $product->get_name();
    }

    return $options;
}

