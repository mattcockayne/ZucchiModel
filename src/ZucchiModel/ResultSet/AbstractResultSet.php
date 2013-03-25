<?php
/**
 * AbstractResultSet.php - (http://zucchi.co.uk) 
 *
 * @link      http://github.com/zucchi/{PROJECT_NAME} for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModel\ResultSet;

use Iterator;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use Zucchi\Event\EventProviderTrait;

/**
 * AbstractResultSet
 *
 * Description of class
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage ResultSet
 * @category 
 */
abstract class AbstractResultSet implements Iterator, ResultSetInterface
{
    use EventProviderTrait;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var null|int
     */
    protected $count = null;

    /**
     * @var null
     */
    protected $objectPrototype = null;

    /**
     * @var Iterator|IteratorAggregate|ResultInterface
     */
    protected $iterator = null;


    public function __construct(EventManager $eventManager, $objectPrototype = null)
    {
        $this->setEventManager($eventManager);
        $this->setObjectPrototype(($objectPrototype) ?: new ArrayObject);
    }

    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * Set the row object prototype
     *
     * @param  object $objectPrototype
     * @throws InvalidArgumentException
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
     * Set the data source for the result set
     *
     * @param  Iterator|IteratorAggregate|ResultInterface $dataSource
     * @return ResultSet
     * @throws \InvalidArgumentException
     */
    public function initialize($iterator)
    {
        switch (true) {
            case ($iterator instanceof ResultInterface):
            case ($iterator instanceof Iterator):
                $this->iterator = $iterator;
                break;
            case ($iterator instanceof IteratorAggregate):
                $this->iterator = $iterator->getIterator();
                break;
            default:
                throw new \InvalidArgumentException('DataSource provided does not implement ResultInterface, Iterator or IteratorAggregate');
                break;
        }

        return $this;
    }

    /**
     * Iterator: get current item
     *
     * @return object
     */
    public function current()
    {
        $object = clone $this->objectPrototype;
        $data = $this->iterator->current();

        if (!is_array($data)) {
            return false;
        }

        // Trigger Hydration events
        $event = new Event('preHydrate', $data);
        $this->getEventManager()->trigger($event);

        $event = new Event('hydrate', $object, array('data' => $data));
        $this->getEventManager()->trigger($event);

        $event = new Event('postHydrate', $object);
        $this->getEventManager()->trigger($event);

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
        $this->iterator->next();
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
        if ($this->iterator instanceof Iterator) {
            return $this->iterator->valid();
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
        if ($this->iterator instanceof Iterator) {
            $this->iterator->rewind();
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
        if ($this->count !== null) {
            return $this->count;
        }
        $this->count = count($this->iterator);
        return $this->count;
    }

}