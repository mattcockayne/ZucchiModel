<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModel\Query;

use Zend\Stdlib\AbstractOptions;
use Zend\Db\Sql\Where;

/**
 * Query Criteria
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage ModelManager
 * @category
 */
class Criteria extends AbstractOptions
{
    /**
     * Model name
     *
     * @var string
     */
    protected $model;

    /**
     * Where object
     *
     * @var \Zend\Db\Sql\Where|null
     */
    protected $where = null;

    /**
     * Integer offset for the query
     *
     * @var int|null
     */
    protected $offset = null;

    /**
     * Integer limit for the query
     *
     * @var int|null
     */
    protected $limit = null;

    /**
     * Order By
     *
     * @var array|null
     */
    protected $orderBy = null;

    /**
     * Additional Join
     *
     * @var array|null
     */
    protected $join = null;

    /**
     * Get Models - a single model.
     *
     * @return string|array|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get join - array of models to join.
     *
     * @return string|array|null
     */
    public function getJoin()
    {
        return $this->join;
    }

    /**
     * Get \Zend\Db\Sql\Where
     *
     * @return \Zend\Db\Sql\Where|null
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * Get integer offset if set.
     *
     * @return int|null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Get integer limit if set.
     *
     * @return int|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Get Order by if set.
     *
     * @return array|null
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Set Model with a given string.
     *
     * @param $model
     * @throws \InvalidArgumentException
     */
    public function setModel($model)
    {
        if (is_string($model) && $model != '') {
            $this->model = $model;
        } else {
            throw new \InvalidArgumentException(sprintf('Model must be a string and can not be blank. Given %s.', var_export($model, true)));
        }
    }

    /**
     * Set Additional Join with a given array.
     *
     * @param $join
     * @throws \InvalidArgumentException
     */
    public function setJoin($join)
    {
        if (is_array($join) || is_null($join)) {
            $this->join = $join;
        } else {
            throw new \InvalidArgumentException(sprintf('Join must be an array or null. Given %s.', var_export($join, true)));
        }
    }

    /**
     * Set Where with given \Zend\Db\Sql\Where.
     *
     * @param $where
     * @throws \InvalidArgumentException
     */
    public function setWhere($where)
    {
        if ($where instanceof Where || is_null($where)) {
            $this->where = $where;
        } else {
            throw new \InvalidArgumentException(sprintf('Where must be an instance of Zend\Db\Sql\Where or null. Given %s.', var_export($where, true)));
        }
    }

    /**
     * Set offest with given integer.
     *
     * @param $offset
     * @throws \InvalidArgumentException
     */
    public function setOffset($offset)
    {
        if ((is_integer($offset) && $offset >= 0) || is_null($offset)) {
            $this->offset = $offset;
        } else {
            throw new \InvalidArgumentException(sprintf('Offset must be a positive integer or null. Given %s.', var_export($offset, true)));
        }
    }

    /**
     * Set limit with given integer.
     *
     * @param $limit
     * @throws \InvalidArgumentException
     */
    public function setLimit($limit)
    {
        if ((is_integer($limit) && $limit > 0) || is_null($limit)) {
            $this->limit = $limit;
        } else {
            throw new \InvalidArgumentException(sprintf('Limit must be a positive integer or null. Given %s.', var_export($limit, true)));
        }
    }

    /**
     * Set order by with given array.
     *
     * @param $orderBy
     * @throws \InvalidArgumentException
     */
    public function setOrderBy($orderBy)
    {
        if (is_array($orderBy) || is_null($orderBy)) {
            $this->orderBy = $orderBy;
        } else {
            throw new \InvalidArgumentException(sprintf('Order By must be an array or null. Given %s.', var_export($orderBy, true)));
        }
    }
}
