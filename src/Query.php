<?php

namespace Basemkhirat\Elasticsearch;

use Basemkhirat\Elasticsearch\Classes\Bulk;
use Basemkhirat\Elasticsearch\Classes\QueryDsl;
use Basemkhirat\Elasticsearch\Classes\Repositorys\RepositoryInterface;
use Basemkhirat\Elasticsearch\Classes\Repositorys\RepositoryRecord;
use Basemkhirat\Elasticsearch\Classes\Search;


/**
 * Class Query
 * @package Basemkhirat\Elasticsearch\Query
 * @method $this index(string $index) Set the index name
 * @method $this type(string $type) Set the type name
 * @method $this scroll(string $scroll) Set the query scroll
 * @method $this scrollID(string $scrollID) Set the query scroll ID
 * @method $this searchType(string $searchType) Set the query search type
 * @method $this getSearchType() Get the query search type
 * @method $this getScroll() Get the query scroll
 * @method $this take(int $take = 10) Set the query limit
 * @method $this ignore(mixed ...$args) Ignore bad HTTP response
 * @method $this skip(int $type) Set the query offset
 * @method $this orderBy(string $field, string $direction = 'asc') Set the sorting field
 * @method $this select(mixed ...$args) Set the query fields to return
 * @method $this _id(bool|string $_id = false) Filter by _id
 * @method $this where(string $name, string $operator = "=", mixed|null $value = NULL) Set the query where clause
 * @method $this whereNot(string $name, string $operator = "=", mixed|null $value = NULL) Set the query inverse where clause
 * @method $this whereBetween(string $name, mixed $first_value, mixed|null $last_value = NULL) Set the query where between clause
 * @method $this whereNotBetween(string $name, mixed $first_value, mixed|null $last_value = NULL) Set the query where not between clause
 * @method $this whereIn(string $name, array $value = []) Set the query where in clause
 * @method $this whereNotIn(string $name, array $value = []) Set the query where not in clause
 * @method $this whereExists(string $name, bool $exists = true) Set the query where exists clause
 * @method $this distance(string $name, mixed $value, string $distance) Add a condition to find documents which are some distance away from the given geo point. @see https://www.elastic.co/guide/en/elasticsearch/reference/2.4/query-dsl-geo-distance-query.html
 * @method $this body(array $body = [])
 */
class Query
{

    /**
     * Native elasticsearch connection instance
     * @var Connection
     */
    public $connection;

    /**
     * @var QueryDsl
     */
    public $queryDsl;

    /**
     * Query array
     * @var
     */
    protected $query;

    /**
     * The key that should be used when caching the query.
     * @var string
     */
    protected $cacheKey;

    /**
     * The number of minutes to cache the query.
     * @var int
     */
    protected $cacheMinutes;

    /**
     * The cache driver to be used.
     * @var string
     */
    protected $cacheDriver;

    /**
     * A cache prefix.
     * @var string
     */
    protected $cachePrefix = 'es';

    /**
     * Elastic model instance
     * @var \Basemkhirat\Elasticsearch\Model
     */
    public $model;

    /**
     * @var null
     */
    public $record = null;

    /**
     * Query constructor.
     * @param resource $connection|null
     * @param QueryDsl $queryDsl|null
     */
    function __construct($connection = NULL, QueryDsl $queryDsl = NULL)
    {
        $this->connection = $connection;
        $this->queryDsl = $queryDsl?: new QueryDsl();
    }

    /**
     * Get the index name
     * @return mixed
     */
    public function getIndex()
    {
        return $this->queryDsl->getIndex();
    }

    /**
     * Get the type name
     * @return mixed
     */
    public function getType()
    {
        return $this->queryDsl->getType();
    }

    /**
     * Get the query limit
     * @return int
     */
    protected function getTake()
    {
        return $this->queryDsl->getTake();
    }

    /**
     * Get the query offset
     * @return int
     */
    protected function getSkip()
    {
        return $this->queryDsl->getSkip();
    }

    /**
     * check if it's a valid operator
     * @param $string
     * @return bool
     */
    protected function isOperator($string)
    {
        return $this->queryDsl->isOperator($string);
    }

    /**
     * Just an alias for _id() method
     * @param bool $_id
     * @return $this
     */
    public function id($_id = false)
    {
        return $this->_id($_id);
    }


    /**
     * Search the entire document fields
     * @param null $q
     * @return $this
     */
    public function search($q = NULL, $settings = NULL)
    {

        if ($q) {

            $search = new Search($this, $q, $settings);

            if (!is_callback_function($settings)) {
                $search->boost($settings ? $settings : 1);
            }

            $search->build();

        }

        return $this;
    }

    /**
     * Generate the query body
     * @return array
     */
    protected function getBody()
    {
        return $this->queryDsl->getBody();
    }

    /**
     * Generate the query to be executed
     * @return array
     */
    public function query()
    {

        $query = [];

        $query["index"] = $this->queryDsl->getIndex();

        if ($this->getType()) {
            $query["type"] = $this->queryDsl->getType();
        }

        if($this->model){
            $this->model->boot($this);
        }

        $query["body"] = $this->queryDsl->getBody();

        $query["from"] = $this->queryDsl->getSkip();

        $query["size"] = $this->queryDsl->getTake();

        if (count($this->queryDsl->ignores)) {
            $query["client"] = ['ignore' => $this->queryDsl->ignores];
        }

        $search_type = $this->queryDsl->getSearchType();

        if ($search_type) {
            $query["search_type"] = $search_type;
        }

        $scroll = $this->queryDsl->getScroll();

        if ($scroll) {
            $query["scroll"] = $scroll;
        }

        return $query;
    }

    /**
     * Clear scroll query id
     * @param  string $scroll_id
     * @return array|Collection
     */
    public function clear($scroll_id = NULL)
    {

        $scroll_id = !is_null($scroll_id) ? $scroll_id : $this->scroll_id;

        return $this->connection->clearScroll([
            "scroll_id" => $scroll_id,
            'client' => ['ignore' => $this->ignores]
        ]);
    }

    /**
     * Get the collection of results
     * @param string $scroll_id
     * @return array|Collection
     */
    public function get($scroll_id = NULL)
    {

        $scroll_id = NULL;

        $result = $this->getResult($scroll_id);

        return $this->getAll($result);
    }

    /**
     * Get the first object of results
     * @param string $scroll_id
     * @return object
     */
    public function first($scroll_id = NULL)
    {

        $this->take(1);

        $result = $this->getResult($scroll_id);

        return $this->getFirst($result);
    }

    /**
     * Get query result
     * @param $scroll_id
     * @return mixed
     */
    protected function getResult($scroll_id)
    {

        if (is_null($this->cacheMinutes)) {
            $result = $this->response($scroll_id);
        } else {

            $result = app("cache")->driver($this->cacheDriver)->get($this->getCacheKey());

            if (is_null($result)) {
                $result = $this->response($scroll_id);
            }
        }

        return $result;
    }


    /**
     * Get non cached results
     * @param null $scroll_id
     * @return mixed
     */
    public function response($scroll_id = NULL)
    {

        $scroll_id = !is_null($scroll_id) ? $scroll_id : $this->scroll_id;

        if ($scroll_id) {

            $result = $this->connection->scroll([
                "scroll" => $this->scroll,
                "scroll_id" => $scroll_id
            ]);

        } else {
            $result = $this->connection->search($this->query());
        }

        if (!is_null($this->cacheMinutes)) {
            app("cache")->driver($this->cacheDriver)->put($this->getCacheKey(), $result, $this->cacheMinutes);
        }

        return $result;
    }

    /**
     * Get the count of result
     * @return mixed
     */
    public function count()
    {

        $query = $this->query();

        // Remove unsupported count query keys

        unset(
            $query["size"],
            $query["from"],
            $query["body"]["_source"],
            $query["body"]["sort"]
        );

        return $this->connection->count($query)["count"];
    }

    /**
     * Set the query model
     * @param $model
     * @return $this
     */
    function setModel($model)
    {

        $this->model = $model;

        return $this;
    }


    /**
     * Retrieve all records
     * @param array $result
     * @return array|Collection
     */
    protected function getAll($result = [])
    {

        $new = [];

        foreach ($result["hits"]["hits"] as $row) {

            $model = $this->model ? new $this->model($row["_source"], true) : new Model($row["_source"], true);

            $model->setConnection($model->getConnection());
            $model->setIndex($row["_index"]);
            $model->setType($row["_type"]);

            // match earlier version

            $model->_index = $row["_index"];
            $model->_type = $row["_type"];
            $model->_id = $row["_id"];
            $model->_score = $row["_score"];

            $new[] = $model;
        }

        $new = new Collection($new);

        $new->total = $result["hits"]["total"];
        $new->max_score = $result["hits"]["max_score"];
        $new->took = $result["took"];
        $new->timed_out = $result["timed_out"];
        $new->scroll_id = isset($result["_scroll_id"]) ? $result["_scroll_id"] : NULL;
        $new->shards = (object)$result["_shards"];

        return $new;
    }

    /**
     * Retrieve only first record
     * @param array $result
     * @return object
     */
    protected function getFirst($result = [])
    {

        $data = $result["hits"]["hits"];

        if (count($data)) {

            if ($this->model) {
                $model = new $this->model($data[0]["_source"], true);
            } else {
                $model = new Model($data[0]["_source"], true);
                $model->setConnection($model->getConnection());
                $model->setIndex($data[0]["_index"]);
                $model->setType($data[0]["_type"]);
            }

            // match earlier version

            $model->_index = $data[0]["_index"];
            $model->_type = $data[0]["_type"];
            $model->_id = $data[0]["_id"];
            $model->_score = $data[0]["_score"];

            $new = $model;

        } else {
            $new = NULL;
        }

        return $new;
    }

    /**
     * Paginate collection of results
     * @param int $per_page
     * @param      $page_name
     * @param null $page
     * @return Pagination
     */
    public function paginate($per_page = 10, $page_name = "page", $page = null)
    {

        $this->take($per_page);

        $page = $page ?: Request::get($page_name, 1);

        $this->skip(($page * $per_page) - $per_page);

        $objects = $this->get();

        return new Pagination($objects, $objects->total, $per_page, $page, ['path' => Request::url(), 'query' => Request::query()]);
    }

    /**
     * Insert a document
     * @param      $data
     * @param null $_id
     * @return object
     */
    public function insert($data, $_id = NULL)
    {

        if ($_id) {
            $this->_id = $_id;
        }

        $parameters = [
            "body" => $data,
            'client' => ['ignore' => $this->ignores]
        ];

        if ($index = $this->getIndex()) {
            $parameters["index"] = $index;
        }

        if ($type = $this->getType()) {
            $parameters["type"] = $type;
        }

        if ($this->_id) {
            $parameters["id"] = $this->_id;
        }

        return (object)$this->connection->index($parameters);
    }

    /**
     * Insert a bulk of documents
     * @param $data[] multidimensional array of [id => data] pairs
     * @return object
     */
    public function bulk($data)
    {

        if (is_callback_function($data)) {

            $bulk = new Bulk($this);

            $data($bulk);

            $params = $bulk->body();

        } else {

            $params = [];

            foreach ($data as $key => $value) {

                $params["body"][] = [

                    'index' => [
                        '_index' => $this->getIndex(),
                        '_type' => $this->getType(),
                        '_id' => $key
                    ]

                ];

                $params["body"][] = $value;

            }

        }

        return (object)$this->connection->bulk($params);
    }

    /**
     * Update a document
     * @param      $data
     * @param null $_id
     * @return object
     */
    public function update($data, $_id = NULL)
    {

        if ($_id) {
            $this->_id = $_id;
        }

        $parameters = [
            "id" => $this->_id,
            "body" => ['doc' => $data],
            'client' => ['ignore' => $this->ignores]
        ];

        if ($index = $this->getIndex()) {
            $parameters["index"] = $index;
        }

        if ($type = $this->getType()) {
            $parameters["type"] = $type;
        }

        return (object)$this->connection->update($parameters);
    }


    /**
     * Increment a document field
     * @param     $field
     * @param int $count
     * @return object
     */
    public function increment($field, $count = 1)
    {

        return $this->script("ctx._source.$field += params.count", [
            "count" => $count
        ]);
    }

    /**
     * Increment a document field
     * @param     $field
     * @param int $count
     * @return object
     */
    public function decrement($field, $count = 1)
    {

        return $this->script("ctx._source.$field -= params.count", [
            "count" => $count
        ]);
    }

    /**
     * Update by script
     * @param       $script
     * @param array $params
     * @return object
     */
    public function script($script, $params = [])
    {

        $parameters = [
            "id" => $this->_id,
            "body" => [
                "script" => [
                    "inline" => $script,
                    "params" => $params
                ]
            ],
            'client' => ['ignore' => $this->ignores]
        ];

        if ($index = $this->getIndex()) {
            $parameters["index"] = $index;
        }

        if ($type = $this->getType()) {
            $parameters["type"] = $type;
        }

        return (object)$this->connection->update($parameters);
    }

    /**
     * Delete a document
     * @param null $_id
     * @return object
     */
    public function delete($_id = NULL)
    {

        if ($_id) {
            $this->_id = $_id;
        }

        $parameters = [
            "id" => $this->_id,
            'client' => ['ignore' => $this->ignores]
        ];

        if ($index = $this->getIndex()) {
            $parameters["index"] = $index;
        }

        if ($type = $this->getType()) {
            $parameters["type"] = $type;
        }

        return (object)$this->connection->delete($parameters);
    }

    /**
     * Return the native connection to execute native query
     * @return object
     */
    public function raw()
    {
        return $this->connection;
    }

    /**
     * Check existence of index
     * @return mixed
     */
    function exists()
    {

        $index = new Index($this->index);

        $index->connection = $this->connection;

        return $index->exists();
    }


    /**
     * Create a new index
     * @param      $name
     * @param bool $callback
     * @return mixed
     */
    function createIndex($name, $callback = false)
    {

        $index = new Index($name, $callback);

        $index->connection = $this->connection;

        return $index->create();
    }


    /**
     * Drop index
     * @param $name
     * @return mixed
     */
    function dropIndex($name)
    {

        $index = new Index($name);

        $index->connection = $this->connection;

        return $index->drop();
    }

    /**
     * create a new index [alias to createIndex method]
     * @param bool $callback
     * @return mixed
     */
    function create($callback = false)
    {

        $index = new Index($this->index, $callback);

        $index->connection = $this->connection;

        return $index->create();
    }

    /**
     * Drop index [alias to dropIndex method]
     * @return mixed
     */
    function drop()
    {

        $index = new Index($this->index);

        $index->connection = $this->connection;

        return $index->drop();
    }

    /* Caching Methods */

    /**
     * Indicate that the results, if cached, should use the given cache driver.
     *
     * @param  string $cacheDriver
     *
     * @return $this
     */
    public function cacheDriver($cacheDriver)
    {

        $this->cacheDriver = $cacheDriver;

        return $this;
    }

    /**
     * Set the cache prefix.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function CachePrefix($prefix)
    {

        $this->cachePrefix = $prefix;

        return $this;
    }


    /**
     * Get a unique cache key for the complete query.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cachePrefix . ':' . ($this->cacheKey ?: $this->generateCacheKey());
    }


    /**
     * Generate the unique cache key for the query.
     * @return string
     */
    public function generateCacheKey()
    {

        return md5(json_encode($this->query()));
    }

    /**
     * Indicate that the query results should be cached.
     * @param  \DateTime|int $minutes
     * @param  string $key
     * @return $this
     */
    public function remember($minutes, $key = null)
    {

        list($this->cacheMinutes, $this->cacheKey) = [$minutes, $key];

        return $this;
    }

    /**
     * Indicate that the query results should be cached forever.
     * @param  string $key
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function rememberForever($key = null)
    {
        return $this->remember(-1, $key);
    }

    /**
     * @param $method
     * @param $parameters
     * @return $this
     */
    function __call($method, $parameters)
    {

        if (method_exists($this, $method)) {
            return $this->$method(...$parameters);
        }elseif(method_exists($this->queryDsl, $method)){
            /*
             * All the chainable methods that were moved to QueryDsl
             * can be handled here and survive as they all expect
             * $this to be the returned value.
             */
            $this->queryDsl->$method(...$parameters);
            return $this;

        } else {

            // Check for model scopes

            $method = "scope" . ucfirst($method);

            if (method_exists($this->model, $method)) {
                $parameters = array_merge([$this], $parameters);
                $this->model->$method(...$parameters);
                return $this;
            }
        }
    }

    /**
     * @return QueryDsl
     */
    public function getQueryDsl()
    {
        return $this->queryDsl;
    }

    /**
     * @param QueryDsl $queryDsl
     */
    public function setQueryDsl($queryDsl)
    {
        $this->queryDsl = $queryDsl;
    }

}
