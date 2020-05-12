<?php

namespace GroundhoggWSG;

use Groundhogg\Extension;
use GroundhoggWSG\Steps\Benchmarks\Gift_Purchase;

class Plugin extends Extension{

    /**
     * Override the parent instance.
     *
     * @var Plugin
     */
    public static $instance;
    
    /**
     * Extension constructor.
     */
    public function __construct()
    {
        if ( $this->dependent_plugins_are_installed() ){

            $this->register_autoloader();

            if ( ! did_action( 'groundhogg/init/v2' ) ){
                add_action( 'groundhogg/init/v2', [ $this, 'init' ] );
            } else {
                $this->init();
            }  
        }
    }

    /**
     * Include any files.
     *
     * @return void
     */
    public function includes()
    {
        require GROUNDHOGG_WSG_PATH . '/includes/functions.php';     
    }

    /**
     * Init any components that need to be added.
     *
     * @return void
     */
    public function init_components()
    {
        //Silence
    }
      
    /**
     * Register additional replacement codes.
     *
     * @param \Groundhogg\Replacements $replacements
     */
    public function add_replacements( $replacements )
    {
        $wc_replacements = new Replacements();

        foreach ($wc_replacements->get_replacements() as $replacement ){
         
            $replacements->add( $replacement[ 'code' ], $replacement[ 'callback' ], $replacement[ 'description' ] );
        }
    }

    /**
     * @param \Groundhogg\Steps\Manager $manager
     */
    public function register_funnel_steps($manager)
    {
        //Benchmarks
        $manager->add_step( new Gift_Purchase() );

    }

    /**
     * Get the ID number for the download in EDD Store
     *
     * @return int
     */
    public function get_download_id()
    {
        // TODO: Implement get_download_id() method.
    }

    /**
     * Get the version #
     *
     * @return mixed
     */
    public function get_version()
    {
        return GROUNDHOGG_WSG_VERSION;
    }

    /**
     * @return string
     */
    public function get_plugin_file()
    {
        return GROUNDHOGG_WSG__FILE__;
    }

    /* WooCommerce Subscription Gifting is required */

    public function get_dependent_plugins() {

        return ['woocommerce-subscriptions-gifting/woocommerce-subscriptions-gifting.php' => 'WooCommerce Subscription Gifting'];
    }
    
    /**
     * Register autoloader.
     *
     * Groundhogg autoloader loads all the classes needed to run the plugin.
     *
     * @since 1.6.0
     * @access private
     */
    protected function register_autoloader()
    {
        require GROUNDHOGG_WSG_PATH . 'includes/autoloader.php';
        Autoloader::run();
    }
}

Plugin::instance();