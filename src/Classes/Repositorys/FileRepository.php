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
 * Class FileRepository
 * @package Basemkhirat\Elasticsearch\Classes\Repositorys
 */
class FileRepository implements RepositoryInterface
{

    /**
     * @param QueryDsl $dsl
     * @param $note
     * @return RepositoryRecord
     */
    public static function store(QueryDsl $dsl, $note)
    {
        $record = new RepositoryRecord(uniqid('es:'));
        $record->setQueryDsl($dsl);
        $record->setNote($note);
        file_put_contents(base_path('storage/queries/'.$record->getId()),$record);
        return $record;
    }

    /**
     * @param RepositoryRecord $record
     * @return RepositoryRecord
     * @throws RecordNotFoundException
     */
    public static function retrieve(RepositoryRecord $record)
    {
        if($data = file_get_contents(base_path('storage/queries/'.$record->getId()))) {
            return unserialize($data);
        }

        throw new RecordNotFoundException("No record found with the ID {$record->getId()} in this repository.");
    }

    /**
     * @param RepositoryRecord $record
     * @return RepositoryRecord
     */
    public static function update(RepositoryRecord $record)
    {
        file_put_contents(base_path('storage/queries/'.$record->getId()),$record);
        return $record;
    }

    /**
     * @return Collection
     */
    public static function all()
    {
        return new Collection(array_diff(scandir(base_path('storage/queries/')), array('.', '..')));
    }

    /**
     * @param RepositoryRecord $record
     * @return bool
     */
    public static function delete(RepositoryRecord $record)
    {
        return unlink(base_path('storage/queries/'.$record->getId()));
    }
}