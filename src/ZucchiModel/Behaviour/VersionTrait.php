<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModel\Behaviour;

/**
 * Version Behaviour Trait
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage Model
 * @category
 */
trait VersionTrait
{
    /**
     * @var integer
     * @Model\Type({"type" : "integer"})
     */
    public $version;
}