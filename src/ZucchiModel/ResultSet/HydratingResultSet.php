<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZucchiModel\ResultSet;

use Iterator;
use IteratorAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;
use Zucchi\Event\EventProviderTrait;

/**
 * Hydrating ResultSet
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage ResultSet
 * @category
 */
class HydratingResultSet implements Iterator, ResultSetInterface
{
    use EventProviderTrait;

    /**
     * Current position of the iterator.
     *
     * @var int
     */
    protected $position = 0;

    /**
     * Prototype object to return for each found
     * record.
     *
     * @var null
     */
    protected $objectPrototype = null;

    /**
     * Iterator for records.
     *
     * @var Iterator|IteratorAggregate|ResultSetInterface
     */
    protected $iterator = null;

    /**
     * Constructor
     *
     * @param EventManagerInterface $eventManager
     * @param null $objectPrototype
     */
    public function __construct(EventManagerInterface $eventManager, $objectPrototype = null)
    {
        $this->setEventManager($eventManager);
        $this->setObjectPrototype(($objectPrototype) ?: new ArrayObject);
    }

    /**
     * Set the data source for the result set.
     *
     * @param  Iterator|IteratorAggregate|ResultSetInterface $iterator
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function initialize($iterator)
    {
        switch (true) {
            case ($iterator instanceof ResultSetInterface):
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
     * Get Iterator
     *
     * @return Iterator|IteratorAggregate|null|ResultSetInterface
     */
    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * Get the row object prototype
     *
     * @return mixed|null
     */
    public function getObjectPrototype()
    {
        return $this->objectPrototype;
    }

    /**
     * Set the row object prototype
     *
     * @param object $objectPrototype
     * @return $this
     * @throws \InvalidArgumentException
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
     * Return the current element.
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return object|bool if no iterator set return false
     */
    public function current()
    {
        if (!$this->iterator instanceof Iterator || !is_object($this->getObjectPrototype())) {
            return false;
        }

        $object = clone $this->objectPrototype;
        $data = $this->iterator->current();

        if (!is_array($data)) {
            return false;
        }

        // Trigger Hydration events
        $event = new Event('preHydrate', $object, array(
            'data' => $data
        ));
        $this->getEventManager()->trigger($event);

        $event->setName('hydrate');
        $this->getEventManager()->trigger($event);

        $event->setName('postHydrate');
        $this->getEventManager()->trigger($event);

        return $object;
    }

    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @throws \RuntimeException
     */
    public function next()
    {
        if (!$this->iterator instanceof Iterator) {
            throw new \RuntimeException(sprintf('Iterator is not instance of \Iterator. Given %s.', var_export($this->iterator, true)));
        }

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
     * Returns count of number of records.
     *
     * @return bool|int if no iterator set return false
     */
    public function count()
    {
        if ($this->iterator instanceof Iterator) {
            return count($this->iterator);
        }

        return false;
    }
}
