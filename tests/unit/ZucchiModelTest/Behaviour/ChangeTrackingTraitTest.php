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

use ZucchiModel\Model\Manager;
use ZucchiModel\Adapter\ZendDb;
use ZucchiModel\Metadata;
use ZucchiModel\Query\Criteria;

/**
 * ChangeTrackingTraitTest
 *
 * Tests on the ChangeTrackingTrait Class.
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModelTest
 * @subpackage Behaviour
 * @category
 */
class ChangeTrackingTraitTest extends \Codeception\TestCase\Test
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
     * Tear down Test
     */
    protected function _after()
    {
    }

    /**
     * Check getChanges can workout the correct fields
     * have been changed in User model.
     */
    public function testGetChangesReturnsValidChangeSetForUser()
    {
        $model = $this->modelManager->findOne(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\Behaviour\TestAsset\User'
                )
            )
        );

        $changes = $model->getChanges();

        $this->assertTrue(empty($changes), 'Get Changes should return no changes after findOne call.');

        $model->forename = 'Billington';
        $model->email = 'billington@me.co.uk';

        $changes = $model->getChanges();

        $this->assertSame(
            array(
                'forename' => 'Billington',
                'email' => 'billington@me.co.uk'
            ),
            $changes
        );
    }

    /**
     * Check getChanges can workout the correct fields
     * have been changed in Customer model.
     */
    public function testGetChangesReturnsValidChangeSetForUserWithOriginalValues()
    {
        $model = $this->modelManager->findOne(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\Behaviour\TestAsset\User'
                )
            )
        );

        $changes = $model->getChanges(true);

        $this->assertTrue(empty($changes), 'Get Changes should return no changes after findOne call.');

        $model->forename = 'Billington';
        $model->email = 'billington@me.co.uk';

        $changes = $model->getChanges(true);

        $this->assertSame(
            array(
                'forename' => 'James',
                'email' => 'james@me.co.uk'
            ),
            $changes
        );
    }

    /**
     * Check getCleanData can workout the correct fields
     * have been changed in User model.
     */
    public function testGetCleanDataReturnsValidUserParams()
    {
        $model = $this->modelManager->findOne(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\Behaviour\TestAsset\User'
                )
            )
        );

        $cleanData = $model->getCleanData();

        // Get rid of untested values
        unset(
            $cleanData['createdAt'],
            $cleanData['updatedAt'],
            $cleanData['getProperty'],
            $cleanData['setProperty'],
            $cleanData['unmappedProperties']
        );

        $this->assertSame(
            array(
                'forename' => 'James',
                'surname' => 'Hetfield',
                'email' => 'james@me.co.uk',
                'id' => 1,

            ),
            $cleanData
        );
    }

    /**
     * Check SetCleanData with valid User.
     */
    public function testSetCleanDataWithValidUser()
    {
        $model = $this->modelManager->findOne(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\Behaviour\TestAsset\User'
                )
            )
        );

        $dataOriginal = array(
            'forename' => 'Testy',
            'surname' => 'McTest',
            'email' => 'testy@example.co.uk'
        );

        $model->setCleanData($dataOriginal);

        $cleanData = $model->getCleanData();

        // Get rid of untested values
        unset(
            $cleanData['createdAt'],
            $cleanData['updatedAt'],
            $cleanData['getProperty'],
            $cleanData['setProperty'],
            $cleanData['unmappedProperties']
        );
        $this->assertSame($dataOriginal, $cleanData);
    }

    /**
     * Check getChanges can workout the correct fields
     * have been changed in Customer model.
     */
    public function testGetChangesReturnsValidChangeSetForCustomer()
    {
        $model = $this->modelManager->findOne(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\Behaviour\TestAsset\Customer'
                )
            )
        );

        $changes = $model->getChanges();

        $this->assertTrue(empty($changes), 'Get Changes should return no changes after findOne call.');

        $model->forename = 'Billington';
        $model->email = 'billington@me.co.uk';

        $changes = $model->getChanges();

        $this->assertSame(
            array(
                'forename' => 'Billington',
                'email' => 'billington@me.co.uk'
            ),
            $changes
        );
    }

    /**
     * Check getChanges can workout the correct fields
     * have been changed in Customer model.
     */
    public function testGetChangesReturnsValidChangeSetForCustomerWithOriginalValues()
    {
        $model = $this->modelManager->findOne(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\Behaviour\TestAsset\Customer'
                )
            )
        );

        $changes = $model->getChanges(true);

        $this->assertTrue(empty($changes), 'Get Changes should return no changes after findOne call.');

        $model->forename = 'Billington';
        $model->email = 'billington@me.co.uk';


        $changes = $model->getChanges(true);

        $this->assertSame(
            array(
                'forename' => 'Matt',
                'email' => 'james@me.co.uk'
            ),
            $changes
        );
    }

    /**
     * Check getCleanData can workout the correct fields
     * have been changed in Customer model.
     */
    public function testGetCleanDataReturnsValidCustomerParams()
    {
        $model = $this->modelManager->findOne(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\Behaviour\TestAsset\Customer'
                )
            )
        );

        $cleanData = $model->getCleanData();

        // Get rid of untested values
        unset(
            $cleanData['createdAt'],
            $cleanData['updatedAt'],
            $cleanData['getProperty'],
            $cleanData['setProperty'],
            $cleanData['unmappedProperties']
        );

        $this->assertSame(
            array(
                'forename' => 'Matt',
                'address' => 'Reichstag, Platz der Republik 1, 10557 Berlin',
                'surname' => 'Hetfield',
                'email' => 'james@me.co.uk',
                'id' => 1
            ),
            $cleanData
        );
    }

    /**
     * Check SetCleanData with valid Customer.
     */
    public function testSetCleanDataWithValidCustomer()
    {
        $model = $this->modelManager->findOne(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\Behaviour\TestAsset\Customer'
                )
            )
        );

        $dataOriginal = array(
            'forename' => 'Testy',
            'surname' => 'McTest',
            'email' => 'testy@example.co.uk'
        );

        $model->setCleanData($dataOriginal);

        $cleanData = $model->getCleanData();

        // Get rid of untested values
        unset(
            $cleanData['createdAt'],
            $cleanData['updatedAt'],
            $cleanData['getProperty'],
            $cleanData['setProperty'],
            $cleanData['unmappedProperties']
        );

        $this->assertSame($dataOriginal, $cleanData);
    }

    /**
     * Check isChanged with valid unchanged Customer causes
     * isChanged with no field param to return false.
     * .
     */
    public function testIsChangedOnValidCustomerWithNoChangesAndNoParam()
    {
        $model = $this->modelManager->findOne(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\Behaviour\TestAsset\Customer'
                )
            )
        );

        $this->assertFalse($model->isChanged(), 'After loading and hydrating a Customer, it should return false from isChanged().');
    }

    /**
     * Check isChanged with valid Customer and a change to forename,
     * causes isChanged with no field param to return true.
     */
    public function testIsChangedOnValidCustomerWithForenameChangeAndNoParam()
    {
        $model = $this->modelManager->findOne(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\Behaviour\TestAsset\Customer'
                )
            )
        );

        $model->forename = 'Billington';

        $this->assertTrue($model->isChanged(), 'After loading and hydrating a Customer, and changing the forename, it should return true from isChanged().');
    }

    /**
     * Check isChanged with valid Customer and a change to forename,
     * causes isChanged with 'forename' set as the param to return true.
     */
    public function testIsChangedOnValidCustomerWithForenameChangeAndParamForename()
    {
        $model = $this->modelManager->findOne(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\Behaviour\TestAsset\Customer'
                )
            )
        );

        $model->forename = 'Billington';

        $this->assertTrue($model->isChanged('forename'), 'After loading and hydrating a Customer, and changing the forename, it should return true from isChanged("forename").');
    }

    /**
     * Check isChanged with valid Customer and a change to forename,
     * does not cause isChanged to return true when supplied 'surname' as the param.
     */
    public function testIsChangedOnValidCustomerWithForenameChangeAndParamSurname()
    {
        $model = $this->modelManager->findOne(
            new Criteria(
                array(
                    'model' => 'ZucchiModelTest\Behaviour\TestAsset\Customer'
                )
            )
        );

        $model->forename = 'Billington';

        $this->assertFalse($model->isChanged('surname'), 'After loading and hydrating a Customer, and changing the forename, it should return false from isChanged("surname").');
    }
}