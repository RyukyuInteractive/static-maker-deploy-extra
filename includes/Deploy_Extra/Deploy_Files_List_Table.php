<?php
namespace Static_Maker\Deploy_Extra;

require_once STATIC_MAKER_DEPLOY_EXTRA_ABSPATH . '/includes/class-wp-list-table.php';

class Deploy_Files_List_Table extends WP_List_Table
{
    private $deploy_id;
    private $per_page = 15;
    private $db;

    public function __construct(DB $db, $id)
    {
        // global $status, $page;

        $this->deploy_id = $id;
        $this->db = $db;

        //Set parent defaults
        parent::__construct(array(
            'singular' => 'deploy file', //singular name of the listed records
            'plural' => 'deploy files', //plural name of the listed records
            'ajax' => false, //does this table support ajax?
        ));

    }

    public function get_columns()
    {
        $columns = array(
            'file_path' => 'File Path',
            'action' => 'Action',
        );
        return $columns;
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
        // $total_items = $this->db->fetch_deploy_list_total_items();
        $total_items = 0;

        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $this->per_page, //WE have to determine how many items to show on a page
        ));

        $columns = $this->get_columns();

        $this->items = $this->db->fetch_deploy_files([
            'id' => $this->deploy_id,
            'current_page' => $current_page,
            'total_items' => $total_items,
            'per_page' => $this->per_page,
            'columns' => $columns,
        ]);
    }

    public function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="deploy[]" value="%s" />', $item['id']
        );
    }

    public function column_date($item)
    {
        $url = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $query = parse_url($url, PHP_URL_QUERY);
        $url .= $query ? '&deploy=' . $item['id'] : '?deploy_id=' . $item['id'];
        return "<a href=\"$url\";>" . $item['date'] . '</a>';
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
        if ('delete' === $this->current_action() && isset($_POST['deploy'])) {
            $message = __('Deleted', STATIC_MAKER_DEPLOY_EXTRA) . ' ' . implode(', ', $_POST['deploy']);
            echo '<div id="message" class="updated notice is-dismissible">';
            echo '<p>' . $message . '</p>';
            echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">この通知を非表示にする</span></button>';
            echo '</div>';
        }
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => ['id', false],
            'date' => ['date', true],
        );
        return $sortable_columns;
    }
}
