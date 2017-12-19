<?php

/*
Name : vinay
Desc : Display all list of Songs in admin side
*/

add_action( 'admin_menu', 'donation_list_menu' );

function donation_list_menu() {
	add_menu_page( 'Songs List', 'Songs List', 'manage_options', 'songslist', 'my_songs_list','dashicons-format-audio',25);
}

if(!class_exists('WP_List_Table')){
 
   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
 
}

class Songs_List_Table_Class extends WP_List_Table
{

    function __construct()
    {
        global $page;
        parent::__construct(array(
            'singular' => 'song',
            'plural' => 'songs',
        ));
    }

    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    function column_song_name($item)
    {
    	
    	$actions = array(
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'Songs_list_table')),

            'edit' => sprintf('<a href="?page=add_edit&action=edit&id=%s">%s</a>', $item['id'], __('Edit', 'Songs_list_table')),
        );

        $return = sprintf('%s',        
            $this->row_actions($actions)
        );
        return $return;
    }
    
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', 
            'song_name' => __('Song Name', 'Songs_list_table'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
        	'id' => array('id',true),
            'song_name' => array('song_name', false),
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'songs_list'; // do not forget about tables prefix

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    function prepare_items($search = null)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'songs_list';
        $per_page = 10;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? intval($_REQUEST['paged']) : 1;
        $offset = ( $paged * $per_page ) - $per_page;

        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        /* for searching */

        $query = (!empty($search)) ? ' WHERE song_name LIKE "%'.$search.'%"' : '';
        
        /* End searching */

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name $query");

        $prepare = sprintf("SELECT * FROM %s %s ORDER BY %s %s LIMIT %d, %d", $table_name, $query, $orderby, $order, $offset, $per_page );
        $this->items = $wpdb->get_results($prepare, ARRAY_A);
  
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }
}
function my_songs_list()
{
    global $wpdb;
    $table = new Songs_List_Table_Class();
    if(isset($_REQUEST['s'])){
        $table->prepare_items($_REQUEST['s']);
    } else {
        $table->prepare_items();
    }
    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'Songs_list_table'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
	<div class="wrap">
		<h1>Songs List</h1>
	    <?php echo $message; ?>
	    <form id="songs-table" method="GET">
	        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
	        <?php 
	        	$table->search_box('Search', 'search');
		        $table->display();
	        ?>
	    </form>
	</div>
<?php
}
