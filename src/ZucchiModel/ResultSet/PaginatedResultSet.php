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
 * Paginated Result Set gets one Page Size worth of results at a time.
 * However from outside this class acts like it contains all the results.
 * This means only the current page of results is in memory at any one time,
 * thus allowing for batch processing on huge Result Sets.
 * Note it uses HydratingResultSet internally to hold the current
 * page of results.
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage ResultSet
 * @category
 */
class PaginatedResultSet implements Iterator, Countable
{
    /**
     * Reference to ModelManager.
     *
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * Clone of original Criteria, but is used to
     * select each page. Thus becoming different to
     * the original. Starts as null until valid is called.
     *
     * @var Criteria
     */
    protected $paginateCriteria = null;

    /**
     * The original supplied Criteria for lookup.
     * This used to reset back to the start of the
     * Result Set with the rewind command.
     *
     * @var Criteria
     */
    protected $originalCriteria;

    /**
     * Actual position of current result in complete result set.
     *
     * @var int
     */
    protected $position = 0;

    /**
     * The internal Result Set, contains one Page Size worth of
     * results.
     *
     * @var HydratingResultSet
     */
    protected $resultSet;

    /**
     * Maximum Page size.
     *
     * @var int
     */
    protected $pageSize;

    /**
     * Current Page of results.
     *
     * @var int
     */
    protected $page = 0;

    /**
     * Total count of selected results.
     *
     * @var bool
     */
    protected $count = false;

    /**
     * Constructor used to set ModelManager, Original Criteria and
     * Page Size.
     *
     * @param ModelManager $modelManager
     * @param Criteria $criteria
     * @param int $pageSize
     * @throws \InvalidArgumentException if pageSize is not a positive integer.
     */
    public function __construct(ModelManager $modelManager, Criteria $criteria, $pageSize = 10)
    {
        $this->setModelManager($modelManager);

        // Can't use setter as it will cause unnecessary reset.
        $this->originalCriteria = $criteria;

        // Check pageSize is positive int
        if (!is_int($pageSize)) {
            throw new \InvalidArgumentException(sprintf('Set Page Size expects parameter to be an Integer. Given %s.', var_export($pageSize, true)));
        }

        if ($pageSize < 1) {
            throw new \InvalidArgumentException(sprintf('Page Size must be greater than 0. Given %s.', var_export($pageSize, true)));
        }

        // All ok, set it.
        // Can't use setter as it will cause unnecessary reset.
        $this->pageSize = $pageSize;
        $this->reset();
    }

    /**
     * Set Original Criteria, which in turn causes reset().
     *
     * @param Criteria $criteria
     * @return $this
     */
    public function setCriteria(Criteria $criteria)
    {
        $this->originalCriteria = $criteria;
        $this->reset();

        return $this;
    }

    /**
     * Get Paginated Criteria.
     *
     * @return Criteria
     */
    public function getCriteria()
    {
        return $this->paginateCriteria;
    }

    /**
     * Set Model Manager.
     *
     * @param ModelManager $modelManager
     * @return $this
     */
    public function setModelManager(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
        return $this;
    }

    /**
     * Get Model Manager.
     *
     * @return ModelManager
     */
    public function getModelManager()
    {
        return $this->modelManager;
    }

    /**
     * Set Page Size, which in turn causes reset().
     *
     * @param int $pageSize
     * @return $this
     * @throws \InvalidArgumentException if pageSize is not a positive integer.
     */
    public function setPageSize($pageSize)
    {
        // Check pageSize is positive int
        if (!is_int($pageSize)) {
            throw new \InvalidArgumentException(sprintf('Set Page Size expects parameter to be an Integer. Given %s.', var_export($pageSize, true)));
        }

        if ($pageSize < 1) {
            throw new \InvalidArgumentException(sprintf('Page Size must be greater than 0. Given %s.', var_export($pageSize, true)));
        }

        // All ok, set it.
        $this->pageSize = $pageSize;

        // Reset with new pageSize.
        $this->reset();

        return $this;
    }

    /**
     * Get Page Size.
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Get current result from result set.
     *
     * @return mixed
     */
    public function current()
    {
        return $this->resultSet->current();
    }

    /**
     * Move to the next result in the result set.
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->resultSet->next();
        $this->position++;
    }

    /**
     * Return the key of the current element.
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid.
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        // If current result set is valid.
        if (!$this->resultSet->valid()) {
            // Assume end of page.
            $criteria = $this->getCriteria();

            // Create new offset.
            $newOffset = $criteria->getOffset() + $this->pageSize;

            // If limit is set check new offset is not beyond it
            // And Set limit. Else return false, no more results.
            // Else no limit is set then set limit to pageSize.
            if ($limit = $this->originalCriteria->getLimit()) {
                if ($newOffset > $limit) {
                    return false;
                }
                if ($newOffset + $criteria->getLimit() > $limit) {
                    $criteria->setLimit($limit - $newOffset);
                }
            } else {
                $criteria->setLimit($this->pageSize);
            }

            // Set new offset.
            $criteria->setOffset($newOffset);

            // Find results.
            $resultSet = $this->getModelManager()->findAll($criteria);

            // If no results are returned, return false.
            if ($resultSet->count() == 0) {
                return false;
            } else {
                $this->resultSet = $resultSet;
                $this->page++;
            }
        }

        // Else current result set is still valid.
        return true;
    }

    /**
     * Reset this Result Set, used by rewind(), setPageSize
     * and setCriteria.
     */
    protected function reset()
    {
        // Reset internal counters etc.
        $this->position = 0;
        $this->page = 0;
        $this->count = false;

        // Clone original criteria to override current paginated criteria.
        $this->paginateCriteria = clone $this->originalCriteria;

        // If limit is set in current paginated criteria.
        if ($limit = $this->getCriteria()->getLimit()) {
            // Set limit or Page Size, which ever is smaller.
            if ($limit > $this->getPageSize()) {
                $this->getCriteria()->setLimit($this->getPageSize());
            }
            // If offset is not set, set to 0.
            if (!($offset = $this->getCriteria()->getOffset())) {
                $this->getCriteria()->setOffset(0);
            }
        } else {
            // Else no limit, set to pageSize and reset offset.
            $this->getCriteria()->setLimit($this->pageSize);
            // If offset is not set, set to 0.
            if (!($offset = $this->getCriteria()->getOffset())) {
                $this->getCriteria()->setOffset(0);
            }
        }

        // Set count.
        $this->count = $this->getModelManager()->countAll(clone $this->getCriteria());

        // Call findAll to return a HydratingResultSet. The first page.
        $this->resultSet = $this->getModelManager()->findAll($this->getCriteria());
    }

    /**
     * Rewind this to the first element.
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->reset();
    }

    /**
     * Get count of result set.
     *
     * @return bool|int if not set return false
     */
    public function count()
    {
        return $this->count;
    }
}
