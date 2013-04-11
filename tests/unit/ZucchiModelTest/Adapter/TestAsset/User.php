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
 * @Model\Target({"moduledev_user"})
 * @Model\Relationship({"name": "Roles", "model": "ZucchiModelTest\Adapter\TestAsset\Role", "type": "ManytoMany", "mappedKey": "id", "mappedBy": "User_id", "foreignKey": "id", "foreignBy": "Role_id", "referencedBy": "moduledev_user_role", "referencedOrder": "sort"})
 */
class User
{
    use Behaviour\IdentityTrait;
    use Behaviour\TimestampTrait;
    use Behaviour\ChangeTrackingTrait;

    /**
     * users forename
     *
     * @Model\Field("string")
     */
    public $forename;

    /**
     * users surname
     *
     * @Model\Field("string")
     */
    public $surname;

    /**
     * users emaiol address
     *
     * @Model\Field("string")
     */
    public $email;
}