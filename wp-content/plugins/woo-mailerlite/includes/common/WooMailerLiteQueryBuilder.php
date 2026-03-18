<?php

class WooMailerLiteQueryBuilder extends WooMailerLiteDBConnection
{
    use WooMailerLiteResources;
    protected $model;

    private $select = "*";

    protected $andWhere = false;

    protected $withoutPrefix = false;
    private $allowedOperators = ['=', '!=', '<>', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'IS', 'IS NOT'];

    public function __construct($model)
    {
        $this->model = $model;
    }

    private function getOperation($operation)
    {
        $operation = strtoupper(trim($operation));
        if (!in_array($operation, $this->allowedOperators, true)) {
            return '=';
        }

        return $operation;
    }

    private function addPrefix($table) {
        if (strpos($table, $this->db()->prefix) === 0) {
            return $table;
        }

        return $this->db()->prefix . $table;
    }

    private function esc($value)
    {
        if (strpos($value, '.') !== false || strpos($value, $this->db()->prefix) === 0) {
            return $value;
        }

        return esc_sql(preg_replace('/[^a-zA-Z0-9_-]/', '', $value));
    }

    private function prepareColumn($column)
    {
        if (strpos($column, '.') !== false) {
            $parts = explode('.', $column, 2);
            return $this->addPrefix($parts[0]) . '.' . $this->esc($parts[1]);
        }
        
        return $this->esc($column);
    }

    public function where($column, $operation = '=', $value = null)
    {
        if ($value === null) {
            $value = $operation;
            $operation = '=';
        }
        if ($this->model->isResource() || ((get_class($this->model) === 'WooMailerLiteCustomer') && !$this->customTableEnabled())) {
            $this->set_resource(get_class($this->model));
            $this->args[$column] = $value;
            return $this;
        }
        $operation = $this->getOperation($operation);
        $column = $this->prepareColumn($column);
        if ($this->hasWhere) {
            $this->andWhere($column, $operation, $value);
        } else {
            $this->query .= $this->db()->prepare(" WHERE {$column} {$operation} %s", $value);
        }
        $this->hasWhere = true;
        return $this;
    }

    public function get(int $count = -1)
    {
        if ($count == -1 && (get_class($this->model) === 'WooMailerLiteCustomer')) {
            return $this->buildQuery($count)->executeQuery();
        }
        if ($this->model->isResource() || ((get_class($this->model) === 'WooMailerLiteCustomer') && !$this->customTableEnabled())) {

            $this->set_resource(get_class($this->model));
            return $this->resource_get($count);
        }
        $collection = new WooMailerLiteCollection();

        $data = $this->buildQuery($count)->executeQuery();
        if ($this->countOnly && (get_class($this->model) === 'WooMailerLiteProduct')) {
            return $data;
        }
        foreach ($data as $item) {
            if ((get_class($this->model) === 'WooMailerLiteCustomer') && empty($item->email)) {
                continue;
            }
            if (get_class($this->model) === 'WooMailerLiteProduct' && !$this->model->isResource()) {
                $item = wc_get_product($item);
                if (!$item) {
                    continue;
                }
                $itemData = $item->get_data();
                $this->prepareResourceData(get_class($this->model), $itemData, $item->last_order_id ?? $item);
                $item = $itemData;
            }
            $model = new $this->model();

            if ($this->model->getCastsArray()) {
                if ($this->model->isResource()) {
                    $this->prepareResourceData(get_class($this->model), $item, $item->last_order_id ?? $item);
                }
                $model->attributes = array_intersect_key((array)$item, array_flip($this->model->getCastsArray() ?? []));
            } else {
                $model->attributes = (array)$item;
            }
            if (!empty($this->model->getFormatArray())) {
                foreach ($this->model->getFormatArray() as $key => $format) {
                    if (!isset($model->attributes[$key])) {
                        continue;
                    }
                    switch ($format) {
                        case 'array':
                            if (is_string($model->attributes[$key])){
                                $model->attributes[$key] = json_decode($model->attributes[$key], true);
                            }
                            break;
	                    case 'boolean':
		                    $model->attributes[$key] = (bool) $model->attributes[$key];
		                    break;
                        case 'string':
                            $model->attributes[$key] = (string) $model->attributes[$key];
                            break;
                    }
                }
            }
            if (!empty($this->model->getRemoveEmptyArray())) {
                foreach ($this->model->getRemoveEmptyArray() as $key) {
                    if (isset($model->attributes[$key])) {
                        if (empty($model->attributes[$key]) || (is_string($model->attributes[$key]) && ctype_space($model->attributes[$key]))) {
                            unset($model->attributes[$key]);
                        }
                    }
                }
            }

            $collection->collect($model);
        }
        return $collection;
    }

    public function buildQuery($count = -1)
    {
        $this->query = "SELECT " . $this->select . " from " . $this->addPrefix($this->esc($this->model->getTable())) . $this->query;
        if ($count != -1) {
            $this->query .= $this->db()->prepare(" LIMIT %d", absint($count));
        }

        $this->query .= ";" ;
        return $this;
    }

    public function whereIn($column, $values)
    {
        if ($this->model->isResource()) {
            $this->args[$column] = $values;
            return $this;
        }
        $column = $this->prepareColumn($column);
        $this->hasWhere = true;
        if (empty($values)) {
            $this->query .= " WHERE 1=0"; // Empty IN clause returns no results
            return $this;
        }
        $placeholders = implode(',', array_fill(0, count($values), '%s'));
        $this->query .= $this->db()->prepare(" WHERE {$column} IN ({$placeholders})", ...$values);
        return $this;
    }

    public function groupBy($column)
    {
        $this->query .= " GROUP BY {$this->prepareColumn($column)}";
        return $this;
    }

    public function orderBy($column)
    {
        $this->query .= " ORDER BY {$this->prepareColumn($column)}";
        return $this;
    }

    public function join($table, $tableLeft = null, $tableRight = null, $alias = null)
    {
        if ($table instanceof WooMailerLiteQueryBuilder) {
            $alias = $this->esc($alias ?? 'subquery');
            $tableLeft = $this->esc($tableLeft);
            $tableRight = $this->esc($tableRight);
            $this->query .= " INNER JOIN (" . rtrim($table->buildQuery()->query, ';') . ") AS " . $this->addPrefix($alias) . " ON " . $this->addPrefix($tableLeft) . " = " . $this->addPrefix($tableRight);
            return $this;
        }

        if (!$tableRight && is_array($tableLeft)) {
            $operator = null;
            $joins = [];
            foreach ($tableLeft as $key => $value) {
                $key = $this->esc($key);
                if (is_string($value)) {
                    if (strpos($value, '.') === false) {
                        // String value - use prepare
                        $value = $this->db()->prepare('%s', $value);
                    } else {
                        // Column reference - escape
                        $value = $this->addPrefix($this->esc($value));
                    }
                }

                if (is_array($value) && is_string(array_keys($value)[0])) {
                    $originalKey = array_keys($value)[0];
                    $operator = $this->getOperation($originalKey);
                    if (empty($value[$originalKey])) {
                        $findin = "(1=0)"; // Empty IN clause
                    } else {
                        $placeholders = implode(',', array_fill(0, count($value[$originalKey]), '%s'));
                        $findin = $this->db()->prepare("({$placeholders})", ...$value[$originalKey]);
                    }
                    $value = " {$operator} {$findin}";
                }
                $joins[] = $this->addPrefix($key) . ($operator ? '' : ' = ') . $value;
            }
            $table = $this->esc($table);
            $this->query .= " INNER JOIN " . $this->addPrefix($table) . " ON " . implode(' AND ', $joins);
            return $this;
        }
        $table = $this->addPrefix($this->esc($table));
        $tableLeft = $this->addPrefix($this->esc($tableLeft));
        $tableRight = $this->addPrefix($this->esc($tableRight));
        $this->query .= " INNER JOIN {$table} ON {$tableLeft} = {$tableRight}";
        return $this;
    }

    public function from($table)
    {
        $this->model->setTable($this->esc($table));
        return $this;
    }

    public function leftJoin(string $table, $tableLeft, string $tableRight = '')
    {
        if (!$tableRight && is_array($tableLeft)) {
            // this condition is for join on key = value and another key = value
            $joins = [];
            foreach ($tableLeft as $key => $value) {
                $key = $this->esc($key);
                if (strpos($value, '.') === false) {
                    // String value - use prepare
                    $value = $this->db()->prepare('%s', $value);
                } else {
                    // Column reference - escape
                    $value = $this->addPrefix($this->esc($value));
                }
                $joins[] = $this->addPrefix($key) . ' = ' . $value;
            }
            $table = $this->esc($table);
            $this->query .= " LEFT JOIN " . $this->addPrefix($table) . " ON " . implode(' AND ', $joins);
            return $this;
        }
        $table = $this->addPrefix($this->esc($table));
        $tableLeft = $this->addPrefix($this->esc($tableLeft));
        $tableRight = $this->addPrefix($this->esc($tableRight));
        $this->query .= " LEFT JOIN {$table} ON {$tableLeft} = {$tableRight}";
        return $this;
    }

    public function andWhere($column, $operation, $value)
    {
        $operation = $this->getOperation($operation);
        $column = $this->prepareColumn($column);
        if ($this->andWhere) {
            $this->andWhere = false;
            $this->query .= $this->db()->prepare(" {$column} {$operation} %s", $value);
            return $this;
        }
        $this->query .= $this->db()->prepare(" AND {$column} {$operation} %s", $value);
        return $this;
    }

    public function orWhere($column, $operation = '=', $value = null)
    {
        if ($value === null) {
            $value = $operation;
            $operation = '=';
        }
        
        $operation = $this->getOperation($operation);
        if (!$this->withoutPrefix) {
            $column = $this->addPrefix($column);
        }
        $column = $this->prepareColumn($column);
        if ($value === null) {
            $this->query .= " OR {$column} IS NULL";
        } else {
            $this->query .= $this->db()->prepare(" OR {$column} {$operation} %s", $value);
        }
        return $this;
    }

    public function withoutPrefix($callback)
    {
        $this->withoutPrefix = true;
        $callback($this);
        $this->withoutPrefix = false;
        return $this;
    }

    public function andCombine($callback)
    {
        $this->andWhere = true;
        $this->query .= ' AND (';
        $callback($this);
        $this->query .= ')';
        return $this;
    }

    public function select($select)
    {        
        $dangerous = [
            ';',
            '--',
            '/*',
            '*/',
        ];
        
        foreach ($dangerous as $pattern) {
            $select = str_replace($pattern, '', $select);
        }
        
        $dangerousKeywords = ['INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE', 'ALTER', 'TRUNCATE', 'EXEC', 'EXECUTE'];
        foreach ($dangerousKeywords as $keyword) {
            $select = preg_replace('/\b' . $keyword . '\b/i', '', $select);
        }

        $this->select = $select;
        return $this;
    }

    public function builder()
    {
        return new static($this->model);
    }

    public function all($arguments = [])
    {
        if ($this->model->isResource()) {
            $this->set_resource(get_class($this->model));
            return $this->resource_all();
        } else {
            return $this->get();
        }
    }

    public function create($data)
    {
        return $this->prepareQuery('create', $data);
    }

    public function update($data)
    {
        return $this->prepareQuery('update', $data, $this->model);
    }

    public function delete()
    {
        return $this->prepareQuery('delete', [], $this->model);
    }

    public function first()
    {
        return $this->get(1)->items[0] ?? null;
    }

    public function firstOrCreate($where, $data)
    {
        $exists = $this->where(array_key_first($where), $where[array_key_first($where)])->first();
        if ($exists) {
            return $exists;
        }
        $this->create(array_merge($where, $data));
        $this->query = '';
        $this->hasWhere = false;
        return $this->where(array_key_first($where), $where[array_key_first($where)])->first();
    }

    public function updateOrCreate($where, $data)
    {
        $exists = $this->where(array_key_first($where), $where[array_key_first($where)])->first();
        if ($exists) {
            $this->query = '';
            $this->hasWhere = false;
            $this->model = $exists;
            $this->update($data);
            return $exists;
        }
        $this->create(array_merge($where, $data));
        $this->query = '';
        $this->hasWhere = false;
        return $this->where(array_key_first($where), $where[array_key_first($where)])->first();
    }

    protected function prepareQuery(string $action, array $data, $model = null)
    {
        switch ($action) {
            case 'create':
                foreach ($data as &$value) {
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                }
                $this->db()->insert($this->addPrefix($this->model->getTable()), $data);
                break;
            case 'update':
                foreach ($data as &$value) {
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                }
                $this->db()->update(
                    $this->addPrefix($this->model->getTable()),
                    $data,
                    [
                        'id' => $model->id
                    ]
                );
                break;
            case 'delete':
                $this->db()->delete($this->addPrefix($this->model->getTable()), ['id' => $model->id] );
                break;
        }
        return true;
    }

    public function getFromOrder()
    {
        $this->model->setResource();
        return $this;
    }
}
