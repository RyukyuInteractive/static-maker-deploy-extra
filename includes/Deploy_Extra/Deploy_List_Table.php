<?php
namespace Static_Maker\Deploy_Extra;

require_once STATIC_MAKER_DEPLOY_EXTRA_ABSPATH . '/includes/class-wp-list-table.php';

class Deploy_List_Table extends WP_List_Table
{
    private $per_page = 15;
    private $path;
    private $file;
    private $db;

    public function __construct(Path $path, File $file, DB $db)
    {
        // global $status, $page;
        $this->path = $path;
        $this->file = $file;
        $this->db = $db;

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
            'id' => __('ID', STATIC_MAKER_DEPLOY_EXTRA),
            'date' => __('Revision Date', STATIC_MAKER_DEPLOY_EXTRA),
            'created_at' => __('Created At', STATIC_MAKER_DEPLOY_EXTRA),
            'exists' => __('File Existance', STATIC_MAKER_DEPLOY_EXTRA),
            'type' => __('Type', STATIC_MAKER_DEPLOY_EXTRA),
            'status' => __('Status', STATIC_MAKER_DEPLOY_EXTRA),
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
        $total_items = $this->db->fetch_deploy_list_total_items();

        // sorting
        $orderby = $_GET['orderby'] ?? 'created_at';
        $order = $_GET['order'] ?? 'desc';

        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $this->per_page, //WE have to determine how many items to show on a page
        ));

        $columns = $this->get_columns();
        unset($columns['cb']);
        unset($columns['exists']);

        $this->items = $this->db->fetch_deploy_list([
            'current_page' => $current_page,
            'total_items' => $total_items,
            'per_page' => $this->per_page,
            'columns' => $columns,
            'orderby' => $orderby,
            'order' => $order,
        ]);
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
            case 'date':
            case 'created_at':
                return __($item[$column_name], STATIC_MAKER_DEPLOY_EXTRA);
            case 'type':
                if ($item[$column_name] === 'whole') {
                    return __('whole', STATIC_MAKER_DEPLOY_EXTRA);
                }
                if ($item[$column_name] === 'partial') {
                    return __('partial', STATIC_MAKER_DEPLOY_EXTRA);
                }
                return __($item[$column_name], STATIC_MAKER_DEPLOY_EXTRA);
            case 'status':
                // lines for gettext
                if ($item[$column_name] === 'canceled') {
                    return __('canceled', STATIC_MAKER_DEPLOY_EXTRA);
                }
                if ($item[$column_name] === 'completed') {
                    return __('completed', STATIC_MAKER_DEPLOY_EXTRA);
                }
                if ($item[$column_name] === 'processing') {
                    return __('processing', STATIC_MAKER_DEPLOY_EXTRA);
                }
                if ($item[$column_name] === 'waiting') {
                    return __('waiting', STATIC_MAKER_DEPLOY_EXTRA);
                }
                if ($item[$column_name] === 'deleted') {
                    return __('deleted', STATIC_MAKER_DEPLOY_EXTRA);
                }
                return __($item[$column_name], STATIC_MAKER_DEPLOY_EXTRA);
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    public function column_exists($item)
    {
        return $this->path->exists_revision($item['timestamp']) ? __('Yes', STATIC_MAKER_DEPLOY_EXTRA) : __('-', STATIC_MAKER_DEPLOY_EXTRA);
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
        if ($this->current_action() === 'delete' && isset($_POST['deploy'])) {
            $this->delete_deploy_by_ids($_POST['deploy']);

            $message = __('Deleted', STATIC_MAKER_DEPLOY_EXTRA) . ' ' . implode(', ', $_POST['deploy']);
            echo '<div id="message" class="updated notice is-dismissible">';
            echo '<p>' . $message . '</p>';
            echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">この通知を非表示にする</span></button>';
            echo '</div>';
        }
    }

    public function delete_deploy_by_ids($ids)
    {
        if ($this->db->soft_delete_deploy_by_ids($ids) === false) {
            return false;
        }

        foreach ($ids as $id) {
            $deploy = $this->db->fetch_deploy($id);
            $timestamp = $deploy['timestamp'];

            if ($deploy['status'] === 'waiting') {
                // remove the timestamp from the cron queue
                wp_unschedule_event($timestamp, 'smde_schedule_handler', [intval($timestamp)]);
            }

            if ($this->path->exists_revision($timestamp)) {
                $this->file->recurse_rm($this->path->get_revision_path($timestamp));
            }
        }

        return true;
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'date' => ['date', false],
            'created_at' => ['created_at', false],
        );
        return $sortable_columns;
    }
}
