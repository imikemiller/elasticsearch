<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 23/05/18
 * Time: 20:01
 */

namespace Basemkhirat\Elasticsearch\Classes\Repositorys;


use Basemkhirat\Elasticsearch\Classes\QueryDsl;
use Basemkhirat\Elasticsearch\Model;

/**
 * Class ESRepository
 * @package Basemkhirat\Elasticsearch\Classes\Repositorys
 */
class ESRepository extends Model implements RepositoryInterface
{
    /**
     * ESRepository constructor.
     */
    public function __construct()
    {
        $this->type = env('ES_STORE_INDEX');
        $this->index = env('ES_STORE_INDEX_ALIAS');
        parent::__construct();
    }

    /**
     * @param QueryDsl $dsl
     * @param $note
     * @return ESRepository|RepositoryInterface
     */
    public static function store(QueryDsl $dsl, $note)
    {
        $model = new static();
        $model->query = serialize($dsl);
        $model->note = (string)$note;
        $model->save();
        return $model;
    }

    /**
     * @param RepositoryRecord $recordId
     * @return RepositoryInterface|mixed
     */
    public static function retrieve(RepositoryRecord $recordId)
    {
        return self::find($recordId->getId());
    }
}