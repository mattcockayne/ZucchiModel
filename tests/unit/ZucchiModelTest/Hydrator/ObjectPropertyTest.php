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
use ZucchiModel\Hydrator\ObjectProperty;
use ZucchiModelTest\Hydrator\TestAsset\User;

/**
 * ObjectPropertyTest
 *
 * Test for ObjectProperty Class.
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModelTest
 * @subpackage Hydrator
 * @category
 */
class ObjectPropertyTest extends \Codeception\TestCase\Test
{
   /**
    * @var \CodeGuy
    */
    protected $codeGuy;

    /**
     * Setup Tests
     */
    protected function _before()
    {
    }

    /**
     * Tear down Tests
     */
    protected function _after()
    {
    }

    /**
     * Check hydrate can populate supplied object with
     * supplied data.
     */
    public function testHydrateWithValidObjectParam()
    {
        $dataOriginal = array(
            'forename' => 'John',
            'surname' => 'Cantrell',
            'email' => 'john@me.co.uk'
        );

        $objectProperty = new ObjectProperty();
        $data = $objectProperty->hydrate($dataOriginal, new User());
        $this->assertInstanceOf('\ZucchiModelTest\Hydrator\TestAsset\User', $data);
        $this->assertSame($dataOriginal['forename'], $data->forename);
        $this->assertSame($dataOriginal['surname'], $data->surname);
        $this->assertSame($dataOriginal['email'], $data->email);
    }

    /**
     * Check hydrate can populate supplied object with
     * supplied data, included unknown property.
     */
    public function testHydrateWithValidObjectParamWithExtraUnmappableProperties()
    {
        $dataOriginal = array(
            'forename' => 'John',
            'surname' => 'Cantrell',
            'email' => 'john@me.co.uk',
            'User_id' => 1,
            'description' => 'Middle Aged.'
        );

        $objectProperty = new ObjectProperty();
        $data = $objectProperty->hydrate($dataOriginal, new User());

        $this->assertInstanceOf('\ZucchiModelTest\Hydrator\TestAsset\User', $data);

        $this->assertSame($dataOriginal['forename'], $data->forename);
        $this->assertSame($dataOriginal['surname'], $data->surname);
        $this->assertSame($dataOriginal['email'], $data->email);

        $this->assertTrue(property_exists($data, 'getProperty'), 'Get Property should exist on Hydrated Object.');
        $getProperty = $data->getProperty;

        $this->assertSame($dataOriginal['User_id'], $getProperty('User_id'));
        $this->assertSame($dataOriginal['description'], $getProperty('description'));
    }

    /**
     * Check hydrate throws an \Zend\Stdlib\Exception\BadMethodCallException
     * when given a string instead of an object.
     *
     * @expectedException \Zend\Stdlib\Exception\BadMethodCallException
     */
    public function testHydrateWithInvalidStringParam()
    {
        $dataOriginal = array(
            'forename' => 'John',
            'surname' => 'Cantrell',
            'email' => 'john@me.co.uk'
        );

        $objectProperty = new ObjectProperty();
        $data = $objectProperty->hydrate($dataOriginal, 'string monkey');
    }

    /**
     * Check getProperty works on hyrdated object and returns
     * value set in setProperty.
     */
    public function testSetAndGetPropertyOnHydratedObject()
    {
        $dataOriginal = array(
            'forename' => 'John',
            'surname' => 'Cantrell',
            'email' => 'john@me.co.uk'
        );

        $objectProperty = new ObjectProperty();
        $data = $objectProperty->hydrate($dataOriginal, new User());

        $this->assertInstanceOf('\ZucchiModelTest\Hydrator\TestAsset\User', $data);
        $this->assertTrue(property_exists($data, 'setProperty'), 'Set Property should exist on Hydrated Object.');

        $setProperty = $data->setProperty;

        $this->assertTrue(property_exists($data, 'getProperty'), 'Get Property should exist on Hydrated Object.');
        $getProperty = $data->getProperty;

        $setProperty('User_id', 1);
        $value = $getProperty('User_id');
        $this->assertSame(1, $value);

        $setProperty('forename', 'Billington');
        $value = $getProperty('forename');
        $this->assertSame('Billington', $value);

        $this->assertSame($dataOriginal['surname'], $data->surname);
        $this->assertSame($dataOriginal['email'], $data->email);
    }

    /**
     * Check getProperty throws a \RuntimeException when
     * the property does not exist and unmappedProperties has been
     * removed from the Hydrated object.
     *
     * @expectedException \RuntimeException
     */
    public function testGetPropertyThrowsRuntimeExceptionWithNoUnmappedProperties()
    {
        $dataOriginal = array(
            'forename' => 'John',
            'surname' => 'Cantrell',
            'email' => 'john@me.co.uk'
        );

        $objectProperty = new ObjectProperty();
        $data = $objectProperty->hydrate($dataOriginal, new User());

        unset($data->unmappedProperties);

        $this->assertTrue(property_exists($data, 'getProperty'), 'Get Property should exist on Hydrated Object.');
        $getProperty = $data->getProperty;

        $getProperty('User_id');
    }

    /**
     * Check getProperty throws a \RuntimeException when
     * the property does not exist.
     *
     * @expectedException \RuntimeException
     */
    public function testGetPropertyThrowsRuntimeExceptionWithUnknownProperty()
    {
        $dataOriginal = array(
            'forename' => 'John',
            'surname' => 'Cantrell',
            'email' => 'john@me.co.uk'
        );

        $objectProperty = new ObjectProperty();
        $data = $objectProperty->hydrate($dataOriginal, new User());

        $this->assertTrue(property_exists($data, 'getProperty'), 'Get Property should exist on Hydrated Object.');
        $getProperty = $data->getProperty;

        $getProperty('User_id');
    }
}