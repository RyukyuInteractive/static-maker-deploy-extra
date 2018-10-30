<?php
namespace Static_Maker\Deploy_Extra;

require_once STATIC_MAKER_DEPLOY_EXTRA_ABSPATH . '/includes/class-wp-list-table.php';

class Deploy_List_Table extends WP_List_Table
{
    private $per_page = 15;

    public function __construct()
    {
        // global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => 'deploy list', //singular name of the listed records
            'plural' => 'deploy lists', //plural name of the listed records
            'ajax' => false, //does this table support ajax?
        ));

    }

    public function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'date' => 'Date',
            'type' => 'Type',
            'status' => 'Status',
        );
        return $columns;
    }

    private function fetch_data($opts)
    {
        global $wpdb;
        $current_page = $opts['current_page'];
        $total_items = $opts['total_items'];

        $search_queries = [];
        $search_values = [];
        if (isset($_POST['s']) && $_POST['s'] !== '') {
            $search_string = '%' . $_POST['s'] . '%';
            $search_query = ' WHERE ';
            foreach ($this->get_columns() as $key => $val) {
                if ($key === 'cb') {continue;}

                array_push($search_queries, "$key LIKE \"%s\"");
                array_push($search_values, $search_string);
            }
        }

        $offset = ($current_page - 1) * $this->per_page;

        $query = 'SELECT * FROM ' . STATIC_MAKER_DEPLOY_EXTRA_DEPLOY_LIST_TABLE_NAME;
        if ($search_queries) {
            $query .= ' WHERE ' . implode(' OR ', $search_queries);
        }
        $query .= " LIMIT %d  OFFSET %d";

        $query = $wpdb->prepare($query, array_merge($search_values, [$this->per_page, $offset]));
        return $wpdb->get_results($query, ARRAY_A);
    }

    private function fetch_total_items()
    {
        global $wpdb;
        return $wpdb->get_var('SELECT COUNT(*) FROM ' . STATIC_MAKER_DEPLOY_EXTRA_DEPLOY_LIST_TABLE_NAME);
    }

    public function prepare_items()
    {
        $this->process_bulk_action();
        $this->process_search();

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $current_page = $this->get_pagenum();
        $total_items = $this->fetch_total_items();

        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $this->per_page, //WE have to determine how many items to show on a page
        ));

        $this->items = $this->fetch_data([
            'current_page' => $current_page,
            'total_items' => $total_items,
        ]);
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'date':
            case 'type':
            case 'status':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="deploy[]" value="%s" />', $item['id']
        );
    }

    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete',
        );
        return $actions;
    }

    private function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            echo __('Deleted (not implemented yet)', STATIC_MAKER_DEPLOY_EXTRA) . ' ' . implode(', ', $_POST['deploy']);
        }
    }

}
