<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Shows a custom table
 *
 * @class   YWRR_Custom_Table
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 * @extends WP_List_Table
 *
 */
class YWRR_Custom_Table extends WP_List_Table {

    /**
     * @var array $options array of options for table showing
     */
    var $options;

    /**
     * Constructor
     *
     * @param   $args array|string array or string of arguments
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @see     WP_List_Table
     */
    function __construct( $args ) {
        global $status, $page;

        parent::__construct( $args );
    }

    /**
     * Default column renderer
     *
     * @param   $item array the row
     * @param   $column_name string the column name
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  string
     */
    function column_default( $item, $column_name ) {
        return $item[ $column_name ];
    }

    /**
     * Checkbox column renderer
     *
     * @param   $item array the row
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * Return array of bulk options
     *
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  array
     */
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => __( 'Delete', 'ywrr' )
        );
        return $actions;
    }

    /**
     * Processes bulk actions
     *
     * @param   $table_name string the database table name
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  void
     */
    function process_bulk_action( $table_name ) {
        global $wpdb;

        if ( 'delete' === $this->current_action() ) {
            $ids = isset( $_GET['id'] ) ? $_GET['id'] : array();
            if ( is_array( $ids ) ) $ids = implode( ',', $ids );

            if ( !empty( $ids ) ) {
                $wpdb->query( "DELETE FROM $table_name WHERE id IN( $ids )" );
            }
        }
    }

    /**
     * It will get rows from database and prepare them to be showed in table
     *
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  void
     */
    function prepare_items() {
        global $wpdb;
        $table_name     = $this->options['select_table'];
        $select_columns = implode( ',', $this->options['select_columns'] );
        $where          = ( $this->options['where'] != '' ? 'WHERE ' . $this->options['where'] : '' );
        $group          = ( $this->options['group'] != '' ? 'GROUP BY ' . $this->options['group'] : '' );
        $per_page       = $this->options['per_page'];
        $count_table    = $this->options['count_table'];
        $view_columns   = $this->options['view_columns'];
        $hidden         = $this->options['hidden_columns'];
        $sortable       = $this->options['sortable_columns'];

        // Here we configure table headers, defined in our methods
        $this->_column_headers = array( $view_columns, $hidden, $sortable );

        // Process bulk action if any
        $this->process_bulk_action( $this->options['delete_table'] );

        // Will be used in pagination settings
        $total_items = $wpdb->get_var( "SELECT COUNT(*) FROM $count_table" );

        // Prepare query params, as usual current page, order by and order direction
        $paged      = isset( $_GET['paged'] ) ? $per_page * ( intval( $_GET['paged'] ) - 1 ) : 0;
        $orderby    = ( isset( $_GET['orderby'] ) && in_array( $_GET['orderby'], array_keys( $this->get_sortable_columns() ) ) ) ? $_GET['orderby'] : $this->options['default_order'];
        $order      = ( isset( $_GET['order'] ) && in_array( $_GET['order'], array( 'asc', 'desc' ) ) ) ? $_GET['order'] : 'asc';

        $this->items = $wpdb->get_results( $wpdb->prepare( "
                        SELECT $select_columns
                        FROM $table_name
                        $where
                        $group
                        ORDER BY $orderby $order
                        LIMIT %d OFFSET %d
                        ", $per_page, $paged ), ARRAY_A );

        $this->set_pagination_args( array(
            'total_items'   => $total_items,
            'per_page'      => $per_page,
            'total_pages'   => ceil( $total_items / $per_page )
        ));
    }

    /**
     * Generates the columns for a single row of the table; overrides original class function
     *
     * @param   $item array the row
     * @since   1.0.0
     * @author  Alberto Ruggiero
     * @return  string
     * @see     WP_List_Table
     */
    protected function single_row_columns( $item ) {
        list( $columns, $hidden ) = $this->get_column_info();

        foreach ( $columns as $column_name => $column_display_name ) {
            $class = "class='$column_name column-$column_name'";

            $style = '';
            if ( in_array( $column_name, $hidden ) )
                $style = ' style="display:none;"';

            $attributes = "$class$style";

            if ( 'cb' == $column_name ) {
                echo '<th scope="row" class="check-column">';
                echo $this->column_cb( $item );
                echo '</th>';
            } elseif ( method_exists( $this, 'column_' . $column_name ) ) {
                echo "<td $attributes>";
                echo call_user_func( array( $this, 'column_' . $column_name ), $item );
                echo "</td>";
            } elseif ( isset( $this->options['custom_columns']['column_' . $column_name] ) ) {
                echo "<td $attributes>";
                echo call_user_func_array( $this->options['custom_columns']['column_' . $column_name] , array( $item, $this ) );
                echo "</td>";
            } else {
                echo "<td $attributes>";
                echo $this->column_default( $item, $column_name );
                echo "</td>";
            }
        }
    }

}