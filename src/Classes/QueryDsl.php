<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 13/05/18
 * Time: 17:27
 */

namespace Basemkhirat\Elasticsearch\Classes;
use Basemkhirat\Elasticsearch\Classes\Repositorys\RepositoryInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;


/**
 * Class QueryDsl
 * @package Basemkhirat\Elasticsearch
 */
class QueryDsl implements Arrayable,Jsonable
{
    /**
     * Ignored HTTP errors
     * @var array
     */
    public $ignores = [];

    /**
     * Filter operators
     * @var array
     */
    public $operators = [
        "=",
        ">",
        ">=",
        "<",
        "<=",
        "like",
        "exists"
    ];

    /**
     * Query index name
     * @var
     */
    public $index;

    /**
     * Query type name
     * @var
     */
    public $type;

    /**
     * Query type key
     * @var
     */
    public $_id;

    /**
     * Query body
     * @var array
     */
    public $body = [];

    /**
     * Query bool filter
     * @var array
     */
    public $filter = [];

    /**
     * Query bool must
     * @var array
     */
    public $must = [];

    /**
     * Query bool must not
     * @var array
     */
    public $must_not = [];

    /**
     * Query returned fields list
     * @var array
     */
    public $_source = [];

    /**
     * Query sort fields
     * @var array
     */
    public $sort = [];

    /**
     * Query scroll time
     * @var string
     */
    public $scroll;

    /**
     * Query scroll id
     * @var string
     */
    public $scroll_id;

    /**
     * Query search type
     * @var int
     */
    public $search_type;

    /**
     * Query limit
     * @var int
     */
    public $take = 10;

    /**
     * Query offset
     * @var int
     */
    public $skip = 0;

    /**
     * Set the index name
     * @param $index
     * @return $this
     */
    public function index($index)
    {

        $this->index = $index;

        return $this;
    }

    /**
     * Get the index name
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Set the type name
     * @param $type
     * @return $this
     */
    public function type($type)
    {

        $this->type = $type;

        return $this;
    }

    /**
     * Get the type name
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the query scroll
     * @param string $scroll
     * @return $this
     */
    public function scroll($scroll)
    {

        $this->scroll = $scroll;

        return $this;
    }

    /**
     * Set the query scroll ID
     * @param string $scroll
     * @return $this
     */
    public function scrollID($scroll)
    {

        $this->scroll_id = $scroll;

        return $this;
    }

    /**
     * Set the query search type
     * @param string $type
     * @return $this
     */
    public function searchType($type)
    {

        $this->search_type = $type;

        return $this;
    }

    /**
     * get the query search type
     * @return int
     */
    public function getSearchType()
    {
        return $this->search_type;
    }

    /**
     * Get the query scroll
     * @return string
     */
    public function getScroll()
    {
        return $this->scroll;
    }

    /**
     * Set the query limit
     * @param int $take
     * @return $this
     */
    public function take($take = 10)
    {

        $this->take = $take;

        return $this;
    }

    /**
     * Ignore bad HTTP response
     * @return $this
     */
    public function ignore()
    {

        $args = func_get_args();

        foreach ($args as $arg) {

            if (is_array($arg)) {
                $this->ignores = array_merge($this->ignores, $arg);
            } else {
                $this->ignores[] = $arg;
            }

        }

        $this->ignores = array_unique($this->ignores);

        return $this;
    }

    /**
     * Get the query limit
     * @return int
     */
    public function getTake()
    {
        return $this->take;
    }

    /**
     * Set the query offset
     * @param int $skip
     * @return $this
     */
    public function skip($skip = 0)
    {
        $this->skip = $skip;
        return $this;
    }

    /**
     * Get the query offset
     * @return int
     */
    public function getSkip()
    {
        return $this->skip;
    }

    /**
     * Set the sorting field
     * @param        $field
     * @param string $direction
     * @return $this
     */
    public function orderBy($field, $direction = "asc")
    {

        $this->sort[] = [$field => $direction];

        return $this;
    }

    /**
     * check if it's a valid operator
     * @param $string
     * @return bool
     */
    public function isOperator($string)
    {

        if (in_array($string, $this->operators)) {
            return true;
        }

        return false;
    }

    /**
     * Set the query fields to return
     * @return $this
     */
    public function select()
    {

        $args = func_get_args();

        foreach ($args as $arg) {

            if (is_array($arg)) {
                $this->_source = array_merge($this->_source, $arg);
            } else {
                $this->_source[] = $arg;
            }

        }

        return $this;
    }

    /**
     * Filter by _id
     * @param bool $_id
     * @return $this
     */
    public function _id($_id = false)
    {

        $this->_id = $_id;

        $this->filter[] = ["term" => ["_id" => $_id]];

        return $this;
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
     * Set the query where clause
     * @param        $name
     * @param string $operator
     * @param null $value
     * @return $this
     */
    public function where($name, $operator = "=", $value = NULL)
    {

        if (is_callback_function($name)) {
            $name($this);
            return $this;
        }

        if (!$this->isOperator($operator)) {
            $value = $operator;
            $operator = "=";
        }

        if ($operator == "=") {

            if ($name == "_id") {
                return $this->_id($value);
            }

            $this->filter[] = ["term" => [$name => $value]];
        }

        if ($operator == ">") {
            $this->filter[] = ["range" => [$name => ["gt" => $value]]];
        }

        if ($operator == ">=") {
            $this->filter[] = ["range" => [$name => ["gte" => $value]]];
        }

        if ($operator == "<") {
            $this->filter[] = ["range" => [$name => ["lt" => $value]]];
        }

        if ($operator == "<=") {
            $this->filter[] = ["range" => [$name => ["lte" => $value]]];
        }

        if ($operator == "like") {
            $this->must[] = ["match" => [$name => $value]];
        }

        if ($operator == "exists") {
            $this->whereExists($name, $value);
        }

        return $this;
    }

    /**
     * Set the query inverse where clause
     * @param        $name
     * @param string $operator
     * @param null $value
     * @return $this
     */
    public function whereNot($name, $operator = "=", $value = NULL)
    {

        if (is_callback_function($name)) {
            $name($this);
            return $this;
        }

        if (!$this->isOperator($operator)) {
            $value = $operator;
            $operator = "=";
        }

        if ($operator == "=") {
            $this->must_not[] = ["term" => [$name => $value]];
        }

        if ($operator == ">") {
            $this->must_not[] = ["range" => [$name => ["gt" => $value]]];
        }

        if ($operator == ">=") {
            $this->must_not[] = ["range" => [$name => ["gte" => $value]]];
        }

        if ($operator == "<") {
            $this->must_not[] = ["range" => [$name => ["lt" => $value]]];
        }

        if ($operator == "<=") {
            $this->must_not[] = ["range" => [$name => ["lte" => $value]]];
        }

        if ($operator == "like") {
            $this->must_not[] = ["match" => [$name => $value]];
        }

        if ($operator == "exists") {
            $this->whereExists($name, !$value);
        }

        return $this;
    }

    /**
     * Set the query where between clause
     * @param $name
     * @param $first_value
     * @param $last_value
     * @return $this
     */
    public function whereBetween($name, $first_value, $last_value = null)
    {

        if (is_array($first_value) && count($first_value) == 2) {
            $last_value = $first_value[1];
            $first_value = $first_value[0];
        }

        $this->filter[] = ["range" => [$name => ["gte" => $first_value, "lte" => $last_value]]];

        return $this;
    }

    /**
     * Set the query where not between clause
     * @param $name
     * @param $first_value
     * @param $last_value
     * @return $this
     */
    public function whereNotBetween($name, $first_value, $last_value = null)
    {

        if (is_array($first_value) && count($first_value) == 2) {
            $last_value = $first_value[1];
            $first_value = $first_value[0];
        }

        $this->must_not[] = ["range" => [$name => ["gte" => $first_value, "lte" => $last_value]]];

        return $this;
    }

    /**
     * Set the query where in clause
     * @param       $name
     * @param array $value
     * @return $this
     */
    public function whereIn($name, $value = [])
    {

        if (is_callback_function($name)) {
            $name($this);
            return $this;
        }

        $this->filter[] = ["terms" => [$name => $value]];

        return $this;
    }

    /**
     * Set the query where not in clause
     * @param       $name
     * @param array $value
     * @return $this
     */
    public function whereNotIn($name, $value = [])
    {

        if (is_callback_function($name)) {
            $name($this);
            return $this;
        }

        $this->must_not[] = ["terms" => [$name => $value]];

        return $this;
    }


    /**
     * Set the query where exists clause
     * @param      $name
     * @param bool $exists
     * @return $this
     */
    public function whereExists($name, $exists = true)
    {

        if ($exists) {
            $this->must[] = ["exists" => ["field" => $name]];
        } else {
            $this->must_not[] = ["exists" => ["field" => $name]];
        }

        return $this;
    }

    /**
     * Add a condition to find documents which are some distance away from the given geo point.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/2.4/query-dsl-geo-distance-query.html
     *
     * @param        $name
     *   A name of the field.
     * @param mixed $value
     *   A starting geo point which can be represented by a string "lat,lon",
     *   an object {"lat": lat, "lon": lon} or an array [lon,lat].
     * @param string $distance
     *   A distance from the starting geo point. It can be for example "20km".
     *
     * @return $this
     */
    public function distance($name, $value, $distance)
    {

        if (is_callback_function($name)) {
            $name($this);
            return $this;
        }

        $this->filter[] = [
            "geo_distance" => [
                $name => $value,
                "distance" => $distance,
            ]
        ];

        return $this;
    }

    /**
     * Generate the query body
     * @return array
     */
    public function getBody()
    {

        $body = $this->body;

        if (count($this->_source)) {

            $_source = array_key_exists("_source", $body) ? $body["_source"] : [];

            $body["_source"] = array_unique(array_merge($_source, $this->_source));
        }

        if (count($this->must)) {
            $body["query"]["bool"]["must"] = $this->must;
        }

        if (count($this->must_not)) {
            $body["query"]["bool"]["must_not"] = $this->must_not;
        }

        if (count($this->filter)) {
            $body["query"]["bool"]["filter"] = $this->filter;
        }

        if (count($this->sort)) {

            $sortFields = array_key_exists("sort", $body) ? $body["sort"] : [];

            $body["sort"] = array_unique(array_merge($sortFields, $this->sort), SORT_REGULAR);

        }

        $this->body = $body;

        return $body;
    }

    /**
     * set the query body array
     * @param array $body
     * @return $this
     */
    function body($body = [])
    {

        $this->body = $body;

        return $this;
    }

    /**
     * @return array
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * @param array $source
     */
    public function setSource($source)
    {
        $this->_source = $source;
    }

    /**
     * @return array
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param array $filter
     */

    public function setFilter(array $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param array $filter
     */
    public function addFilter(array $filter)
    {
        $this->filter[] = $filter;
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
