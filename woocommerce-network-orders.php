<?php
/*
Plugin Name: Woo Network Orders
Plugin URI:
description: Show orders from all sites on one page
Version: 0.1
Author: themkvz
Author URI:
License: MIT
Text-domain: woo-network-orders-textdomain
Domain Path: /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
  die;
}

/**
 * Define default constant
 */
if ( ! defined( 'WNO_URL' ) ) {
  define( 'WNO_URL', plugins_url( '/', __FILE__ ) );
}

if ( ! defined( 'WNO_PATH' ) ) {
  define( 'WNO_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WNO_PREFIX' ) ) {
  define( 'WNO_PREFIX', 'WNO' );
}

if ( ! defined( 'WNO_DOMAIN' ) ) {
  define( 'WNO_DOMAIN', 'woo-network-orders-textdomain' );
}

if ( ! defined( 'WNO_VERSION' ) ) {
  define( 'WNO_VERSION', '0.1' );
}

if ( ! function_exists( 'dependency_woo_network_orders' ) ) {
  /**
   * Dependency plugin
   * @return bool
   */
  function dependency_woo_network_orders() {
    if ( ! function_exists( 'is_plugin_active' ) ) {
      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) && current_user_can( 'activate_plugins' ) ) {
      deactivate_plugins( plugin_basename( __FILE__ ) );
      $error_message = '<p>' . esc_html__( 'This plugin requires ', WNO_DOMAIN ) . '<a href="' . esc_url( 'https://wordpress.org/plugins/woocommerce/' ) . '">WooCommerce</a>' . esc_html__( ' plugin to be active.', WNO_DOMAIN ) . '</p>';
      die( $error_message );
    }

    return true;
  }
}

if ( ! function_exists( 'activation_woo_network_orders' ) ) {
  /**
   * Activation plugin
   */
  function activation_woo_network_orders() {
    if ( dependency_woo_network_orders() ) {
      flush_rewrite_rules();
    }
  }
}

if ( ! function_exists( 'deactivation_woo_network_orders' ) ) {
  /**
   * Deactivation plugin
   */
  function deactivation_woo_network_orders() {
    flush_rewrite_rules();
  }
}

if ( ! function_exists( 'uninstall_woo_network_orders' ) ) {
  /**
   * Uninstall plugin
   */
  function uninstall_woo_network_orders() {
    flush_rewrite_rules();
  }
}

if ( ! function_exists( 'run_woo_network_orders' ) ) {
  /**
   * Create new object and run plugin
   */
  function run_woo_network_orders() {
    load_plugin_textdomain( WNO_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    if( ! class_exists( 'WP_List_Table' ) ) {
      require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    }

    foreach ( glob( WNO_PATH . 'classes/*.php' ) as $file ) {
      require_once( $file );
    }

    if ( class_exists( 'Woo_Network_Orders' ) ) {
      $callback = new Woo_Network_Orders();
    }
  }
}

register_activation_hook( __FILE__, 'activation_woo_network_orders' );
register_deactivation_hook( __FILE__, 'deactivation_woo_network_orders' );
register_uninstall_hook( __FILE__, 'uninstall_woo_network_orders' );
add_action( 'plugins_loaded', 'run_woo_network_orders', 20 );
