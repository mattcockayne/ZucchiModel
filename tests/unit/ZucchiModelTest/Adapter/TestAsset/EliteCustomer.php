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
 * @Model\Target({"moduledev_elite_customer", "moduledev_user","moduledev_customer","moduledev_premier_customer"})
 */
class EliteCustomer extends PremierCustomer
{
    use Behaviour\ChangeTrackingTrait;
    /**
     * discount
     *
     * @Model\Field("string")
     */
    public $surname;
}