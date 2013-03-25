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
 * @package ZucchiModel\ResultSet
 * @subpackage
 * @category
 */
class PaginatedResultSet implements Iterator, Countable
{
    /**
     * @var ModelManager
     */
    protected $modelManager;


    /**
     * Critera for lookup
     * @var Criteria
     */
    protected $criteria;


    /**
     * Actual position of record in complete recordset
     * @var int
     */
    protected $position = 0;

    /**
     * @var HydratingRowset
     */
    protected $resultSet;

    /**
     * @var Maximum Page size
     */
    protected $pageSize;

    /**
     * Current Page of results
     * @var int
     */
    protected $page = 0;

    protected $count = false;

    protected $limit = 0;


    protected $offset = 0;

    /**
     * constructor
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
     * initialise the results
     */
    public function initialize()
    {
        $this->page = 0;
        $this->position = $this->offset;

        $criteria = $this->getCriteria();

        if ($this->limit < $this->getPageSize()) {
            $criteria->setLimit($this->limit);
        } else {
            $criteria->setLimit($this->getPageSize());
        }

        $criteria->setOffset($this->offset);

        $this->resultSet = $this->getModelManager()->findAll($criteria);

    }

    public function current()
    {
        return $this->resultSet->current();
    }

    public function next()
    {
        $this->resultSet->next();
        $this->position++;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        if (!$this->resultSet->valid()) {

            // assume end of page
            $criteria = $this->getCriteria();

            $criteria->setOffset($this->offset + ($this->pageSize * $this->page+1));

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

    public function rewind()
    {
        $this->initialize();
    }


    public function count()
    {
        if ($this->count !== false) {
            return $this->count;
        }

        $this->count = $this->getModelManager()->countAll(clone $this->getCriteria());

        return $this->count;

    }




    public function setModelManager($modelManager)
    {
        $this->modelManager = $modelManager;
        return $this;
    }

    public function getModelManager()
    {
        return $this->modelManager;
    }

    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
        return $this;
    }

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

    public function getCriteria()
    {
        return $this->criteria;
    }

    public function getPageSize()
    {
        return $this->pageSize;
    }

}