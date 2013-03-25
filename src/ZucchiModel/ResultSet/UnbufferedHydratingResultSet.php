<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModel\ResultSet;

use Iterator;
use ArrayObject;
use Zend\Stdlib\Hydrator\ArraySerializable;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Unbuffered Hydrating ResultSet
 *
 * Use this class when a result set might exhaust
 * all available memory. If result set is small, use
 * Buffered Hydrating Result Set for increased performance.
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage ResultSet
 * @category
 */
class UnbufferedHydratingResultSet implements Iterator, ResultSetInterface
{
    /**
     * @var Iterator|IteratorAggregate|ResultInterface
     */
    protected $dataSource = null;

    /**
     * @var HydratorInterface
     */
    protected $hydrator = null;

    /**
     * @var null
     */
    protected $objectPrototype = null;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * Constructor
     *
     * @param  null|HydratorInterface $hydrator
     * @param  null|object $objectPrototype
     */
    public function __construct(HydratorInterface $hydrator = null, $objectPrototype = null)
    {
        $this->setHydrator(($hydrator) ?: new ArraySerializable);
        $this->setObjectPrototype(($objectPrototype) ?: new ArrayObject);
    }

    /**
     * Get the data source used to create the result set
     *
     * @return null|Iterator
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * Set the row object prototype
     *
     * @param  object $objectPrototype
     * @throws \InvalidArgumentException
     * @return ResultSet
     */
    public function setObjectPrototype($objectPrototype)
    {
        if (!is_object($objectPrototype)) {
            throw new \InvalidArgumentException(
                'An object must be set as the object prototype, a ' . gettype($objectPrototype) . ' was provided.'
            );
        }
        $this->objectPrototype = $objectPrototype;
        return $this;
    }

    /**
     * Set the hydrator to use for each row object
     *
     * @param HydratorInterface $hydrator
     * @return HydratingResultSet
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * Get the hydrator to use for each row object
     *
     * @return HydratorInterface
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * Set the data source for the result set
     *
     * @param  Iterator|IteratorAggregate|ResultInterface $dataSource
     * @return ResultSet
     * @throws \InvalidArgumentException
     */
    public function initialize($dataSource)
    {
        switch (true) {
            case ($dataSource instanceof ResultInterface):
            case ($dataSource instanceof Iterator):
            $this->dataSource = $dataSource;
            break;
            case ($dataSource instanceof IteratorAggregate):
                $this->dataSource = $dataSource->getIterator();
                break;
            default:
                throw new \InvalidArgumentException('DataSource provided does not implement ResultInterface, Iterator or IteratorAggregate');
                break;
        }

        return $this;
    }

    /**
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        $data = $this->dataSource->current();
        $object = is_array($data) ? $this->hydrator->hydrate($data, clone $this->objectPrototype) : false;

        return $object;
    }

    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->dataSource->next();
        $this->position++;
    }

    /**
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        if ($this->dataSource instanceof Iterator) {
            return $this->dataSource->valid();
        }

        return false;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        if ($this->dataSource instanceof Iterator) {
            $this->dataSource->rewind();
        }
        $this->position = 0;
    }

    /**
     * Always returns false as count can not be performed
     * on Unbuffered Result Sets.
     *
     * @return bool
     */
    public function count()
    {
        trigger_error('Count is not available with an Unbuffered Result Set', E_USER_NOTICE);

        return false;
    }
}