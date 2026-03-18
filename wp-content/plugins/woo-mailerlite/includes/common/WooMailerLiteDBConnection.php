<?php
global $wpdb;

class WooMailerLiteDBConnection
{
    use WooMailerLiteResources;

    /**
     * @var wpdb $db
     */
    private $db;

    /**
     * @var string $wooMailerliteTablePrefix
     */
    private $wooMailerliteTablePrefix = 'woo_mailerlite_';

    /**
     * @var string $query
     */
    public $query = '';

    /**
     * @var string $column
     */
    private $column;

    /**
     * @var string $primaryKey
     */
    private $primaryKey = '';

    /**
     * @var string $select
     */
    private string $select = '*';

    /**
     * @var WooMailerLiteDBConnection|null $db_connection_instance
     */
    protected static $db_connection_instance = null;

    protected $hasWhere = false;

    protected $countOnly = false;


    private $columnsOnly = false;

    /**
     * @var string|null $table
     */


    protected $withRelation = "";


    /**
     * Execute the prepared DB query
     * @param $updateStructure
     * @return mixed
     */
    public function executeQuery($updateStructure = false)
    {
        $this->db()->flush();
        if ($updateStructure) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $result = dbDelta($this->query);
        } else {
            if ($this->columnsOnly) {
                $result = $this->db()->get_col($this->query);
            } else {
                $result = $this->db()->get_results($this->query);
            }
        }
        return $result;
    }

    /**
     * Get the wpdb instance
     * @return wpdb
     */
    public function db()
    {
        global $wpdb;
        $this->db = $wpdb;
        return $this->db;
    }

    /**
     * Show all the tables created by the plugin
     * @return array
     */
    public function showWooMlTables()
    {
        $tables = [];
        foreach ($this->db()->get_results('SHOW TABLES LIKE "' . $this->getDbTablePrefix() . '%"', ARRAY_N) as $table)
        {
            $tables[] = $table[0];
        }
        return $tables;
    }

    /**
     * Get the table prefix
     * @return string
     */
    protected function getDbTablePrefix()
    {
        return $this->db()->prefix . $this->wooMailerliteTablePrefix;
    }

    public static function setTable(string $table)
    {
        static::$db_connection_instance->table = $table;
    }

    public function count()
    {
        $this->countOnly = true;
        $data = $this->get();
        $this->countOnly = false;
        if ($data instanceof WooMailerLiteCollection) {
            return $data->count();
        } else {
            return is_array($data) ? count($data) : (is_int($data) ? $data : 0);
        }
    }

    public function toArray()
    {
        return $this->model->attributes;
    }

    public static function with($relation)
    {
        static::db_connection_instance()->withRelation = $relation;
        return static::db_connection_instance();
    }

    public function columnsOnly()
    {
        $this->columnsOnly = true;
        return $this;
    }
}
