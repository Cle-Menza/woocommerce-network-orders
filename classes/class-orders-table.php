<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
  die;
}

class Woo_Orders_Table extends WP_List_Table {
  public $found_data = array();
  public $orders;

  function __construct( $orders ) {
    global $status, $page;
    parent::__construct( array(
      'singular' => 'order',
      'plural'   => 'orders',
      'ajax'     => false //We won't support Ajax for this table
    ) );

    $this->orders = $orders;
    add_action( 'admin_head', array( $this, 'admin_header' ) );
  }

  function admin_header() {
    $page = ( isset( $_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
    if ( 'network-orders' != $page ) {
      return;
    }
    ?>
    <style type="text/css">
      .wp-list-table .column-id {
        width: 5%;
      }

      .wp-list-table .column-order_name {
        width: 35%;
      }

      .wp-list-table .column-vendor_site {
        width: 15%;
      }

      .wp-list-table .column-order_date {
        width: 15%;
      }

      .wp-list-table .column-order_status {
        width: 15%;
      }

      .wp-list-table .column-order_total {
        width: 15%;
      }

      .order_status span {
        display: inline-flex;
        line-height: 2em;
        color: #777;
        background: #e5e5e5;
        border-radius: 4px;
        border-bottom: 1px solid rgba(0,0,0,.05);
        white-space: nowrap;
        padding: 0 5px;
        margin: -.25em 0;
      }

      .wp-list-table .status-processing {
        background: #c6e1c6;
        color: #5b841b;
      }

      .wp-list-table .status-on-hold {
        background: #f8dda7;
        color: #94660c;
      }

      .wp-list-table .status-completed {
        background: #c8d7e1;
        color: #2e4453;
      }

      .wp-list-table .status-failed {
        background: #eba3a3;
        color: #761919;
      }
    </style>
    <?php
  }

  function no_items() {
    _e( 'No orders found.' );
  }

  function get_columns() {
    $columns = array(
      'id'           => 'ID',
      'order_name'   => 'Order',
      'order_site'  => 'Site',
      'order_date'   => 'Date',
      'order_status' => 'Status',
      'order_total'  => 'Total'
    );

    return $columns;
  }

  function column_default( $item, $column_name ) {
    switch ( $column_name ) {
      case 'id':
      case 'order_name':
      case 'order_site':
      case 'order_date':
      case 'order_status':
      case 'order_total':
        return $item[ $column_name ];
      default:
        return print_r( $item, true );
    }
  }

  function get_sortable_columns() {
    $sortable_columns = array(
      'id'           => array( 'id', true ),
      'order_date'   => array( 'order_date', true ),
      'order_status' => array( 'order_status', true ),
      'order_total'  => array( 'order_total', true ),
    );

    return $sortable_columns;
  }

  function usort_reorder( $a, $b ) {
    $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'id';
    $order   = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'desc';
    $result  = strcmp( $a[ $orderby ], $b[ $orderby ] );

    return ( $order === 'asc' ) ? $result : - $result;
  }

  function prepare_items() {
    $per_page     = 20;
    $current_page = $this->get_pagenum();

    $columns               = $this->get_columns();
    $hidden                = array();
    $sortable              = $this->get_sortable_columns();
    $this->_column_headers = array( $columns, $hidden, $sortable );
    usort( $this->orders, array( &$this, 'usort_reorder' ) );

    $total_items      = count( $this->orders );
    $this->found_data = array_slice( $this->orders, ( ( $current_page - 1 ) * $per_page ), $per_page );

    $this->set_pagination_args( array(
      'total_items' => $total_items,
      'per_page'    => $per_page
    ) );
    $this->items = $this->found_data;
  }
}
