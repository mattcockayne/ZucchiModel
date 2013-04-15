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

use ZucchiModel\Annotation\Target;

/**
 * TargetTest
 *
 * Tests on the Target Class.
 *
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @package ZucchiModelTest
 * @subpackage Annotation
 * @category
 */
class TargetTest extends \Codeception\TestCase\Test
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
     * Tear Down tests
     */
    protected function _after()
    {
    }

    /**
     * Check Target can be instantiated and retrieved.
     */
    public function testGetTargetWithValidTargets()
    {
        $targetOriginal = array(
            'value' => array(
                'test_zucchimodel_customer',
                'test_zucchimodel_user'
            )
        );
        $target = new Target($targetOriginal);
        $this->assertSame($targetOriginal['value'], $target->getTarget());
    }
}