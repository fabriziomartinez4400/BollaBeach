<?php

class WooMailerLiteCollection
{
    /**
     * @var array $items
     */
    public $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Collect items
     * @param array $items
     * @return void
     */
    public function collect(?WooMailerLiteModel $items)
    {
        $this->items[] = $items;
    }

    /**
     * Return array of items
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->items as $model) {
            $array[] = $model->attributes;
        }
        return $array;
    }

    /**
     * Return count of items
     * @return int
     */
    public function count()
    {
        return count(array_filter($this->items));
    }

    public function hasItems()
    {
        return $this->count() > 0;
    }

    public function first()
    {
        return $this->items[0];
    }

    public function last()
    {
        return $this->items[$this->count() - 1];
    }

    public function empty()
    {
        $this->items = [];
    }
}