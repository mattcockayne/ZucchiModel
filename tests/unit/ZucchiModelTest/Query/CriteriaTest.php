<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModelTest\Query;

use Codeception\Util\Stub;

use Zend\Db\Sql\Where;
use ZucchiModel\Query\Criteria;

/**
 * CriteriaTest
 *
 * Tests on the CriteriaTest Class.
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModelTest
 * @subpackage Query
 * @category
 */
class CriteriaTest extends \Codeception\TestCase\Test
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
     * Check getModel returns the correct string, which
     * was set by setModel to 'test_zucchimodel_user'.
     */
    public function testSetAndGetModelWithValidStringParam()
    {
        $dataOriginal = 'test_zucchimodel_user';

        $criteria = new Criteria();
        $criteria->setModel($dataOriginal);

        $data = $criteria->getModel();

        $this->assertSame($dataOriginal, $data);
    }

    /**
     * Check setModel throws an \InvalidArgumentException
     * when supplied with an empty string.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetModelWithInvalidEmptyStringParam()
    {
        $criteria = new Criteria();
        $criteria->setModel('');
    }

    /**
     * Check setModel throws an \InvalidArgumentException
     * when supplied with invalid object.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetModelWithInvalidObjectParam()
    {
        $criteria = new Criteria();
        $criteria->setModel(new \stdClass());
    }

    public function testSetModel()
    {

    }

    /**
     * Check getJoin returns the correct array, which
     * was set by setJoin.
     */
    public function testSetAndGetJoinWithValidArrayParam()
    {
        $dataOriginal = array(
            'name' => 'Roles',
            'model' => 'ZucchiModelTest\Adapter\TestAsset\Role',
            'type' => 'ManytoMany',
            'mappedKey' => 'id',
            'mappedBy' => 'User_id',
            'foreignKey' => 'id',
            'foreignBy' => 'Role_id',
            'referencedBy' => 'test_zucchimodel_user_role',
            'referencedOrder' => 'sort'
        );

        $criteria = new Criteria();
        $criteria->setJoin($dataOriginal);

        $data = $criteria->getJoin();

        $this->assertSame($dataOriginal, $data);
    }

    /**
     * Check setJoin allow a valid null param.
     */
    public function testSetJoinWithValidNullParam()
    {
        $criteria = new Criteria();
        $criteria->setJoin(
            array(
                'name' => 'Roles',
                'model' => 'ZucchiModelTest\Adapter\TestAsset\Role',
                'type' => 'ManytoMany',
                'mappedKey' => 'id',
                'mappedBy' => 'User_id',
                'foreignKey' => 'id',
                'foreignBy' => 'Role_id',
                'referencedBy' => 'test_zucchimodel_user_role',
                'referencedOrder' => 'sort'
            )
        );

        // Now set to null
        $criteria->setJoin(null);

        // Check get Join gives null
        $this->assertTrue(is_null($criteria->getJoin()), 'Set Join should allow null.');
    }

    /**
     * Check setJoin throws an \InvalidArgumentException
     * when supplied with invalid object.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetJoinWithInvalidObjectParam()
    {
        $criteria = new Criteria();
        $criteria->setJoin(new \stdClass());
    }

    /**
     * Check getWhere returns the correct \Zend\Db\Sql\Where
     * param, which was set by setWhere.
     */
    public function testSetAndGetWhereWithValidWhereParam()
    {
        $dataOriginal = new Where();
        $dataOriginal->like('email', '%rick%');

        $criteria = new Criteria();
        $criteria->setWhere($dataOriginal);

        $data = $criteria->getWhere();

        $this->assertSame($dataOriginal, $data);
    }

    /**
     * Check setWhere allow a valid null param.
     */
    public function testSetWhereWithInvalidNullParam()
    {
        $where = new Where();

        $criteria = new Criteria();
        $criteria->setWhere($where);

        // Now set to null
        $criteria->setWhere(null);

        // Check get Where gives null
        $this->assertTrue(is_null($criteria->getWhere()), 'Set Where should allow null.');
    }

    /**
     * Check setWhere throws an \InvalidArgumentException
     * when supplied with invalid object.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetWhereWithInvalidObjectParam()
    {
        $criteria = new Criteria();
        $criteria->setWhere(new \stdClass());
    }

    /**
     * Check getOffset returns the correct integer, which
     * was set by setLimit to 100.
     */
    public function testSetAndGetOffsetWithValidIntegerParam()
    {
        $dataOriginal = 100;

        $criteria = new Criteria();
        $criteria->setOffset($dataOriginal);

        $data = $criteria->getOffset();

        $this->assertSame($dataOriginal, $data);
    }

    /**
     * Check setOffset throws throws an \InvalidArgumentException
     * when supplied with invalid negative integer.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetOffsetWithInvalidNegativeIntegerParam()
    {
        $dataOriginal = -100;

        $criteria = new Criteria();
        $criteria->setOffset($dataOriginal);
    }

    /**
     * Check setOffset allow a valid null param.
     */
    public function testSetOffsetWithValidNullParam()
    {
        $dataOriginal = 100;

        $criteria = new Criteria();
        $criteria->setOffset($dataOriginal);

        // Now set to null
        $criteria->setOffset(null);

        // Check get Offset gives null
        $this->assertTrue(is_null($criteria->getOffset()), 'Set Offset should allow null.');
    }

    /**
     * Check setOffset throws an \InvalidArgumentException
     * when supplied with invalid object.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetOffsetWithInvalidObjectParam()
    {
        $criteria = new Criteria();
        $criteria->setOffset(new \stdClass());
    }

    /**
     * Check getLimit returns the correct integer, which
     * was set by setLimit to 100.
     */
    public function testSetAndGetLimitWithValidIntegerParam()
    {
        $dataOriginal = 100;

        $criteria = new Criteria();
        $criteria->setLimit($dataOriginal);

        $data = $criteria->getLimit();

        $this->assertSame($dataOriginal, $data);
    }

    /**
     * Check setLimit throws throws an \InvalidArgumentException
     * when supplied with invalid negative integer.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetLimitWithInvalidNegativeIntegerParam()
    {
        $dataOriginal = -100;

        $criteria = new Criteria();
        $criteria->setLimit($dataOriginal);
    }

    /**
     * Check setLimit allow a valid null param.
     */
    public function testSetLimitWithValidNullParam()
    {
        $dataOriginal = 100;

        $criteria = new Criteria();
        $criteria->setLimit($dataOriginal);

        // Now set to null
        $criteria->setLimit(null);

        // Check get Limit gives null
        $this->assertTrue(is_null($criteria->getLimit()), 'Set Limit should allow null.');
    }

    /**
     * Check setLimit throws an \InvalidArgumentException
     * when supplied with invalid object.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetLimitWithInvalidObjectParam()
    {
        $criteria = new Criteria();
        $criteria->setLimit(new \stdClass());
    }

    /**
     * Check getOrderBy returns the correct array, which
     * was set by setOrderBy.
     */
    public function testSetAndGetOrderByWithValidArrayParam()
    {
        $dataOriginal = array(
            'email ASC',
        );

        $criteria = new Criteria();
        $criteria->setOrderBy($dataOriginal);

        $data = $criteria->getOrderBy();

        $this->assertSame($dataOriginal, $data);
    }

    /**
     * Check setOrderBy allow a valid null param.
     */
    public function testSetOrderByWithValidNullParam()
    {
        $criteria = new Criteria();
        $criteria->setOrderBy(
            array(
                'email ASC',
            )
        );

        // Now set to null
        $criteria->setOrderBy(null);

        // Check get Order By gives null
        $this->assertTrue(is_null($criteria->getOrderBy()), 'Set OrderBy should allow null.');
    }

    /**
     * Check setOrderBy throws an \InvalidArgumentException
     * when supplied with invalid object.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetOrderByWithInvalidObjectParam()
    {
        $criteria = new Criteria();
        $criteria->setOrderBy(new \stdClass());
    }
}