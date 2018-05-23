<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 14/05/18
 * Time: 17:35
 */

namespace Basemkhirat\Elasticsearch\Classes;


use Basemkhirat\Elasticsearch\Model;

class QueryStore extends Model
{
    public static function store(QueryDsl $queryDsl, $description = null)
    {
        $qs = new static();
        $qs->setIndex(config('es.storage_index'));
        $qs->setType(config('es.storage_index'));
    }

    public static function retrieve($_id)
    {

    }
}
