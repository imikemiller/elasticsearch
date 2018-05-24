<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 23/05/18
 * Time: 20:01
 */

namespace Basemkhirat\Elasticsearch\Classes\Repositorys;


use Basemkhirat\Elasticsearch\Classes\QueryDsl;
use Illuminate\Support\Collection;

/**
 * Interface RepositoryInterface
 * @package Basemkhirat\Elasticsearch\Classes\Repositorys
 */
interface RepositoryInterface
{
    /**
     * @param QueryDsl $dsl
     * @param $note
     * @return RepositoryRecord
     */
    public static function store(QueryDsl $dsl, $note);

    /**
     * @param RepositoryRecord $record
     * @return RepositoryRecord|mixed
     * @throws RecordNotFoundException
     */
    public static function retrieve(RepositoryRecord $record);

    /**
     * @param RepositoryRecord $record
     * @return RepositoryRecord
     */
    public static function update(RepositoryRecord $record);

    /**
     * @return Collection
     */
    public static function all();

    /**
     * @param RepositoryRecord $record
     * @return bool
     */
    public static function delete(RepositoryRecord $record);
}