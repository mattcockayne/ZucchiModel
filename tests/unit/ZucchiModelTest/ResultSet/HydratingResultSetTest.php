<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModelTest\ResultSet;

use Codeception\Util\Stub;

use Zend\Db\Adapter\Adapter as ZendDbAdapter;

use Zend\Db\Sql\Where;
use ZucchiModel\ModelManager;
use ZucchiModel\Adapter\ZendDb;
use ZucchiModel\Metadata;
use ZucchiModel\Query\Criteria;
use ZucchiModel\ResultSet\HydratingResultSet;

use ZucchiModelTest\ResultSet\TestAsset\Customer;
use ZucchiModelTest\ResultSet\TestAsset\MyData;
use ZucchiModelTest\ResultSet\TestAsset\User;

/**
 * HydratingResultSetTest
 *
 * Tests on the HydratingResultSet Class.
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModelTest
 * @subpackage ResultSet
 * @category
 */
class HydratingResultSetTest extends \Codeception\TestCase\Test
{
   /**
    * @var \CodeGuy
    */
    protected $codeGuy;

    /**
     * Actually connection via Zend Db
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $zendDbAdapter;

    /**
     * ZucchiModel wrapper for Zend Db adapter
     *
     * @var \ZucchiModel\Adapter\ZendDb
     */
    protected $adapter;

    /**
     * ModelManager
     *
     * @var \ZucchiModel\ModelManager
     */
    protected $modelManager;

    /**
     * Setup Zend Db and Zucchi Model Manger for Tests
     */
    protected function _before()
    {
        $this->zendDbAdapter = new ZendDbAdapter(array(
            'driver' => 'Mysqli',
            'database' => 'test_zucchimodel',
            'username' => 'root',
            'password' => 'password',
            'port' => '3306',
            'host' => '127.0.0.1',
            'charset' => 'UTF8',
            'profiler' => true
        ));
        $this->adapter = new ZendDb($this->zendDbAdapter);
        $this->modelManager = new ModelManager($this->adapter);
    }

    /**
     * Tear down Test
     */
    protected function _after()
    {
    }

    /**
     * Check HydratingResultSet can be instantiated when supplied with valid
     * Event Manager and User.
     */
    public function testHydrationResultSetWithValidParam()
    {
        $hydratingResultSet = new HydratingResultSet($this->modelManager->getEventManager(), new User());
        $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $hydratingResultSet);
    }

    /**
     * Check HydratingResultSet throws an \ErrorException when supplied with
     * invalid params.
     *
     * @expectedException \ErrorException
     */
    public function testHydratingResultSetWithInvalidParams()
    {
        $hydratingResultSet = new HydratingResultSet('string monkey', 'string monkey');
    }


    /**
     * Check getIterator returns instance of Iterator after
     * findAll is called.
     */
    public function testGetIteratorOnValidHydratingResultSet()
    {
        $results = $this->modelManager->findAll(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\ResultSet\TestAsset\User'
                )
            )
        );

        $iterator = $results->getIterator();
        $this->assertInstanceOf('\Iterator', $iterator);
    }

    /**
     * Check getIterator returns null when HydratingResultSet
     * is first initialised.
     */
    public function testGetIteratorOnInitialisation()
    {
        $hydratingResultSet = new HydratingResultSet($this->modelManager->getEventManager(), new User());
        $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $hydratingResultSet);

        $iterator = $hydratingResultSet->getIterator();
        $this->assertTrue(is_null($iterator), 'Get Iterator should return null when HydratingResultSet is first instantiated.');
    }

    /**
     * Check setObjectPrototype with valid object prototype.
     */
    public function testSetAndGetObjectPrototypeWithValidPrototypeParam()
    {
        $hydratingResultSet = new HydratingResultSet($this->modelManager->getEventManager(), new User());
        $this->assertInstanceOf('\ZucchiModelTest\ResultSet\TestAsset\User', $hydratingResultSet->getObjectPrototype());

        $hydratingResultSet->setObjectPrototype(new Customer());
        $this->assertInstanceOf('\ZucchiModelTest\ResultSet\TestAsset\Customer', $hydratingResultSet->getObjectPrototype());
    }

    /**
     * Check setObjectPrototype throws an \InvalidArgumentException
     * with invalid object prototype.
     *
     * @expectedException InvalidArgumentException
     */
    public function testSetObjectPrototypeWithInvalidStringPrototypeParam()
    {
        $hydratingResultSet = new HydratingResultSet($this->modelManager->getEventManager(), new User());
        $this->assertInstanceOf('\ZucchiModelTest\ResultSet\TestAsset\User', $hydratingResultSet->getObjectPrototype());

        $hydratingResultSet->setObjectPrototype('string monkey');
    }

    /**
     * Check initialize with valid iterator.
     */
    public function testInitializeWithValidIteratorParam()
    {
        $hydratingResultSet = new HydratingResultSet($this->modelManager->getEventManager(), new User());
        $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $hydratingResultSet);

        $testArray = array('test','test2','test3');
        $iterator = new \ArrayIterator($testArray);
        $iterator = new \IteratorIterator($iterator);
        $hydratingResultSet->initialize($iterator);
    }

    /**
     * Check initialize with valid iterator aggregate.
     */
    public function testInitializeWithValidIteratorAggregateParam()
    {
        $hydratingResultSet = new HydratingResultSet($this->modelManager->getEventManager(), new User());
        $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $hydratingResultSet);

        $iterator = new myData();
        $hydratingResultSet->initialize($iterator);
        $hydratingResultSet->rewind();
        $current = $hydratingResultSet->current();
        $this->assertInstanceOf('\ZucchiModelTest\ResultSet\TestAsset\User', $current);
    }

    /**
     * Check initialize throws \InvalidArgumentException
     * when supplied with invalid iterator.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInitializeWithInvalidIteratorParam()
    {
        $hydratingResultSet = new HydratingResultSet($this->modelManager->getEventManager(), new User());
        $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $hydratingResultSet);

        $hydratingResultSet->initialize('string monkey');
    }

    /**
     * Check current returns false when first initialised.
     */
    public function testCurrentOnInitialisation()
    {
        $hydratingResultSet = new HydratingResultSet($this->modelManager->getEventManager(), new User());
        $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $hydratingResultSet);

        $hydratingResultSet->rewind();
        $this->assertFalse($hydratingResultSet->current(), 'Current should return false when HydratingResultSet is first instantiated.');
    }

    /**
     * Check current returns false when iterator has
     * no data to iterate over.
     */
    public function testCurrentWithEmptyHydratingResultSet()
    {
        $where = new Where();
        $where->like('email', 'test');

        $results = $this->modelManager->findAll(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\ResultSet\TestAsset\User',
                    'where' => $where
                )
            )
        );

        $results->rewind();
        $this->assertFalse($results->current(), 'Current should return false when HydratingResultSet is first instantiated.');
    }

    /**
     * Check next returns the next result.
     */
    public function testNextOnValidUserHydratingResultSet()
    {
        $results = $this->modelManager->findAll(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\ResultSet\TestAsset\User'
                )
            )
        );

        $valueOld = null;
        $results->rewind();

        while ($results->valid()) {
            $value = $results->current();
            $this->assertNotSame($valueOld, $value);

            $valueOld = $value;
            $results->next();
        }
    }

    /**
     * Check next throw a \RuntimeException when iterator
     * has not been set.
     *
     * @expectedException \RuntimeException
     */
    public function testNextOnNullIterator()
    {
        $hydratingResultSet = new HydratingResultSet($this->modelManager->getEventManager(), new User());

        $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $hydratingResultSet);
        $hydratingResultSet->next();
    }

    /**
     * Check rewind returns the first result, after looping
     * through all the results.
     */
    public function testRewindOnValidUserHydratingResultSet()
    {
        $results = $this->modelManager->findAll(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\ResultSet\TestAsset\User'
                )
            )
        );

        $first = true;
        $firstValue = null;
        $results->rewind();

        while ($results->valid()) {
            if ($first) {
                $first = false;
                $firstValue = $results->current();
            }

            $results->next();
        }

        $results->rewind();

        $value = $results->current();
        $this->assertSame($firstValue->id, $value->id);
    }

    /**
     * Check key returns the right count.
     */
    public function testKeyOnValidUserHydratingResultSet()
    {
        $results = $this->modelManager->findAll(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\ResultSet\TestAsset\User'
                )
            )
        );

        $runningKey = 0;
        $results->rewind();

        while ($results->valid()) {
            $key = $results->key();
            $this->assertSame($runningKey, $key);

            $runningKey++;
            $results->next();
        }
    }

    /**
     * Check valid returns false when first initialised.
     */
    public function testValidOnInitialisation()
    {
        $hydratingResultSet = new HydratingResultSet($this->modelManager->getEventManager(), new User());
        $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $hydratingResultSet);

        $hydratingResultSet->rewind();
        $this->assertFalse($hydratingResultSet->valid(), 'Valid should return false when HydratingResultSet is first instantiated.');
    }

    /**
     * Check count returns correct value when
     * supplied valid User ResultSet.
     */
    public function testCountOnValidUserHydratingResultSet()
    {
        $results = $this->modelManager->findAll(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\ResultSet\TestAsset\User'
                )
            )
        );

        $count = $results->count();
        $this->assertSame(3, $count);
    }

    /**
     * Check count returns false when first initialised.
     */
    public function testCountOnInitialisation()
    {
        $hydratingResultSet = new HydratingResultSet($this->modelManager->getEventManager(), new User());
        $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $hydratingResultSet);

        $hydratingResultSet->rewind();
        $this->assertFalse($hydratingResultSet->count(), 'Valid should return false when HydratingResultSet is first instantiated.');
    }
}