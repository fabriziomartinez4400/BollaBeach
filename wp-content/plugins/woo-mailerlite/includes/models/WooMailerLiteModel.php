<?php

class WooMailerLiteModel
{
	public array $meta = [
		'_woo_ml_product_tracked' => 'tracked',
		'_woo_ml_product_ignored' => 'ignored',
		'_woo_ml_category_tracked' => 'tracked'
	];
	public $attributes;

	protected $table;

	protected array $updateAttributes = [];

	protected $casts = [];

	protected $format;

	protected $removeEmpty = [];

	protected $isResource = false;

	public static $customTablesEnabled;

	public function __construct($attributes = [])
	{
		$this->attributes = $attributes;
	}

	public function __get($attribute)
	{
		if (in_array($attribute, $this->casts)) {

			if (!empty($this->format[$attribute])) {
				switch ($this->format[$attribute]) {
					case 'array':
						if (!is_array($this->attributes[$attribute])) {
							return json_decode($this->attributes[$attribute], true);
						}
						break;
					case 'object':
						return json_decode($this->attributes[$attribute]);
					case 'boolean':
						return (bool)$this->attributes[$attribute];
					case 'string':
						return (string)$this->attributes[$attribute];
					default:
						return $this->attributes[$attribute];
				}
			}
			return $this->attributes[$attribute];
		} elseif (isset($this->attributes[$attribute])) {
			return $this->attributes[$attribute];
		}
		return null;
	}

	public function __set($attribute, $value)
	{
		if (static::class === 'WooMailerLiteCategory') {
			unset($this->meta['_woo_ml_product_tracked']);
		}
		if (in_array($attribute, array_values($this->meta))) {
			$this->attributes[$attribute] = $value;
			$this->updateAttributes[] = $attribute;

		} else {
			$this->attributes[$attribute] = $value;
		}
	}

	public function __isset($attribute)
	{
		return isset($this->attributes[$attribute]);
	}

	public function __call($method, $parameters)
	{
		return $this->queryBuilder()->$method(...$parameters);
	}

	public static function __callStatic($name, $arguments)
	{
		return (new static([]))->$name(...$arguments);
	}

	public function save()
	{
		foreach ($this->updateAttributes as $updateAttribute) {
			if (static::class === 'WooMailerLiteCategory') {
				update_term_meta($this->resource_id, array_search($updateAttribute, $this->meta), $this->attributes[$updateAttribute]);
			} else {
				if ($updateAttribute === 'ignored') {
					$ignoredProducts = WooMailerLiteOptions::get('ignored_products', []);
					if (!$this->attributes[$updateAttribute]) {
						unset($ignoredProducts[$this->resource_id]);
					} else {
						$ignoredProducts[$this->resource_id] = $this->name;
					}
					WooMailerLiteOptions::update('ignored_products', $ignoredProducts);
				}
				update_post_meta($this->resource_id, array_search($updateAttribute, $this->meta), $this->attributes[$updateAttribute]);

			}
		}
	}

	public function exists()
	{
		return count($this->attributes) > 0;
	}

	public function setRelation($relation, $value)
	{
		$this->attributes[$relation] = $value;
		return $this;
	}

	public function queryBuilder()
	{
		return new WooMailerLiteQueryBuilder($this);
	}

	public function getTable()
	{
		return $this->table;
	}

	public function isResource()
	{
		return $this->isResource;
	}

	public function getCastsArray()
	{
		return $this->casts;
	}

	public function getFormatArray()
	{
		return $this->format;
	}

	public function getRemoveEmptyArray()
	{
		return $this->removeEmpty;
	}

	public function setResource()
	{
		$this->isResource = true;
	}

    public function setTable($table)
    {
        $this->table = $table;
    }
}
