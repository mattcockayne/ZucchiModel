<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModelTest\Behaviour;

use Codeception\Util\Stub;

use Zend\Db\Adapter\Adapter as ZendDbAdapter;

use ZucchiModel\ModelManager;
use ZucchiModel\Adapter\ZendDb;
use ZucchiModel\Behaviour\BehaviourListener;
use ZucchiModel\Metadata;

/**
 * MetadataListenerTest
 *
 * Tests on the MetadataListener Class.
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModelTest
 * @subpackage Behaviour
 * @category
 */
class BehaviourListenerTest extends \Codeception\TestCase\Test
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
     * Check BehaviourListener can be instantiated when supplied with valid
     * Model Manager.
     */
    public function testBehaviourListenerWithValidModelManager()
    {
        $behaviourListener = new BehaviourListener($this->modelManager);
        $this->assertInstanceOf('\ZucchiModel\Behaviour\BehaviourListener', $behaviourListener);
    }

    /**
     * Check BehaviourListener throws an \ErrorException when supplied with
     * invalid Model Manager.
     *
     * @expectedException \ErrorException
     */
    public function testBehaviourListenerWithInvalidModelManager()
    {
        $behaviourListener = new BehaviourListener('string monkey');
    }

    /**
     * Check getModelManager when supplied with valid
     * Model Manager.
     */
    public function testGetModelManagerWithValidModelManager()
    {
        $behaviourListener = new BehaviourListener($this->modelManager);
        $this->assertSame($this->modelManager, $behaviourListener->getModelManager());
    }

    /**
     * Check setModelManager when supplied with valid
     * Model Manager.
     */
    public function testSetModelManagerWithValidModelManager()
    {
        $behaviourListener = new BehaviourListener($this->modelManager);
        $behaviourListener->setModelManager($this->modelManager);
        $this->assertSame($this->modelManager, $behaviourListener->getModelManager());
    }

    /**
     * Check setModelManager throws an \ErrorException when supplied with
     * invalid Model Manager.
     *
     * @expectedException \ErrorException
     */
    public function testSetModelManagerWithInvalidModelManager()
    {
        $behaviourListener = new BehaviourListener($this->modelManager);
        $behaviourListener->setModelManager('string monkey');
    }

    /**
     * Check BehaviourListener Events can be attached and detached from Event
     * Manager.
     */
    public function testDetachBehaviourListenerWithValidEventManager()
    {
        $em = $this->modelManager->getEventManager();
        $behaviourListener = new BehaviourListener($this->modelManager);
        $behaviourListener->attach($em);
        $behaviourListener->detach($em);
    }
}