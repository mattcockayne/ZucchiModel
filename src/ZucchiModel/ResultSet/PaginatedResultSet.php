<?php
/**
 * PaginatedResultSet.php - (http://zucchi.co.uk) 
 *
 * @link      http://github.com/zucchi/{PROJECT_NAME} for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModel\ResultSet;

use \Iterator;
use \Countable;
use ZucchiModel\ModelManager;
use ZucchiModel\Query\Criteria;

/**
 * PaginatedResultSet
 *
 * Description of class
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModel\ResultSet
 * @subpackage
 * @category
 */
class PaginatedResultSet implements Iterator, Countable
{
    /**
     * Reference to ModelManager
     *
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * The original supplied Critera for lookup
     *
     * @var Criteria
     */
    protected $criteria;

    /**
     * Actual position of current result in complete result set
     *
     * @var int
     */
    protected $position = 0;

    /**
     * @var HydratingRowset
     */
    protected $resultSet;

    /**
     * Maximum Page size
     *
     * @var int
     */
    protected $pageSize;

    /**
     * Current Page of results
     *
     * @var int
     */
    protected $page = 0;

    /**
     * Total count of selected results
     *
     * @var bool
     */
    protected $count = false;

    /**
     * Custom Limit 0 = Not set
     *
     * @var int
     */
    protected $limit = 0;

    /**
     * Current offset of page
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * Constructor
     *
     * @param ModelManager $modelManager
     * @param Criteria $criteria
     * @param $pageSize
     */
    public function __construct(ModelManager $modelManager, Criteria $criteria, $pageSize = 10)
    {
        $this->setModelManager($modelManager);
        $this->setCriteria($criteria);
        $this->setPageSize($pageSize);
        $this->position = 0;
    }

    /**
     * Initialise the results
     *
     * @return void
     */
    public function initialize()
    {
        $this->page = 0;
        $this->position = $this->offset;

        $criteria = $this->getCriteria();

        if ($this->limit != 0 && $this->limit < $this->getPageSize()) {
            $criteria->setLimit($this->limit);
        } else {
            $criteria->setLimit($this->getPageSize());
        }

        $criteria->setOffset($this->offset);

        $this->resultSet = $this->getModelManager()->findAll($criteria);

    }

    /**
     * Get current result from result set
     *
     * @return mixed
     */
    public function current()
    {
        return $this->resultSet->current();
    }

    /**
     * Move to the next result in the
     * result set
     *
     * @return void
     */
    public function next()
    {
        $this->resultSet->next();
        $this->position++;
    }

    /**
     * Get current key
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        if (!$this->resultSet->valid()) {

            // assume end of page
            $criteria = $this->getCriteria();

            $criteria->setOffset($this->offset + ($this->pageSize * ($this->page+1)));

            $resultSet = $this->getModelManager()->findAll($criteria);
            if ($resultSet->count() == 0) {
                return false;
            } else {
                $this->resultSet = $resultSet;
                $this->page++;
            }
        }

        return true;
    }

    /**
     * Rewind result set to the beginning
     */
    public function rewind()
    {
        $this->initialize();
    }

    /**
     * Get count of result set
     *
     * @return bool|int
     */
    public function count()
    {
        if ($this->count !== false) {
            return $this->count;
        }

        $this->count = $this->getModelManager()->countAll(clone $this->getCriteria());

        return $this->count;
    }

    /**
     * Set Model Manager
     *
     * @param $modelManager
     * @return $this
     */
    public function setModelManager($modelManager)
    {
        $this->modelManager = $modelManager;
        return $this;
    }

    /**
     * Get Model Manager
     *
     * @return ModelManager
     */
    public function getModelManager()
    {
        return $this->modelManager;
    }

    /**
     * Set Page Size
     *
     * @param $pageSize
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
        return $this;
    }

    /**
     * Set Criteria
     *
     * @param $criteria
     * @return $this
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;

        if ($limit = $criteria->getLimit()) {
            $this->limit = $limit;
        }

        if ($offset = $criteria->getOffset()) {
            $this->offset = $offset;
        }

        return $this;
    }

    /**
     * Get Criteria
     *
     * @return Criteria
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Get Page Size
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }
}
