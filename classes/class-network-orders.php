<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
  die;
}

class Woo_Network_Orders {

  public function __construct() {
    add_action( 'admin_menu', array( $this, 'plugin_setup_menu' ) );
    add_action( 'network_admin_menu', array( $this, 'plugin_setup_menu' ) );
  }

  public function plugin_setup_menu() {
    $hook = add_menu_page(
      esc_html__( 'Network Orders', WNO_DOMAIN ),
      esc_html__( 'Network Orders', WNO_DOMAIN ),
      'edit_theme_options',
      'network-orders',
      array( $this, 'plugin_setting_page' ),
      'dashicons-money-alt',
      55
    );
    add_action( "load-$hook", array( $this, 'add_options' ) );
  }

  public function add_options() {
    global $woo_orders_table;

    $orders_list = [];

    $sites = get_sites();
    foreach ( $sites as $site ) {
      switch_to_blog( $site->blog_id );

      $args   = array(
        'status' => [ 'pending', 'processing', 'on-hold', 'failed' ],
        'limit'  => - 1,
      );
      $orders = wc_get_orders( $args );
      foreach ( $orders as $order ) {
        $first_name    = $order->get_billing_first_name() ? $order->get_billing_first_name() : '';
        $last_name     = $order->get_billing_last_name() ? $order->get_billing_last_name() : '';
        $user          = $order->get_user();
        $customer_name = ( $first_name || $last_name ) ? $first_name . ' ' . $last_name : $user->display_name;
        $date_created  = $order->get_date_created();

        $orders_list[] = array(
          'id'           => $order->get_id(),
          'order_name'   => sprintf( '<a href="%spost.php?post=%s&action=edit">#%s %s</a>', admin_url(), $order->get_id(), $order->get_id(), $customer_name ),
          'order_site'  => sprintf( '<a href="%s">%s</a>', admin_url(), $site->domain ),
          'order_date'   => $date_created->format( 'd.m.Y' ),
          'order_status' => sprintf( '<span class="status-%s">%s</span>', $order->get_status(), $order->get_status() ),
          'order_total'  => wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) )
        );
      }

      restore_current_blog();
    }

    $woo_orders_table = new Woo_Orders_Table( $orders_list );
  }

  public function plugin_setting_page() {
    global $woo_orders_table;

    $woo_orders_table->prepare_items();
    ?>
    <div class="wrap">
      <h2>Orders</h2>
      <form method="post">
        <input type="hidden" name="page" value="network-orders-table">
        <?php
        $woo_orders_table->display();
        ?>
      </form>
    </div>
    <?php
  }
}
