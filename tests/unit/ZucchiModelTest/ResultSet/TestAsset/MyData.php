<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModelTest\ResultSet\TestAsset;

/**
 * Class MyData
 *
 * Test class for testing \IteratorAggregate
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModelTest
 * @subpackage ResultSet\TestAsset
 * @category
 */
class MyData implements \IteratorAggregate
{
    /**
     * Test Property
     *
     * @var array
     */
    public $propertyOne = array('Property one');

    /**
     * Test Property
     *
     * @var array
     */
    public $propertyTwo = array('Property two');

    /**
     * Test Property
     *
     * @var array
     */
    public $propertyThree = array('Property three');

    /**
     * Get Iterator
     *
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }
}
