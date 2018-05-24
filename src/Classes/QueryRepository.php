<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 24/05/18
 * Time: 10:02
 */

namespace Basemkhirat\Elasticsearch\Classes;


use Basemkhirat\Elasticsearch\Classes\Repositorys\RepositoryInterface;
use Basemkhirat\Elasticsearch\Classes\Repositorys\RepositoryRecord;

class QueryRepository
{

    public function store(QueryDsl $dsl, $note = null,RepositoryInterface $repository = null)
    {
        if($repository = $this->resolveRepository($repository)){
            return $repository::store($dsl,$note);
        }

        return null;
    }

    public function retrieve(RepositoryRecord $record, RepositoryInterface $repository = null)
    {
        if($repository = $this->resolveRepository($repository)){
            return $repository::retrieve($record);
        }

        return null;
    }

    public function update(RepositoryRecord $record, RepositoryInterface $repository = null)
    {
        if($repository = $this->resolveRepository($repository)){
            return $repository::update($record);
        }

        return null;
    }

    public function all(RepositoryInterface $repository = null)
    {
        if($repository = $this->resolveRepository($repository)){
            return $repository::all();
        }

        return null;
    }

    public function delete(RepositoryRecord $record,RepositoryInterface $repository = null)
    {
        if($repository = $this->resolveRepository($repository)){
            return $repository::delete($record);
        }

        return null;
    }

    public function resolveRepository(RepositoryInterface $repository = null)
    {
        if(function_exists('app') && !$repository){
            $repository = app('config')->get('es.store.driver');
            $repository = new $repository();
        }

        if(!$repository || !$repository instanceof RepositoryInterface){
            trigger_error('No available query repository.',E_USER_WARNING);
            return null;
        }
        return $repository;
    }
}