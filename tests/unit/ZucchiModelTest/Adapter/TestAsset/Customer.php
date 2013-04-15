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

/**
 * @author Rick Nicol <rick@zucchi.co.uk>
 * @Model\Target({"test_zucchimodel_customer","test_zucchimodel_user"})
 */
class Customer extends User
{
    /**
     * users forename
     *
     * @Model\Field("string")
     */
    public $forename;

    /**
     * customer address
     *
     * @Model\Field("string")
     */
    public $address;
}