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

use \ZucchiModel\Annotation\Relationship;

/**
 * RelationshipTest
 *
 * Tests on the Relationship Class.
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModelTest
 * @subpackage Annotation
 * @category
 */
class RelationshipTest extends \Codeception\TestCase\Test
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
     */
    protected function _after()
    {
    }

    /**
     * Check valid toOne Relationship can be parsed
     * correctly.
     */
    public function testRelationshipWithValidToOne()
    {
        $valueOriginal = array(
            'value' => array(
                'name' => 'Role',
                'model' => 'ZucchiModelTest\Annotation\TestAsset\Role',
                'type' => 'toOne',
                'mappedKey' => 'id',
                'mappedBy' => 'User_id'
            )
        );
        $relationship = new Relationship($valueOriginal);
        $this->assertInstanceOf('\ZucchiModel\Annotation\Relationship', $relationship);
        $value = $relationship->getRelationship();
        $this->assertSame($value, $valueOriginal['value']);
    }

    /**
     * Check valid toMany Relationship can be parsed
     * correctly.
     */
    public function testRelationshipWithValidToMany()
    {
        $valueOriginal = array(
            'value' => array(
                'name' => 'Role',
                'model' => 'ZucchiModelTest\Annotation\TestAsset\Role',
                'type' => 'toMany',
                'mappedKey' => 'User_id',
                'mappedBy' => 'id'
            )
        );
        $relationship = new Relationship($valueOriginal);
        $this->assertInstanceOf('\ZucchiModel\Annotation\Relationship', $relationship);
        $value = $relationship->getRelationship();
        $this->assertSame($value, $valueOriginal['value']);
    }

    /**
     * Check valid ManytoMany Relationship can be parsed
     * correctly.
     */
    public function testRelationshipWithValidManyToMany()
    {
        $valueOriginal = array(
            'value' => array(
                'name' => 'Role',
                'model' => 'ZucchiModelTest\Annotation\TestAsset\Role',
                'type' => 'ManytoMany',
                'mappedKey' => 'id',
                'mappedBy' => 'User_id',
                'foreignKey' => 'id',
                'foreignBy' => 'Role_id',
                'referencedBy' => 'moduledev_user_role'
            )
        );
        $relationship = new Relationship($valueOriginal);
        $this->assertInstanceOf('\ZucchiModel\Annotation\Relationship', $relationship);
        $value = $relationship->getRelationship();
        $this->assertSame($value, $valueOriginal['value']);
    }

    /**
     * Check valid ManyToMany Relationship can be parsed
     * correctly with additional opitional sort column.
     */
    public function testRelationshipWithValidManyToManyAndOptionalSortColumn()
    {
        $valueOriginal = array(
            'value' => array(
                'name' => 'Role',
                'model' => 'ZucchiModelTest\Annotation\TestAsset\Role',
                'type' => 'toOne',
                'mappedKey' => 'id',
                'mappedBy' => 'User_id',
                'foreignKey' => 'id',
                'foreignBy' => 'Role_id',
                'referencedBy' => 'moduledev_user_role',
                'referencedOrder' => 'sort'
            )
        );
        $relationship = new Relationship($valueOriginal);
        $this->assertInstanceOf('\ZucchiModel\Annotation\Relationship', $relationship);
        $value = $relationship->getRelationship();
        $this->assertSame($value, $valueOriginal['value']);
    }

    /**
     * Check Relationship throw a \RuntimeExcpetion when supplied
     * with missing type.
     *
     * @expectedException \RuntimeException
     */
    public function testRelationshipWithMissingRelationshipType()
    {
        $valueOriginal = array(
            'value' => array(
                'name' => 'Role',
                'model' => 'ZucchiModelTest\Annotation\TestAsset\Role',
                'mappedKey' => 'id',
                'mappedBy' => 'User_id',
                'foreignKey' => 'id',
                'foreignBy' => 'Role_id',
                'referencedBy' => 'moduledev_user_role',
                'referencedOrder' => 'sort'
            )
        );
        $relationship = new Relationship($valueOriginal);
    }

    /**
     * Check Relationship throw a \RuntimeExcpetion when supplied
     * with missing type value.
     *
     * @expectedException \RuntimeException
     */
    public function testRelationshipWithMissingRelationshipTypeValue()
    {
        $valueOriginal = array(
            'value' => array(
                'name' => 'Role',
                'model' => 'ZucchiModelTest\Annotation\TestAsset\Role',
                'type' => '',
                'mappedKey' => 'id',
                'mappedBy' => 'User_id',
                'foreignKey' => 'id',
                'foreignBy' => 'Role_id',
                'referencedBy' => 'moduledev_user_role',
                'referencedOrder' => 'sort'
            )
        );
        $relationship = new Relationship($valueOriginal);
    }

    /**
     * Check Relationship throw a \RuntimeExcpetion when supplied
     * with invalid type.
     *
     * @expectedException \RuntimeException
     */
    public function testRelationshipWithInvalidRelationshipType()
    {
        $valueOriginal = array(
            'value' => array(
                'name' => 'Role',
                'model' => 'ZucchiModelTest\Annotation\TestAsset\Role',
                'type' => 'unknown',
                'mappedKey' => 'id',
                'mappedBy' => 'User_id',
                'foreignKey' => 'id',
                'foreignBy' => 'Role_id',
                'referencedBy' => 'moduledev_user_role',
                'referencedOrder' => 'sort'
            )
        );
        $relationship = new Relationship($valueOriginal);
    }

    /**
     * Check Relationship toOne throw a \RuntimeExcpetion when supplied
     * with missing required key.
     *
     * @expectedException \RuntimeException
     */
    public function testRelationshipWithMissingRequiredKeysForToOne()
    {
        $valueOriginal = array(
            'value' => array(
                'name' => 'Role',
                'model' => 'ZucchiModelTest\Annotation\TestAsset\Role',
                'type' => 'toOne',
                'mappedBy' => 'User_id'
            )
        );
        $relationship = new Relationship($valueOriginal);
    }

    /**
     * Check Relationship toMany throw a \RuntimeExcpetion when supplied
     * with missing required key.
     *
     * @expectedException \RuntimeException
     */
    public function testRelationshipWithMissingRequiredKeysForToMany()
    {
        $valueOriginal = array(
            'value' => array(
                'name' => 'Role',
                'model' => 'ZucchiModelTest\Annotation\TestAsset\Role',
                'type' => 'toMany',
                'mappedKey' => 'User_id',
            )
        );
        $relationship = new Relationship($valueOriginal);
    }

    /**
     * Check Relationship ManytoMany throw a \RuntimeExcpetion when supplied
     * with missing required key.
     *
     * @expectedException \RuntimeException
     */
    public function testRelationshipWithMissingRequiredKeysForManyToMany()
    {
        $valueOriginal = array(
            'value' => array(
                'name' => 'Role',
                'model' => 'ZucchiModelTest\Annotation\TestAsset\Role',
                'type' => 'ManytoMany',
                'mappedKey' => 'id',
                'mappedBy' => 'User_id',
                'foreignBy' => 'Role_id',
                'referencedBy' => 'moduledev_user_role',
                'referencedOrder' => 'sort'
            )
        );
        $relationship = new Relationship($valueOriginal);
    }

    /**
     * Check Relationship toOne throw a \RuntimeExcpetion when supplied
     * with extra invalid key.
     *
     * @expectedException \RuntimeException
     */
    public function testRelationshipWithExtraInvalidKeysForToOne()
    {
        $valueOriginal = array(
            'value' => array(
                'name' => 'Role',
                'model' => 'ZucchiModelTest\Annotation\TestAsset\Role',
                'type' => 'toOne',
                'mappedBy' => 'User_id',
                'mappedKey' => 'id',
                'unknown' => 'test'
            )
        );
        $relationship = new Relationship($valueOriginal);
    }
}