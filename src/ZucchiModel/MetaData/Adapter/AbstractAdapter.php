<?php
/**
 * ZucchiModel (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiModel for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */
namespace ZucchiModel\Metadata\Adapter;

use Zend\Stdlib\ArrayObject;

/**
 * Adapter Metadata container
 *
 * @author Matt Cockayne <matt@zucchi.co.uk>
 * @package ZucchiModel
 * @subpackage MetaData
 * @category
 */
abstract class Adapter extends ArrayObject implements AdapterInterface
{
    protected $metaData;

    public function setMetaData($metaData)
    {
        $this->metaData = $metaData;
    }

    public function getMetaData()
    {
        return $this->metaData;
    }
}
