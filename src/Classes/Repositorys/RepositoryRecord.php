<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 23/05/18
 * Time: 20:42
 */

namespace Basemkhirat\Elasticsearch\Classes\Repositorys;
use Basemkhirat\Elasticsearch\Classes\QueryDsl;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;


/**
 * Class RepositoryRecordId
 * @package Basemkhirat\Elasticsearch\Classes\Repositorys
 */
class RepositoryRecord implements Arrayable,Jsonable
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $queryDsl;

    /**
     * @var string
     */
    protected $note;

    /**
     * RepositoryRecordId constructor.
     * @param string $id
     */
    public function __construct($id=null)
    {
        $this->id = (string)$id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = (string)$id;
    }

    /**
     * @return QueryDsl
     */
    public function getQueryDsl()
    {
        return unserialize($this->queryDsl);
    }

    /**
     * @param QueryDsl $queryDsl
     */
    public function setQueryDsl(QueryDsl $queryDsl)
    {
        $this->queryDsl = serialize($queryDsl);
    }

    /**
     * @return string|null
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string $note|null
     */
    public function setNote($note = null)
    {
        $this->note = $note;
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return serialize($this);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(),$options);
    }
}