<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ZucchiModelTest\Adapter\TestAsset;

use ZucchiModel\Annotation as Model;
use ZucchiModel\Behaviour;

/**
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @Model\Target({"test_zucchimodel_elite_customer", "test_zucchimodel_user","test_zucchimodel_customer","test_zucchimodel_premier_customer"})
 */
class EliteCustomer extends PremierCustomer
{
    /**
     * discount
     *
     * @Model\Field("string")
     */
    public $surname;
}