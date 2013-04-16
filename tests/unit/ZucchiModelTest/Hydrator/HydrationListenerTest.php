<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModelTest\Hydrator;

use Codeception\Util\Stub;

use Zend\Db\Adapter\Adapter as ZendDbAdapter;
use Zend\EventManager\Event;
use Zend\Json\Json;

use ZucchiModel\ModelManager;
use ZucchiModel\Adapter\ZendDb;
use ZucchiModel\Hydrator\HydrationListener;
use ZucchiModel\Metadata;

/**
 * HydrationListenerTest
 *
 * Tests on the HydrationListener Class.
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModelTest
 * @subpackage Hydrator
 * @category
 */
class HydrationListenerTest extends \Codeception\TestCase\Test
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
     * Check HydrationListener can be instantiated when supplied with valid
     * Model Manager.
     */
    public function testHydrationListenerWithValidModelManager()
    {
        $hydrationListener = new HydrationListener($this->modelManager);
        $this->assertInstanceOf('\ZucchiModel\Hydrator\HydrationListener', $hydrationListener);
    }

    /**
     * Check HydrationListener throws an \ErrorException when supplied with
     * invalid Model Manager.
     *
     * @expectedException \ErrorException
     */
    public function testHydrationListenerWithInvalidModelManager()
    {
        $hydrationListener = new HydrationListener('string monkey');
    }

    /**
     * Check getModelManager when supplied with valid
     * Model Manager.
     */
    public function testGetModelManagerWithValidModelManager()
    {
        $hydrationListener = new HydrationListener($this->modelManager);
        $this->assertSame($this->modelManager, $hydrationListener->getModelManager());
    }

    /**
     * Check setModelManager when supplied with valid
     * Model Manager.
     */
    public function testSetModelManagerWithValidModelManager()
    {
        $hydrationListener = new HydrationListener($this->modelManager);
        $hydrationListener->setModelManager($this->modelManager);
        $this->assertSame($this->modelManager, $hydrationListener->getModelManager());
    }

    /**
     * Check setModelManager throws an \ErrorException when supplied with
     * invalid Model Manager.
     *
     * @expectedException \ErrorException
     */
    public function testSetModelManagerWithInvalidModelManager()
    {
        $hydrationListener = new HydrationListener($this->modelManager);
        $hydrationListener->setModelManager('string monkey');
    }

    /**
     * Check HydrationListener Events can be attached and detached from Event
     * Manager.
     */
    public function testDetachHydrationListenerWithValidEventManager()
    {
        $em = $this->modelManager->getEventManager();
        $hydrationListener = new HydrationListener($this->modelManager);
        $hydrationListener->attach($em);
        $hydrationListener->detach($em);
    }

    /**
     * Check castDateTime returns a valid DateTime when supplied
     * with give datetime string.
     */
    public function testCastDateTimeWithValidDateTimeParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        // Hyphens are used to treat this as a UK date.
        $dataOriginal = '12-06-1990 05:06:07';

        $event = new Event();
        $event->setName('castDateTime');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'datetime');

        $data = $hydrationListener->castDateTime($event);
        $this->assertInstanceOf('DateTime', $data);
        $this->assertSame($dataOriginal, $data->format('d-m-Y h:i:s'));
    }

    /**
     * Check castDateTime returns correct value when
     * given a type that is not lowercase.
     */
    public function testCastDateTimeWithMixCaseType()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        // Hyphens are used to treat this as a UK date.
        $dataOriginal = '12-06-1990 05:06:07';

        $event = new Event();
        $event->setName('castDateTime');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'DaTeTiMe');

        $data = $hydrationListener->castDateTime($event);
        $this->assertInstanceOf('DateTime', $data);
        $this->assertSame($dataOriginal, $data->format('d-m-Y h:i:s'));
    }

    /**
     * Check castDateTime throws an \ErrorException
     * when supplied with \stdClass for value instead of a
     * string.
     *
     * @expectedException \ErrorException
     */
    public function testCastDateTimeWithInvalidValueParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = new \stdClass();

        $event = new Event();
        $event->setName('castDateTime');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'datetime');

        $data = $hydrationListener->castDateTime($event);
    }

    /**
     * Check castDateTime throws an \RuntimeException
     * when supplied with 50 as month for value.
     *
     * @expectedException \RuntimeException
     */
    public function testCastDateTimeWithMalformedValueParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        // Hyphens are used to treat this as a UK date.
        $dataOriginal = '12-50-1990 05:06:07';

        $event = new Event();
        $event->setName('castDateTime');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'datetime');

        $data = $hydrationListener->castDateTime($event);
    }

    /**
     * Check castDateTime throws an \UnexpectedValueException
     * when supplied with no type.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testCastDateTimeWithEmptyTypeParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        // Hyphens are used to treat this as a UK date.
        $dataOriginal = '12-06-1990 05:06:07';

        $event = new Event();
        $event->setName('castDateTime');
        $event->setTarget($dataOriginal);
        $event->setParam('type', '');

        $data = $hydrationListener->castDateTime($event);
    }

    /**
     * Check castDateTime throws an \UnexpectedValueException
     * when supplied with invalid type.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testCastDateTimeWithInvalidTypeParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        // Hyphens are used to treat this as a UK date.
        $dataOriginal = '12-06-1990 05:06:07';

        $event = new Event();
        $event->setName('castDateTime');
        $event->setTarget($dataOriginal);
        $event->setParam('type', new \stdClass());

        $data = $hydrationListener->castDateTime($event);
    }

    /**
     * Check castDateTime returns origin value when
     * given a type that is not 'datetime'.
     */
    public function testCastDateTimeWithStringType()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = 'test';

        $event = new Event();
        $event->setName('castDateTime');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'string');

        $data = $hydrationListener->castDateTime($event);
        $this->assertSame($dataOriginal, $data);
    }

    /**
     * Check castJson returns a valid Json Array when supplied
     * with give valid value.
     */
    public function testCastJsonWithValidJsonArrayTypeParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = array('forename' => 'Billington');

        $event = new Event();
        $event->setName('castJson');
        $event->setTarget(Json::encode($dataOriginal));
        $event->setParam('type', 'json_array');

        $data = $hydrationListener->castJson($event);
        $this->assertSame($dataOriginal, $data);
    }

    /**
     * Check castJson returns a valid Json Array when supplied
     * with give valid value.
     */
    public function testCastJsonWithValidJsonObjectTypeParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = new \stdClass();
        $dataOriginal->forename = 'Billington';

        $event = new Event();
        $event->setName('castJson');
        $event->setTarget(Json::encode($dataOriginal));
        $event->setParam('type', 'json_object');

        $data = $hydrationListener->castJson($event);
        $this->assertInstanceOf('\stdClass', $data);
        $this->assertSame($dataOriginal->forename, $data->forename);
    }

    /**
     * Check castJson returns correct value when
     * given a type that is not lowercase.
     */
    public function testCastJsonWithMixCaseType()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = array('forename' => 'Billington');

        $event = new Event();
        $event->setName('castJson');
        $event->setTarget(Json::encode($dataOriginal));
        $event->setParam('type', 'JsOn_Array');

        $data = $hydrationListener->castJson($event);
        $this->assertSame($dataOriginal, $data);
    }

    /**
     * Check castJson throws an \ErrorException
     * when supplied with \stdClass for value instead of a
     * string.
     *
     * @expectedException \ErrorException
     */
    public function testCastJsonWithInvalidValueParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = new \stdClass();

        $event = new Event();
        $event->setName('castJson');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'json_array');

        $data = $hydrationListener->castJson($event);
    }

    /**
     * Check castJson throws an \RuntimeException
     * when supplied with malformed Json string.
     *
     * @expectedException \RuntimeException
     */
    public function testCastJsonWithMalformedValueParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = '{"forename" : }';

        $event = new Event();
        $event->setName('castJson');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'json_array');

        $data = $hydrationListener->castJson($event);
    }

    /**
     * Check castJson throws an \UnexpectedValueException
     * when supplied with no type.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testCastJsonWithEmptyTypeParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = array('forename' => 'Billington');

        $event = new Event();
        $event->setName('castJson');
        $event->setTarget(Json::encode($dataOriginal));
        $event->setParam('type', '');

        $data = $hydrationListener->castJson($event);
    }

    /**
     * Check castJson throws an \UnexpectedValueException
     * when supplied with invalid type.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testCastJsonWithInvalidTypeParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = array('forename' => 'Billington');

        $event = new Event();
        $event->setName('castJson');
        $event->setTarget(Json::encode($dataOriginal));
        $event->setParam('type', new \stdClass());

        $data = $hydrationListener->castJson($event);
    }

    /**
     * Check castJson returns origin value when
     * given a type that is not 'json_array' or 'json_object'.
     */
    public function testCastJsonWithStringType()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = 'test';

        $event = new Event();
        $event->setName('castJson');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'string');

        $data = $hydrationListener->castJson($event);
        $this->assertSame($dataOriginal, $data);
    }

    /**
     * Check castScalar returns a valid Boolean when supplied
     * with given boolean.
     */
    public function testCastScalarWithValidTrueBooleanParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = 1;

        $event = new Event();
        $event->setName('castScalar');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'boolean');

        $data = $hydrationListener->castScalar($event);
        $this->assertTrue(is_bool($data), 'Given "1" should return boolean variable.');
        $this->assertTrue((true === $data), 'Given "1" should return boolean true.');
    }

    /**
     * Check castScalar returns a valid Boolean when supplied
     * with given boolean.
     */
    public function testCastScalarWithValidFalseBooleanParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = false;

        $event = new Event();
        $event->setName('castScalar');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'boolean');

        $data = $hydrationListener->castScalar($event);
        $this->assertTrue(is_bool($data), 'Given "false" should return boolean variable.');
        $this->assertTrue((false === $data), 'Given "false" should return boolean false.');
    }

    /**
     * Check castScalar returns a valid Float when supplied
     * with given float.
     */
    public function testCastScalarWithValidFloatParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = 10.25;

        $event = new Event();
        $event->setName('castScalar');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'float');

        $data = $hydrationListener->castScalar($event);
        $this->assertTrue(is_float($data), 'Given "10.25" should return float variable.');
        $this->assertSame($dataOriginal, $data);
    }

    /**
     * Check castScalar returns a valid Integer when supplied
     * with give integer.
     */
    public function testCastScalarWithValidIntegerParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = 210;

        $event = new Event();
        $event->setName('castScalar');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'integer');

        $data = $hydrationListener->castScalar($event);
        $this->assertTrue(is_int($data), 'Given "210" should return integer variable.');
        $this->assertSame($dataOriginal, $data);
    }

    /**
     * Check castScalar returns a valid String when supplied
     * with given string.
     */
    public function testCastScalarWithValidStringParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = 'string monkey';

        $event = new Event();
        $event->setName('castScalar');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'string');

        $data = $hydrationListener->castScalar($event);
        $this->assertTrue(is_string($data), 'Given "string monkey" should return string variable.');
        $this->assertSame($dataOriginal, $data);
    }

    /**
     * Check castScalar throws an \ErrorException
     * when supplied with \stdClass for value instead of a
     * string and given boolean type.
     *
     * @expectedException \ErrorException
     */
    public function testCastScalarWithInvalidValueAndBooleanTypeParams()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = new \stdClass();

        $event = new Event();
        $event->setName('castScalar');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'boolean');

        $data = $hydrationListener->castScalar($event);
    }

    /**
     * Check castScalar throws an \ErrorException
     * when supplied with \stdClass for value instead of a
     * string and given float type.
     *
     * @expectedException \ErrorException
     */
    public function testCastScalarWithInvalidValueAndFloatTypeParams()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = new \stdClass();

        $event = new Event();
        $event->setName('castScalar');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'float');

        $data = $hydrationListener->castScalar($event);
    }

    /**
     * Check castScalar throws an \ErrorException
     * when supplied with \stdClass for value instead of a
     * string and given integer type.
     *
     * @expectedException \ErrorException
     */
    public function testCastScalarWithInvalidValueAndIntegerTypeParams()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = new \stdClass();

        $event = new Event();
        $event->setName('castScalar');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'integer');

        $data = $hydrationListener->castScalar($event);
    }

    /**
     * Check castScalar throws an \ErrorException
     * when supplied with \stdClass for value instead of a
     * string and given string type.
     *
     * @expectedException \ErrorException
     */
    public function testCastScalarWithInvalidValueAndStringTypeParams()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = new \stdClass();

        $event = new Event();
        $event->setName('castScalar');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'string');

        $data = $hydrationListener->castScalar($event);
    }

    /**
     * Check castScalar throws an \UnexpectedValueException
     * when supplied with no type.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testCastScalarWithEmptyTypeParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = 1;

        $event = new Event();
        $event->setName('castScalar');
        $event->setTarget($dataOriginal);
        $event->setParam('type', '');

        $data = $hydrationListener->castScalar($event);
    }

    /**
     * Check castScalar throws an \UnexpectedValueException
     * when supplied with invalid type.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testCastScalarWithInvalidTypeParam()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        $dataOriginal = 1;

        $event = new Event();
        $event->setName('castScalar');
        $event->setTarget($dataOriginal);
        $event->setParam('type', new \stdClass());

        $data = $hydrationListener->castScalar($event);
    }

    /**
     * Check castScalar returns origin value when
     * given a type that is not 'boolean', 'float', 'integer',
     * or 'string'.
     */
    public function testCastScalarWithDateTimeType()
    {
        $hydrationListener = new HydrationListener($this->modelManager);

        // Hyphens are used to treat this as a UK date.
        $dataOriginal = '12-06-1990 05:06:07';

        $event = new Event();
        $event->setName('castScalar');
        $event->setTarget($dataOriginal);
        $event->setParam('type', 'datetime');

        $data = $hydrationListener->castScalar($event);
        $this->assertSame($dataOriginal, $data);
    }
}