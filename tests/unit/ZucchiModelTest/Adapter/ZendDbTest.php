<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModelTest\Adapter;

use Codeception\Util\Stub;

use Zend\Db\Adapter\Adapter as ZendDbAdapter;
use Zend\Db\Sql\Where;

use ZucchiModel\Model\Manager;
use ZucchiModel\Adapter\ZendDb;
use ZucchiModel\Query\Criteria;

/**
 * ZendDbTest
 *
 * Tests on the ZendDb Class.
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModelTest
 * @subpackage Adapter
 * @category
 */
class ZendDbTest extends \Codeception\TestCase\Test
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
     * Model\Manager
     *
     * @var \ZucchiModel\Model\Manager
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
        $this->modelManager = new Manager($this->adapter);
    }

    /**
     * Tear down
     */
    protected function _after()
    {
    }

    /**
     * Check if a \ZucchiModel\Adapter\ZendDb object can be
     * instantiated.
     */
    public function testCanInstantiateWithValidAdapterParam()
    {
        $adapter = new ZendDb($this->zendDbAdapter);
        $this->assertInstanceOf('\ZucchiModel\Adapter\ZendDb', $adapter);
    }

    /**
     * Check instantiating \ZucchiModel\Adapter\ZendDb throws
     * an \ErrorException when supplied with a string of garbage.
     *
     * @expectedException \ErrorException
     */
    public function testCanInstantiateWithInvalidAdapterParam()
    {
        $adapter = new ZendDb('string monkey');
    }

    /**
     * Check getDataSource returns an \Zend\Db\Adapter\Adapter
     * object when it has been correctly instantiated.
     */
    public function testGetDataSource()
    {
        $dataSource = $this->adapter->getDataSource();
        $this->assertInstanceOf('\Zend\Db\Adapter\Adapter', $dataSource);
    }

    /**
     * Check setDataSource sets correctly.
     */
    public function testSetDataSourceWithValidDataSourceParam()
    {
        $dataSource = $this->adapter->getDataSource();
        $this->adapter->setDataSource($dataSource);
    }

    /**
     * Check setDataSource throws an \ErrorException
     * when supplied with a string of garbage.
     *
     * @expectedException \ErrorException
     */
    public function testSetDataSourceWithInvalidDataSourceParam()
    {
        $this->adapter->setDataSource('string monkey');
    }

    /**
     * Check supplying valid param to getMetaData returns instances
     * of \ZucchiModel\Metadata\Adapter\ZendDb.
     */
    public function testGetMetaDataWithValidTableParam()
    {
        $metadata = $this->adapter->getMetaData(array('test_zucchimodel_user'));
        $this->assertInstanceOf('\ZucchiModel\Metadata\Adapter\ZendDb', $metadata);
    }

    /**
     * Check getMetaData throws an \Exception when supplied with
     * an unknown target.
     *
     * @expectedException \Exception
     */
    public function testGetMetaDataWithUnknownTableParam()
    {
        $metadata = $this->adapter->getMetaData(array('doesnot exist'));
    }

    /**
     * Check getMetaData throws an \Exception
     * when supplied with a string of garbage.
     *
     * @expectedException \ErrorException
     */
    public function testGetMetaDataWithInvalidArrayParam()
    {
        $metadata = $this->adapter->getMetaData('string monkey');
    }

    /**
     * Check getMetaData throws an \InvalidArgumentException
     * when supplied with an empty array of targets.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testGetMetaDataWithEmptyArrayParam()
    {
        $metadata = $this->adapter->getMetaData(array());
    }

    /**
     * Check buildQuery returns an instance of \Zend\Db\Sql\Select
     * when supplied with correct params.
     */
    public function testBuildQueryWithValidCriteriaAndMetaDataContainerParams()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\Customer');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $criteria = new Criteria(array('model' => __NAMESPACE__ . '\TestAsset\Customer'));
        $select = $this->adapter->buildQuery($criteria, $metaDataContainer);
        $this->assertInstanceOf('\Zend\Db\Sql\Select', $select);
    }

    /**
     * Check buildQuery returns an instance of \Zend\Db\Sql\Select
     * when supplied with correct params and a valid \Zend\Db\Sql\Where.
     */
    public function testBuildQueryWithValidWhere()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\Customer');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $where = new Where();
        $where->like('email', '%rick%');
        $criteria = new Criteria(
            array(
                'model' => __NAMESPACE__ . '\TestAsset\Customer',
                'where' => $where
            )
        );
        $select = $this->adapter->buildQuery($criteria, $metaDataContainer);
        $this->assertInstanceOf('\Zend\Db\Sql\Select', $select);
    }

    /**
     * Check buildQuery returns an instance of \Zend\Db\Sql\Select
     * when supplied with correct params and a valid \Zend\Db\Sql\Where
     * with a valid offset and limit.
     */
    public function testBuildQueryWithValidOffsetAndLimit()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\Customer');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $criteria = new Criteria(
            array(
                'model' => __NAMESPACE__ . '\TestAsset\Customer',
                'offset' => 1,
                'limit' => 1
            )
        );
        $select = $this->adapter->buildQuery($criteria, $metaDataContainer);
        $this->assertInstanceOf('\Zend\Db\Sql\Select', $select);
    }

    /**
     * Check buildQuery throws \ErrorException when supplied with
     * string params of garbage.
     *
     * @expectedException \ErrorException
     */
    public function testBuildQueryWithInvalidCriteriaAndMetaDataContainerParams()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\Customer');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $select = $this->adapter->buildQuery('string monkey', 'string monkey');
    }

    /**
     * Check buildCountQuery returns an instance of \Zend\Db\Sql\Select
     * when supplied with correct params and a valid \Zend\Db\Sql\Where
     * with a valid offset and limit.
     */
    public function testBuildCountQueryWithValidCriteriaAndMetaDataContainerParams()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\Customer');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $criteria = new Criteria(array('model' => __NAMESPACE__ . '\TestAsset\Customer'));
        $select = $this->adapter->buildCountQuery($criteria, $metaDataContainer);
        $this->assertInstanceOf('\Zend\Db\Sql\Select', $select);
    }

    /**
     * Check buildCountQuery throws \ErrorException when supplied with
     * string params of garbage.
     *
     * @expectedException \ErrorException
     */
    public function testBuildCountQueryWithInvalidCriteriaAndMetaDataContainerParams()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\Customer');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $select = $this->adapter->buildCountQuery('string monkey', 'string monkey');
    }

    /**
     * Check execute returns instance of \Zend\Db\Adapter\Driver\ResultInterface
     * when supplied with valid \Zend\Db\Sql\Select on \ZucchiModelTest\Adapter\TestAsset\Customer.
     */
    public function testExecuteWithValidCustomerSelect()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\Customer');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $criteria = new Criteria(array('model' => __NAMESPACE__ . '\TestAsset\Customer'));
        $select = $this->adapter->buildQuery($criteria, $metaDataContainer);
        $this->assertInstanceOf('\Zend\Db\Sql\Select', $select);

        $results = $this->adapter->execute($select);
        $this->assertInstanceOf('\Zend\Db\Adapter\Driver\ResultInterface', $results);
    }

    /**
     * Check execute throws \ErrorException when supplied with
     * string param of garbage.
     *
     * @expectedException \ErrorException
     */
    public function testExecuteWithInvalidQueryParam()
    {
        $adapter = new ZendDb($this->zendAdapter);
        $results = $adapter->execute('string monkey');
    }

    /**
     * Check execute throws \Zend\Db\Adapter\Exception\InvalidQueryException
     * when supplied with unknown column in \Zend\Db\Sql\Where.
     *
     * @expectedException \Zend\Db\Adapter\Exception\InvalidQueryException
     */
    public function testExecuteWithInvalidWhere()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\Customer');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $where = new Where();
        $where->like('string monkey', '%rick%');
        $criteria = new \ZucchiModel\Query\Criteria(
            array(
                'model' => __NAMESPACE__ . '\TestAsset\Customer',
                'where' => $where,
            )
        );
        $select = $this->adapter->buildQuery($criteria, $metaDataContainer);
        $results = $this->adapter->execute($select);
    }

    /**
     * Check execute throws \Zend\Db\Adapter\Exception\RuntimeException
     * when accessing count on an unbuffered result set.
     *
     * @expectedException \Zend\Db\Adapter\Exception\RuntimeException
     */
    public function testExecuteAccessingCountWhenUnbufferedWithValidUser()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\User');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $where = new Where();
        $where->like('email', 'this is not in the db');
        $criteria = new Criteria(
            array(
                'model' => __NAMESPACE__ . '\TestAsset\User',
                'where' => $where,
            )
        );

        $query = $this->adapter->buildCountQuery($criteria, $metaDataContainer);
        $results = $this->adapter->execute($query);
        $this->assertInstanceOf('\Zend\Db\Adapter\Driver\ResultInterface', $results);
        $count = $results->count();
    }

    /**
     * Check execute returns instance of \Zend\Db\Adapter\Driver\ResultInterface
     * when supplied with valid \Zend\Db\Sql\Select on \ZucchiModelTest\Adapter\TestAsset\User.
     * Also check the returning count is set.
     */
    public function testExecuteNoResultsWithValidUser()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\User');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $where = new Where();
        $where->like('email', 'this is not in the db');
        $criteria = new Criteria(
            array(
                'model' => __NAMESPACE__ . '\TestAsset\User',
                'where' => $where,
            )
        );

        $query = $this->adapter->buildCountQuery($criteria, $metaDataContainer);
        $results = $this->adapter->execute($query);
        $this->assertInstanceOf('\Zend\Db\Adapter\Driver\ResultInterface', $results);
        $result = $results->current();
        $this->assertEquals(0, $result['count'], 'Find should return result set with a count equal to 0.');
    }

    /**
     * Check find throws \Zend\Db\Adapter\Exception\InvalidQueryException
     * when supplied with unknown column in \Zend\Db\Sql\Where.
     *
     * @expectedException \Zend\Db\Adapter\Exception\InvalidQueryException
     */
    public function testFindWithInvalidWhere()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\Customer');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $where = new Where();
        $where->like('string monkey', '%rick%');
        $criteria = new \ZucchiModel\Query\Criteria(
            array(
                'model' => __NAMESPACE__ . '\TestAsset\Customer',
                'where' => $where,
            )
        );
        $results = $this->adapter->find($criteria, $metaDataContainer);
    }

    /**
     * Check find returns instance of \Zend\Db\Adapter\Driver\ResultInterface
     * when supplied with valid \Zend\Db\Sql\Select on \ZucchiModelTest\Adapter\TestAsset\Customer.
     * Also check the returning count is set and 0.
     */
    public function testFindWithValidOutOfBoundsOffsetAndLimit()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\Customer');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $criteria = new Criteria(
            array(
                'model' => __NAMESPACE__ . '\TestAsset\Customer',
                'offset' => 10,
                'limit' => 1
            )
        );
        $results = $this->adapter->find($criteria, $metaDataContainer);
        $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $results);
        $count = $results->count();
        $this->assertEquals(0, $count, 'Find should return result set with a count equal to 0.');
    }

    /**
     * Check find ignores offset without a limit in \Zend\Db\Sql\Where and
     * returns a \ZucchiModel\ResultSet\HydratingResultSet with 3 results.
     */
    public function testFindWithOffsetAndNoLimit()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\Customer');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $criteria = new Criteria(
            array(
                'model' => __NAMESPACE__ . '\TestAsset\Customer',
                'offset' => 1
            )
        );
        $results = $this->adapter->find($criteria, $metaDataContainer);
        $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $results);
        $count = $results->count();
        $this->assertEquals(3, $count, 'Find should return result set with a count equal to 0.');
    }

    /**
     * Check find returns instance of \ZucchiModel\ResultSet\HydratingResultSet
     * when supplied with valid \Zend\Db\Sql\Select on \ZucchiModelTest\Adapter\TestAsset\Customer
     * and valid \Zend\Db\Sql\Where with valid order by.
     */
    public function testFindWithValidOrderBy()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\Customer');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        // This will preform order by email desc surname asc
        $criteria = new Criteria(
            array(
                'model' => __NAMESPACE__ . '\TestAsset\Customer',
                'orderBy' => array('email DESC', 'surname')
            )
        );
        $results = $this->adapter->find($criteria, $metaDataContainer);
        $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $results);
    }

    /**
     * Check find throws \Zend\Db\Adapter\Exception\InvalidQueryException
     * when supplied with invalid order by in \Zend\Db\Sql\Where.
     *
     * @expectedException \Zend\Db\Adapter\Exception\InvalidQueryException
     */
    public function testFindWithInvalidOrderBy()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\Customer');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $criteria = new Criteria(
            array(
                'model' => __NAMESPACE__ . '\TestAsset\Customer',
                'orderBy' => array('email', '7')
            )
        );
        $results = $this->adapter->find($criteria, $metaDataContainer);
    }

    /**
     * Check find returns first a \ZucchiModel\ResultSet\HydratingResultSet of
     * \ZucchiModelTest\Adapter\TestAsset\User. Then foreach User find a
     * \ZucchiModel\ResultSet\PaginatedResultSet of \ZucchiModelTest\Adapter\TestAsset\Role
     * that are related to Users via a Many to Many.
     */
    public function testFindWithValidUserGetPaginatedManyToManyRelationshipRole()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\User');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $criteria = new Criteria(array('model' => __NAMESPACE__ . '\TestAsset\User'));

        $results = $this->adapter->find($criteria, $metaDataContainer);
        $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $results);

        foreach ($results as $result) {
            $roles = $this->modelManager->getRelationship($result, 'Roles', 10);
            $this->assertInstanceOf('\ZucchiModel\ResultSet\PaginatedResultSet', $roles);
            foreach ($roles as $role) {
                $this->assertInstanceOf('\ZucchiModelTest\Adapter\TestAsset\Role', $role);
            }
        }
    }

    /**
     * Check find returns first a \ZucchiModel\ResultSet\HydratingResultSet of
     * \ZucchiModelTest\Adapter\TestAsset\User. Then foreach User find a
     * \ZucchiModel\ResultSet\HydratingResultSet of \ZucchiModelTest\Adapter\TestAsset\Role
     * that are related to Users via a Many to Many.
     */
    public function testFindWithValidUserGetHydratedManyToManyRelationshipToRole()
    {
        $metaDataContainer = $this->modelManager->getMetaData(__NAMESPACE__ . '\TestAsset\Customer');
        $this->assertInstanceOf('\ZucchiModel\MetaData\MetaDataContainer', $metaDataContainer);

        $criteria = new Criteria(array('model' => __NAMESPACE__ . '\TestAsset\User'));

        $results = $this->adapter->find($criteria, $metaDataContainer);
        $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $results);

        foreach ($results as $result) {
            $roles = $this->modelManager->getRelationship($result, 'Roles');
            $this->assertInstanceOf('\ZucchiModel\ResultSet\HydratingResultSet', $roles);
            foreach ($roles as $role) {
                $this->assertInstanceOf('\ZucchiModelTest\Adapter\TestAsset\Role', $role);
            }
        }
    }
}