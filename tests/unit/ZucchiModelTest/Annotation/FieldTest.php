<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModelTest\Annotation;

use Codeception\Util\Stub;

use \ZucchiModel\Annotation\Field;

/**
 * FieldTest
 *
 * Tests on the Field Class.
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModelTest
 * @subpackage Annotation
 * @category
 */
class FieldTest extends \Codeception\TestCase\Test
{
   /**
    * @var \CodeGuy
    */
    protected $codeGuy;

    /**
     * Setup Test
     */
    protected function _before()
    {
    }

    /**
     * Tear down Test
     * @todo: check with matt if these are needed.
     */
    protected function _after()
    {
    }

    /**
     * Check string is valid Field type.
     */
    public function testFieldWithValidTypeString()
    {
        $valueOriginal = 'string';
        $field = new Field(array('value' => $valueOriginal));
        $this->assertInstanceOf('\ZucchiModel\Annotation\Field', $field);
        $value = $field->getField();
        $this->assertSame($value, $valueOriginal);
    }

    /**
     * Check integer is valid Field type.
     */
    public function testFieldWithValidTypeInteger()
    {
        $valueOriginal = 'integer';
        $field = new Field(array('value' => $valueOriginal));
        $this->assertInstanceOf('\ZucchiModel\Annotation\Field', $field);
        $value = $field->getField();
        $this->assertSame($value, $valueOriginal);
    }

    /**
     * Check binary is valid Field type.
     */
    public function testFieldWithValidTypeBinary()
    {
        $valueOriginal = 'binary';
        $field = new Field(array('value' => $valueOriginal));
        $this->assertInstanceOf('\ZucchiModel\Annotation\Field', $field);
        $value = $field->getField();
        $this->assertSame($value, $valueOriginal);
    }

    /**
     * Check boolean is valid Field type.
     */
    public function testFieldWithValidTypeBoolean()
    {
        $valueOriginal = 'boolean';
        $field = new Field(array('value' => $valueOriginal));
        $this->assertInstanceOf('\ZucchiModel\Annotation\Field', $field);
        $value = $field->getField();
        $this->assertSame($value, $valueOriginal);
    }

    /**
     * Check float is valid Field type.
     */
    public function testFieldWithValidTypeFloat()
    {
        $valueOriginal = 'float';
        $field = new Field(array('value' => $valueOriginal));
        $this->assertInstanceOf('\ZucchiModel\Annotation\Field', $field);
        $value = $field->getField();
        $this->assertSame($value, $valueOriginal);
    }

    /**
     * Check date is valid Field type.
     */
    public function testFieldWithValidTypeDate()
    {
        $valueOriginal = 'date';
        $field = new Field(array('value' => $valueOriginal));
        $this->assertInstanceOf('\ZucchiModel\Annotation\Field', $field);
        $value = $field->getField();
        $this->assertSame($value, $valueOriginal);
    }

    /**
     * Check time is valid Field type.
     */
    public function testFieldWithValidTypeTime()
    {
        $valueOriginal = 'time';
        $field = new Field(array('value' => $valueOriginal));
        $this->assertInstanceOf('\ZucchiModel\Annotation\Field', $field);
        $value = $field->getField();
        $this->assertSame($value, $valueOriginal);
    }

    /**
     * Check datetime is valid Field type.
     */
    public function testFieldWithValidTypeDateTime()
    {
        $valueOriginal = 'datetime';
        $field = new Field(array('value' => $valueOriginal));
        $this->assertInstanceOf('\ZucchiModel\Annotation\Field', $field);
        $value = $field->getField();
        $this->assertSame($value, $valueOriginal);
    }

    /**
     * Check json_array is valid Field type.
     */
    public function testFieldWithValidTypeJsonArray()
    {
        $valueOriginal = 'json_array';
        $field = new Field(array('value' => $valueOriginal));
        $this->assertInstanceOf('\ZucchiModel\Annotation\Field', $field);
        $value = $field->getField();
        $this->assertSame($value, $valueOriginal);
    }

    /**
     * Check json_object is valid Field type.
     */
    public function testFieldWithValidTypeJsonObject()
    {
        $valueOriginal = 'json_object';
        $field = new Field(array('value' => $valueOriginal));
        $this->assertInstanceOf('\ZucchiModel\Annotation\Field', $field);
        $value = $field->getField();
        $this->assertSame($value, $valueOriginal);
    }

    /**
     * Check Field throws \RuntimeException when given
     * invalid array.
     *
     * @expectedException \RuntimeException
     */
    public function testFieldWithInvalidType()
    {
        $field = new Field(array('value' => 'unknown'));
    }

    /**
     * Check Field throws \Zend\Form\Exception\DomainException when given
     * invalid array.
     *
     * @expectedException \Zend\Form\Exception\DomainException
     */
    public function testFieldWithInvalidDataParam()
    {
        $field = new Field(array('email' => 'string'));
    }
}