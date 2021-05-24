<?php

namespace demi\sort;

use Yii;
use yii\data\Sort;
use yii\db;

class Sort extends Sort
{
    public $pinAtrribute = 'sort';

    public function getOrders($recalculate = false)
    {
        $attributeOrders = $this->getAttributeOrders($recalculate);
        $orders = [];
        foreach ($attributeOrders as $attribute => $direction) {
            $definition = $this->attributes[$attribute];
            $columns = $definition[$direction === SORT_ASC ? 'asc' : 'desc'];
            if (is_array($columns) || $columns instanceof \Traversable) {
                foreach ($columns as $name => $dir) {
                    $orders[$name] = $dir;
                }
            } else {
                $orders[] = $columns;
            }
        }

        //return $orders;
        return array_merge([$this->pinAtrribute  => new Expression("{$this->pinAtrribute} IS NULL, {$this->pinAtrribute} ASC")], $orders);
    }
}
